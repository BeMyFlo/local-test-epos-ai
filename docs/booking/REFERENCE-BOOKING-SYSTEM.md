# Booking / Enquiry System — Full Functional Specification

> Audience: an AI agent (or developer) that must understand **what the booking feature does and how it is wired** without reading the source. This document is the single source of truth for the "Bookings" feature of the iBAT / Urbanytes website.

---

## 1. What this feature is

The site runs a **venue/session booking & group-event enquiry system** for a baseball batting-cage business (brand "iBAT", client "Urbanytes", Singapore). It is **not** a real-time self-serve reservation engine. Instead it is a **request → staff-quote → pay-by-link** workflow:

1. A visitor submits an enquiry/booking request through one of two public forms.
2. The request is stored in a **custom database table** and staff are emailed.
3. Staff manage requests in a **custom admin screen** (top-level "Bookings" menu): change status, add internal notes, and — when ready — **issue a quote**.
4. Issuing a quote **creates a hidden WooCommerce order** for the quoted amount and **emails the customer a branded payment link**.
5. When the customer pays, WooCommerce order status flows back and the booking is auto-marked **Paid**.

Two things are shared across the whole feature:
- **Availability settings** (time slots, closed days, blackout dates, booking window, per-slot capacity) — one option, one admin screen, consumed by both public forms and re-validated on the server.
- **Bookings DB table** — one table holding both enquiry types.

### Booking "types"
| Type value | Source form | Typical use |
|---|---|---|
| `group_enquiry` | Events Enquiry Form (`events-form` block) | Corporate / group / event bookings that need a custom quote |
| `session_booking` | Contact Booking Form (`contact-booking` block) | Individual / small session requests (mostly walk-in oriented) |

### Status lifecycle
`new` → `contacted` → `confirmed` → `paid`, with `cancelled` reachable from any state.
- `new`: freshly submitted.
- `contacted`: staff has reached out (manual status change).
- `confirmed`: a quote/WooCommerce order has been issued (set automatically when staff clicks "Create Order & Send", or manually).
- `paid`: the linked WooCommerce order reached `processing`/`completed` (set automatically).
- `cancelled`: staff cancelled → an email is sent to the customer automatically.

---

## 2. Where the code lives (file map)

All booking code lives in the **child theme** `ai-zippy-child`. Nothing is in a plugin.

### PHP classes — `src/wp-content/themes/ai-zippy-child/inc/`
| File | Class / role | Responsibility |
|---|---|---|
| `BookingsDb.php` | `AiZippyChild\BookingsDb` | Custom table `{prefix}ibat_bookings`: create/upgrade schema, insert, query (with search/filter), stats, per-slot counts, update status/fields, delete. |
| `BookingAvailability.php` | `AiZippyChild\BookingAvailability` | Single source of truth for availability. Stored as option `ibat_booking_availability`. Computes slot lists per date, date-picker bounds, capacity filtering, and server-side validation. |
| `EnquiryApi.php` | `AiZippyChild\EnquiryApi` | All REST routes (public submit + public availability + all admin endpoints). Handles enquiry submission, admin list/status/notes/confirm/resend/delete, availability get/save, cancellation email. |
| `BookingsAdmin.php` | `AiZippyChild\BookingsAdmin` | The admin UI: registers the top-level "Bookings" menu and renders two tabs (Bookings list + Availability settings). Pure inline HTML/JS that talks to the REST API. |
| `BookingOrders.php` | `AiZippyChild\BookingOrders` | Bridge to WooCommerce: hidden "Group Booking" product, order creation from a booking, order↔booking status sync, admin order-list "Source" column, order-edit metabox, skip email verification for booking orders. |
| `BookingMailer.php` | `AiZippyChild\BookingMailer` | Registers and sends the custom WooCommerce email `WC_Email_IBAT_Booking_Payment` (the payment-link email). |
| `Email/WC_Email_IBAT_Booking_Payment.php` | `WC_Email_IBAT_Booking_Payment` | A `WC_Email` subclass defining the payment-link customer email (subject, heading, HTML + plain templates, placeholders). |

### Email template
- `src/wp-content/themes/ai-zippy-child/woocommerce/emails/ibat-booking-payment.php` — self-contained branded HTML layout for the payment-link email (does **not** use the shared WC header/footer). A `emails/plain/ibat-booking-payment.php` plain-text counterpart is referenced by the email class.

### Gutenberg blocks (public forms) — `src/wp-content/themes/ai-zippy-child/src/blocks/`
| Block | Files | Role |
|---|---|---|
| `ai-zippy/events-form` ("Events Enquiry Form") | `block.json`, `edit.js`, `render.php`, `index.js`, scss | Group enquiry form. Submits via **AJAX/REST** to `/enquiry`. Renders `window.__ibatAvailability` + client-side date/slot logic. |
| `ai-zippy/contact-booking` ("Contact Booking") | `block.json`, `edit.js`, `render.php`, `index.js`, scss | Session booking form + contact info / opening hours / social panel. Submits via a **classic POST** (server-rendered, nonce-protected). Renders `window.__ibatContactAvailability`. |

Both blocks are auto-registered: `functions.php` scans `assets/blocks/*/block.json` on `init`.

### Wiring — `src/wp-content/themes/ai-zippy-child/functions.php`
- `require_once` for all six `inc/` classes at the top.
- Always calls: `BookingsDb::init()`, `BookingAvailability::register()`, `BookingsAdmin::register()`, `EnquiryApi::register()`.
- Only if WooCommerce is active (`class_exists('WooCommerce')`): `BookingOrders::register()`, `BookingMailer::register()`.
- Defines constant `IBAT_ENQUIRY_TO_EMAIL = 'info@urbanytes.com'` and helper `ibat_enquiry_to_email()` (filterable via `ibat_enquiry_to_email`) — the single inbox that receives all new-submission notifications.
- **Local mail capture:** on `localhost`/`127.0.0.1`/`::1` or CLI, defines `IBAT_MAIL_LOG` and hooks `pre_wp_mail` to write every email to `wp-content/uploads/wc-logs/ibat-mail.log` and pretend delivery succeeded (the Docker container has no sendmail/SMTP). Real hosts are unaffected.

---

## 3. Data model — the `ibat_bookings` table

Table name: `{$wpdb->prefix}ibat_bookings` (e.g. `wp_ibat_bookings`).
Schema version constant `BookingsDb::DB_VERSION = '4'`, tracked in option `ibat_bookings_db_version`. On mismatch, `createTable()` (via `dbDelta`) + `addMissingColumns()` (idempotent `ALTER`s) run on `after_setup_theme`.

| Column | Type | Notes |
|---|---|---|
| `id` | BIGINT UNSIGNED AI PK | The "Booking ID" / reference shown everywhere (`#123`). |
| `type` | VARCHAR(50) | `group_enquiry` (default) or `session_booking`. Indexed. |
| `name` | VARCHAR(255) NOT NULL | Full name (contact form concatenates first+last). |
| `company` | VARCHAR(255) | Only used by group enquiries. |
| `email` | VARCHAR(255) NOT NULL | |
| `phone` | VARCHAR(100) NOT NULL | Phone / WhatsApp. |
| `event_type` | VARCHAR(100) | Group: event type. Session: repurposed to store the chosen session type. |
| `group_size` | VARCHAR(100) | |
| `preferred_date` | VARCHAR(100) | Stored as `Y-m-d` string. Optional. |
| `preferred_time` | VARCHAR(100) | Stores the **slot label** (e.g. `10am – 12pm`). Optional. Indexed indirectly via date/time counts. |
| `coach_preference` | VARCHAR(100) | Present in schema/DB but the public selects are currently removed from the UI (see §5). |
| `details` | TEXT | Public "message / special requests". |
| `notes` | TEXT | **Admin-only internal note. Never emailed to the customer.** |
| `status` | VARCHAR(50) NOT NULL default `new` | One of the 5 statuses. Indexed. |
| `quote_amount` | DECIMAL(10,2) | Amount the admin quoted (excl. tax). |
| `order_id` | BIGINT UNSIGNED | Linked WooCommerce order id. Indexed. |
| `confirmed_at` | DATETIME | When quote/order was issued. |
| `paid_at` | DATETIME | When order became paid. |
| `created_at` | DATETIME default CURRENT_TIMESTAMP | Indexed; list is ordered by this DESC. |

Indexes: `status`, `type`, `order_id`, `created_at`.

### Key DB methods
- `insert(array)` — always sets `status='new'`, `created_at=now`; sanitizes every field; returns new id.
- `getBookings($search,$status,$type,$limit=100,$offset=0)` — `LIKE` search across name/email/phone/company/event_type; status & type filters ignore `all`/empty.
- `getStats()` — totals for the admin stat cards: `total`, `new`, `confirmed`, `paid`, `group` (count of `group_enquiry`).
- `countForSlot($date,$time)` — how many **non-cancelled** bookings hold that exact date+time label (capacity enforcement).
- `countsForDate($date)` — map `['10am – 12pm' => 3, …]` of non-cancelled bookings per slot label for a date.
- `getById($id)` — single row (array) or null.
- `updateStatus($id,$status)` — validates against `STATUSES` whitelist then updates.
- `updateFields($id,$fields)` — whitelist-limited update (only `status`, `quote_amount`, `order_id`, `confirmed_at`, `paid_at`, `notes`).
- `delete($id)` — hard delete of the row.

---

## 4. Availability engine (`BookingAvailability`)

Stored as **one autoloaded option** `ibat_booking_availability`. `get()` always merges stored values onto `defaults()` so the shape is complete. Seeded once on `after_setup_theme` if absent.

### Settings shape & defaults
```php
[
  'time_slots' => [ // default slots used for every open day
    ['start'=>'10:00','end'=>'12:00','label'=>'','capacity'=>0],
    ['start'=>'11:00','end'=>'13:00','label'=>'','capacity'=>0],
    ['start'=>'12:00','end'=>'14:00','label'=>'','capacity'=>0],
    ['start'=>'13:00','end'=>'15:00','label'=>'','capacity'=>0],
    ['start'=>'14:00','end'=>'16:00','label'=>'','capacity'=>0],
    ['start'=>'15:00','end'=>'17:00','label'=>'','capacity'=>0],
  ],
  'closed_weekdays'  => [0],   // 0=Sun … 6=Sat (default: closed Sunday)
  'blackout_dates'   => [],    // ['Y-m-d', …] one-off closures
  'min_notice_days'  => 1,     // earliest selectable date = today + N
  'max_advance_days' => 90,    // latest selectable date   = today + N
  'weekday_overrides' => [],   // [0..6 => [slot,…]] recurring per-weekday slots
  'date_overrides'   => [],    // ['Y-m-d' => [slot,…]] per-date slots
]
```

### Slot semantics
- A slot = `{ start, end, label, capacity }`. Times validated as `HH:MM` (24h). Windows may overlap.
- **`label`**: if blank, it is auto-formatted at read time from start/end, e.g. `10:00`+`12:00` → `10am – 12pm`; `13:30` → `1:30pm`. The blank is preserved in storage; formatting happens on read (`prepareSlots`).
- **The label is what gets stored** in `ibat_bookings.preferred_time` and what fills the form `<select>` option values.
- **`capacity`**: `0` = unlimited (no per-slot check). `>0` = max number of **non-cancelled** bookings allowed for that slot label on a given calendar date.

### Slot resolution precedence (per date)
`getSlotsForDate($ymd)` picks the **first** that applies:
1. **Date-specific override** (`date_overrides['Y-m-d']`) — highest precedence. An override with an **empty slot list closes that single date**.
2. **Recurring weekday override** (`weekday_overrides[0..6]`).
3. **Global default** `time_slots`.

### Open/closed logic
`isDateOpen($ymd)` = valid date AND its weekday not in `closed_weekdays` AND not in `blackout_dates`.

### Capacity-aware labels
`availableLabelsForDate($ymd)`:
- empty date → all labels (no capacity check).
- closed date → `[]` (no slots).
- open date → labels whose per-slot booked count (`countsForDate`) is below capacity (capacity `0` always passes).

### Date-picker bounds
`datePickerBounds()` returns `['min' => today+min_notice_days, 'max' => today+max_advance_days]` in `Y-m-d`, using the site timezone (`current_time`).

### Front-end config blob
`frontendConfig($date='')` returns the JSON injected into the page:
```
{ slots, allSlots, closedWeekdays, blackoutDates, bounds:{min,max} }
```
- `slots`: if `$date` given → `availableLabelsForDate`; else all default labels.
- `allSlots`: every default label (used as a JS fallback).

### Server-side re-validation
`validateSubmission($date,$time)` returns an **error string or null**. Date and time are both optional (empty passes). Checks, in order:
1. If `time` given, it must be in the effective label list for that date → else "The selected preferred time is no longer available…".
2. If `date` given, it must be within bounds → else "Please choose a preferred date within the allowed booking window."
3. If `date` given, `isDateOpen` must be true → else "We are closed on the selected date…".
4. If both date+time given and the slot has `capacity>0`, `countForSlot` must be `< capacity` → else "That time slot is fully booked for the selected date…".

This runs on **every** submission (both forms) so stale/tampered client input is rejected.

### Saving
`save($raw)` sanitizes/normalizes the admin payload (drops invalid times/dates, de-dupes and sorts closed weekdays & blackout dates, converts override lists to keyed maps) and persists it. Empty `time_slots` falls back to defaults. Returns the stored settings.

---

## 5. Public forms (front end)

### 5a. Events Enquiry Form — `events-form` block (group enquiries)
- Rendered as a section with `id="enquiry"`. All fields are plain inputs/selects (no `<form>` submit); a **Submit** button calls `handleEventsSubmit(this)` in JS.
- **Fields:** Your Name\*, Company / Organisation\*, Email\*, Phone / WhatsApp\*, Event Type\*, Group Size\*, Preferred Date (optional), Preferred Time (optional), Additional Details / Special Requests (optional). (`*` = required.)
- **Options** come from block attributes, with defaults:
  - `eventTypeOptions` default: Corporate Team Building / School-Youth Programme / Birthday Celebration / Private Group Session / Community Event / Other.
  - `groupSizeOptions` default: `5 - 10`, `11 - 15`, `16 - 20`, `21 - 25`.
  - `preferredTimeOptions` (block-level override). **If set (non-empty), it wins** over central availability for the slot list; capacity filtering only applies to the central slots, and the live date→slot re-fetch is disabled (`hasOverride` flag).
  - `coachOptions` exists but the Coach select is **commented out** of the UI (kept for easy restore).
- **Preferred Date** input gets `min`/`max` from `bounds`. Preferred Time select is only rendered if there are slots.
- **Submission:** `POST /wp-json/ai-zippy-child/v1/enquiry` with JSON `{ name, company, email, phone, event_type, group_size, preferred_date, preferred_time, details }`.
- **Client-side UX:** before submit it checks required fields and runs `ibatCheckEnquiryDate()` (bounds/closed-weekday/blackout). On date change it re-fetches `/enquiry/availability?date=…` to refresh the time select (`ibatRefreshSlots`), keeping the current choice if still available; shows "No times available for this date" when empty. Button shows `Submitting…` → `✓ Submitted!` and the form clears on success; errors show a red status box.
- **Server (`handleEnquiry`):** validates name/company/email/phone/event_type/group_size required, then `BookingAvailability::validateSubmission`. Inserts a row with `type='group_enquiry'`. Emails staff (`ibat_enquiry_to_email()`) a plain-text "New Group Event Enquiry" summary (Reply-To = customer) and emails the customer a "We have received your group enquiry" confirmation. Returns `{ success, message, bookingId, mailSent }`.

### 5b. Contact Booking Form — `contact-booking` block (session bookings)
- Two-column layout: left = walk-in notice + booking form; right = Contact Information, Opening Hours for Walk-Ins, Follow & Connect (socials), and a "Fastest Response" WhatsApp CTA card.
- This form is a **classic server-rendered POST** (not REST). The `render.php` handles `$_SERVER['REQUEST_METHOD']==='POST'` with a nonce field `ibat_booking_nonce` (action `ibat_booking`). On success it re-renders a green "Booking Request Received!" panel instead of the form.
- **Fields currently rendered:** First Name\*, Last Name\*, Email\*, Phone / WhatsApp\*, Group Size (select), Preferred Date, Preferred Time (select), Message / Special Requests (textarea), Consent checkbox\* ("I agree to be contacted by iBAT…"). 
  - **Session Type** and **Coaches Preference** selects are **commented out** of the markup (the code/vars are retained so they can be re-added). So `session_type`/`coach_preference` normally submit empty for this form.
- **Required (server):** first_name, last_name, valid email, phone, consent. Then `validateSubmission` on date/time. On error it re-renders the form with a red error list.
- **On success:** inserts a row with `type='session_booking'` (name = first+last, `event_type` = session_type value), emails staff "New iBAT Booking Request" (Reply-To = customer) and emails the customer "We have received your booking request".
- **Same availability behaviour** as events-form via `window.__ibatContactAvailability`: date bounds, closed-weekday/blackout client checks (`ibatContactCheckDate`), live slot refresh (`ibatContactRefreshSlots`), and it blocks submit if the chosen date is invalid.
- **Editable block attributes** (via `block.json`) include all labels/placeholders, contact details (address, phone, email, WhatsApp URLs), walk-in notice text (+ extra lines), opening hours rows, social links, and the option lists (session types, group sizes, coaches). Defaults are iBAT/SAFRA Tampines branded.
- Note: `functions.php` has a `render_block_data` filter that migrates **known stale placeholder** contact values (old address/phone/email/WhatsApp `6500000000`) to current ones, and drops legacy `sectionTitle`/`whatsappNumber` attrs.

---

## 6. REST API (namespace `ai-zippy-child/v1`)

Registered by `EnquiryApi::registerRoutes()` on `rest_api_init`.

### Public (no auth — `permission_callback => __return_true`)
| Method | Route | Purpose |
|---|---|---|
| POST | `/enquiry` | Submit a group enquiry (events-form). Validates + inserts `group_enquiry` + sends staff & customer emails. |
| GET | `/enquiry/availability?date=Y-m-d` | Returns `frontendConfig($date)` — live slot list (capacity-filtered for that date) + bounds + closed days. Used by both forms' date→slot refresh. |

### Admin (require `manage_options` — `adminPermission()`)
| Method | Route | Purpose |
|---|---|---|
| GET | `/bookings/availability` | Read raw availability settings. |
| POST | `/bookings/availability` | Save availability settings (`saveAvailability` → `BookingAvailability::save`). |
| GET | `/bookings?search=&status=&type=` | List bookings (+ each item's linked `order` summary) + `stats`. |
| POST | `/bookings/status` | `{ id, status }` → change status; if `cancelled`, auto-email the customer. |
| POST | `/bookings/notes` | `{ id, notes }` → save admin-only internal note. |
| POST | `/bookings/confirm` | `{ id, amount, note }` → create WC order + set `confirmed` + send payment-link email. |
| POST | `/bookings/resend` | `{ id }` → resend the payment-link email for an existing order. |
| DELETE | `/bookings/{id}` | Hard-delete a booking row. |

Admin UI calls all of these with the `X-WP-Nonce` header (`wp_create_nonce('wp_rest')`).

### Order summary shape (attached to each booking in `/bookings`)
`orderInfo($order_id)` returns `{ id, number, status, status_name, total, total_html, currency, is_paid, pay_url (only if needs payment), edit_url }`, or `null` if WooCommerce is missing / order gone.

### Confirm flow details (`confirmBooking`)
- 404 if booking not found.
- 409 "already has an order — use Resend" if `order_id` still maps to a real order.
- 400 if `amount <= 0`.
- Creates the order (`BookingOrders::createOrderForBooking`), then updates the booking: `status='confirmed'`, `quote_amount`, `order_id`, `confirmed_at`. Then sends the payment-link email. Returns `{ success, mail_sent, message, order }` (message tells the admin to use Resend if mail failed).

---

## 7. Admin UI — top-level "Bookings" menu

Registered by `BookingsAdmin::register()` → `add_menu_page('Bookings','Bookings','manage_options','ibat-bookings', renderAdminPage, 'dashicons-calendar-alt', position 26)`. URL: `admin.php?page=ibat-bookings`. Two tabs (switched via `&tab=`): **Bookings** (default) and **Availability**.

### 7a. Bookings tab
- **Header** with a "Refresh Data" button.
- **Stat cards** (from `getStats`): Total Bookings, New Enquiries, Confirmed, Group Enquiries.
- **Filters:** text search (name/email/phone/company/event_type, debounced 300ms), Status select (All / New / Contacted / Confirmed / Paid / Cancelled), Type select (All / Group Enquiry / Session Booking). Changing filters reloads via `/bookings`.
- **Table columns:** Customer (name, company, 📝 if it has notes, email • phone), Type badge (Group Enquiry / Session Booking), Details (`event_type (group_size)`), Date/Time (`preferred_date @ preferred_time`, else "Flex"), Status badge (color-coded), Submitted (`created_at`), Actions.
- **Actions per row:** a Status `<select>` (instant `updateStatus`), a "View Details" (eye) button opening the modal, and a Delete (trash) button with a JS `confirm()`.
- **Detail modal** shows: full customer info, type, event/group size, preferred date/time, coach preference, the "Additional Details / Special Requests", an **Internal Notes** textarea (amber box, "never emailed to the customer") with a Save Notes button, and an **Order section**:
  - **No order + not cancelled** → a green "Confirm & Send Payment Link" panel: an **Amount (excl. tax)** number input, an optional **note to customer** textarea, and a red "Create Order & Send" button. Helper text: "Creates a WooCommerce order (1 order = this booking) and emails the customer a payment link. GST is added on top."
  - **Has order** → a purple panel with Order #, "Open order ↗" link, Status, Total, the **payment link** (readonly + Copy) when unpaid, or "✓ Payment received" when paid, plus a "Resend payment email" button (disabled once paid).
  - **Cancelled** → a dashed note: reopen (set status) to issue a quote.

### 7b. Availability tab
Renders editable UI over `BookingAvailability::get()`, saved via `POST /bookings/availability`:
- **Time slots** (default slots): repeating rows of `start time → end time`, label text (blank = "Auto label"), capacity number (title "Slots (0 = unlimited)"), and remove ✕. "+ Add slot" appends `09:00–11:00`.
- **Weekday time slots** (recurring per-weekday overrides): pick a weekday + its own slot rows. Weekdays not listed use defaults.
- **Date-specific time slots** (per-date overrides): pick a date + slot rows. A date with **no slots = that date is closed**. Date overrides beat weekday overrides.
- **Weekly closed days:** Sun–Sat checkboxes (`closed_weekdays`).
- **Holiday / blackout dates:** date input + "Add date"; rendered as removable pills.
- **Booking window:** "Minimum notice (days)" and "Book up to (days ahead)".
- **Save availability settings** button posts the whole payload; on success it re-syncs the in-memory JS state from the returned settings and re-renders.
- Helper copy explains: overlapping windows are allowed; blank labels auto-format; capacity `0` = unlimited; precedence is date-specific > weekday > default.

---

## 8. WooCommerce integration (`BookingOrders`)

Only active when WooCommerce is loaded. Model: **one shared hidden product**, each order's line-item price is overridden with the admin's quote, and **GST/tax is added on top** by WooCommerce.

### Hidden "Group Booking" product
- `getProductId()` reads option `ibat_booking_product_id`; if missing/invalid/trashed it **auto-creates** a `WC_Product_Simple` named "Group Booking": status `private`, catalog visibility `hidden`, virtual, sold individually, taxable, empty price, meta `_ibat_booking_product='yes'`. Id is cached in the option.

### Order creation (`createOrderForBooking($booking,$amount,$note)`)
- Errors if `amount <= 0` or product unavailable.
- `wc_create_order()`, adds the product with qty 1 and `subtotal`/`total` = `$amount`.
- Line-item name = `event_type (group_size)`; item meta: Booking ID `#<id>`, Preferred Date, Preferred Time (when present).
- Billing from the booking: first/last name split from `name`, email, phone, company (if any), country = store base country (fallback `SG`).
- `created_via = 'ibat_booking'`; order meta `_ibat_booking_id`, `_ibat_order_type='group_booking'`, and `_ibat_quote_note` (if a note was given).
- Adds an order note "Created from Group Booking enquiry #<id>".
- `calculate_totals(true)` (recalculates tax so GST sits on top), status → `pending` ("Awaiting customer payment"), then `save()`. Returns the `WC_Order`.

### Order → booking status sync (`woocommerce_order_status_changed`)
- If new status is `processing` or `completed` → booking `status='paid'`, `paid_at=now`.
- If `cancelled` or `refunded` → booking `status='confirmed'` (so staff can re-issue).

### Payment without the email gate
`woocommerce_order_email_verification_required` returns `false` for orders carrying `_ibat_booking_id`, so customers can pay straight from the emailed link (scoped to booking orders only).

### Admin surfaces
- **Order list "Source" column** (HPOS + legacy): shows a green "🏟 Group Booking" pill for booking orders, else an em dash.
- **Order-edit metabox** "Group Booking" (side, high; HPOS + legacy): shows Enquiry #, Event, Group size, Preferred date/time, the quote note, and an "Open Bookings admin →" link. Non-booking orders show "Not a booking order."

---

## 9. Emails

All new-submission and cancellation emails are plain-text via `wp_mail()`. The payment-link email is a branded WooCommerce HTML email. On localhost/CLI everything is captured to `ibat-mail.log` instead of sent (see §2).

| Email | Trigger | To | Subject / content |
|---|---|---|---|
| Staff: new group enquiry | `handleEnquiry` (events-form) | `ibat_enquiry_to_email()` | "New Group Event Enquiry - <name> (<company>)"; full field dump; Reply-To = customer. |
| Customer: group enquiry received | `handleEnquiry` | customer | "We have received your group enquiry - iBAT"; promises reply within 1 business day; echoes details. |
| Staff: new booking request | contact-booking POST | `ibat_enquiry_to_email()` | "New iBAT Booking Request - <first> <last>"; full field dump; Reply-To = customer. |
| Customer: booking received | contact-booking POST | customer | "We have received your booking request - iBAT"; reply within 24h; echoes details. |
| Customer: cancellation | `updateStatus` → `cancelled` | customer | "Your iBAT booking request has been cancelled"; booking details; invites rebook via reply/WhatsApp. |
| Customer: **payment link** | `confirmBooking` / `resendPaymentLink` → `BookingMailer::sendPaymentLink` | order billing email | WooCommerce email `WC_Email_IBAT_Booking_Payment`. |

### The payment-link email (`WC_Email_IBAT_Booking_Payment`)
- `id = 'ibat_booking_payment'`, `customer_email = true`. Appears under WooCommerce → Settings → Emails as "Group Booking — payment link".
- Default subject: **"Your iBAT group booking is confirmed — complete your payment"**; heading: **"Booking confirmed — one step left"**; additional content: "Questions? Reply to this email or message us on WhatsApp…".
- Placeholders: `{site_title}`, `{booking_id}`, `{order_number}`.
- Templates resolved from the child theme: `woocommerce/emails/ibat-booking-payment.php` (HTML) and `woocommerce/emails/plain/ibat-booking-payment.php` (plain).
- **Triggered manually** via `do_action('ibat_booking_payment_link', $booking_id, $order_id)` or directly by `BookingMailer::sendPaymentLink()` → `trigger()`. It is **not** hooked to automatic order events. `trigger()` loads the booking + order, sets recipient = order billing email, and sends through the WC mailer (so any SMTP plugin applies). Returns bool; failures are `error_log`ged with an `[ibat]` prefix.
- **HTML template content:** brand header (site logo or name), green accent bar, greeting "Hi <first name>,", a **booking summary** card (Booking reference `#id`, Event type, Group size, Preferred date, Preferred time, Special requests), an **amount** card (Subtotal, GST/tax if any, bold "Total due"), a big red **"Complete payment"** button linking to `$order->get_checkout_payment_url()` plus the raw URL as text, optional additional content, sign-off "See you at the cage," and a copyright footer. Brand colors: green `#1B3A1F`, crimson `#C41C1C`. Self-contained layout (does not use WC's shared header/footer).

### `BookingMailer` internals
Hooks `woocommerce_email_classes` to inject the email instance (built by `require`ing the class file, which `return`s a new instance). `getEmail()` safely resolves the instance either from the live WC mailer or by building it from file, so it works when called from REST handlers outside `WC_Emails::init()`.

---

## 10. End-to-end flows

### Customer submits a group enquiry
1. Visitor fills the **Events Enquiry Form** and clicks Submit.
2. JS validates required fields + date, `POST /enquiry`.
3. Server validates again (incl. `validateSubmission`), inserts a `group_enquiry` row (`status=new`), emails staff + customer.
4. UI shows the success message and clears the form.

### Staff turn an enquiry into a paid booking
1. Staff opens **Bookings**, sees the new row (status New), optionally sets status **Contacted** and writes an **Internal Note**.
2. Staff opens the detail modal, enters the quoted **Amount (excl. tax)** + optional customer note, clicks **Create Order & Send**.
3. `POST /bookings/confirm` → a hidden-product WooCommerce order is created for that amount (GST added on top), booking becomes **Confirmed** with `quote_amount`, `order_id`, `confirmed_at`.
4. The branded **payment-link email** is sent to the customer.
5. Customer clicks the link and pays (no email-verification gate). WooCommerce order → `processing`/`completed`.
6. `syncOrderStatus` flips the booking to **Paid** with `paid_at`. The modal now shows "✓ Payment received" and disables Resend.
7. If mail failed at step 4, staff uses **Resend** (`/bookings/resend`). If the order is cancelled/refunded, the booking falls back to **Confirmed**.

### Session booking (contact form)
Same storage/admin/email pipeline, but submission is a **classic nonce-protected POST** rendered by the `contact-booking` block, stored as `type=session_booking`. Staff can still issue a quote/payment link exactly the same way.

---

## 11. Configuration reference (options, constants, defaults)

| Kind | Key | Where | Meaning / default |
|---|---|---|---|
| Option | `ibat_booking_availability` | BookingAvailability | All availability settings (§4). |
| Option | `ibat_bookings_db_version` | BookingsDb | Schema version gate (`'4'`). |
| Option | `ibat_booking_product_id` | BookingOrders | Cached hidden "Group Booking" product id. |
| Constant | `IBAT_ENQUIRY_TO_EMAIL` | functions.php | `info@urbanytes.com` — staff inbox for new submissions. |
| Constant | `IBAT_MAIL_LOG` | functions.php | Set on localhost/CLI; path `uploads/wc-logs/ibat-mail.log`. |
| Filter | `ibat_enquiry_to_email` | functions.php | Override the staff enquiry inbox. |
| Order meta | `_ibat_booking_id`, `_ibat_order_type` (`group_booking`), `_ibat_quote_note` | BookingOrders | Tag/identify booking orders. |
| Product meta | `_ibat_booking_product='yes'` | BookingOrders | Marks the hidden product. |
| Nonce (form) | action `ibat_booking`, field `ibat_booking_nonce` | contact-booking render.php | Classic POST protection. |
| Nonce (REST) | `wp_rest` via `X-WP-Nonce` | BookingsAdmin JS | Admin REST calls. |
| Action hook | `ibat_booking_payment_link` ($booking_id, $order_id) | WC_Email class | Fires the payment-link email. |

---

## 12. Behaviours & gotchas an AI must know

- **Validation is duplicated on purpose:** the browser blocks invalid dates/slots for UX, but the server (`validateSubmission`) is the real gate. Never trust the client payload.
- **Preferred date/time are optional.** Empty values pass validation; capacity is only enforceable when both date and time are present.
- **`preferred_time` stores the human label** (e.g. `10am – 12pm`), not a time range. Capacity counting matches on that exact label string, so changing a slot's label effectively resets its booked-count matching.
- **Slot precedence** is date-override > weekday-override > global default. An override (date or weekday) with **zero slots closes** that date/weekday.
- **Capacity `0` = unlimited.** Capacity is per slot-label per calendar date, counting only **non-cancelled** bookings.
- **Both forms share one availability config and one DB table**, distinguished by `type`.
- **The events-form uses REST/AJAX; the contact-booking form uses a classic server-rendered POST** with a nonce and a server-rendered success panel. Different mechanics, same downstream storage/email/admin path.
- **Session Type and Coach Preference selects are currently commented out** of both public forms, but the DB columns, block attributes, option lists, and server handling remain intact so they can be re-enabled by uncommenting the noted spots in the `render.php` files.
- **`notes` is internal-only** and never included in any customer email.
- **Quotes are manual.** There is no automatic pricing; the admin types the amount. Tax/GST is added on top by WooCommerce at `calculate_totals(true)`.
- **One booking ↔ one order.** Confirming twice is blocked (HTTP 409); use Resend. Deleting a booking row does **not** delete its WooCommerce order.
- **WooCommerce is optional.** If WooCommerce is inactive, forms/DB/admin still work, but `order` info is null and confirm/resend are unavailable; `BookingOrders`/`BookingMailer` are not registered.
- **Local dev never sends real mail.** On localhost/CLI all `wp_mail` is intercepted to `ibat-mail.log` and reported as success — inspect that file to verify email content locally.
- **Emails:** submission/cancellation emails are plain text; only the payment-link email is branded HTML through the WC mailer (so it respects the site's SMTP setup and the WC "Emails" settings screen where it can be enabled/disabled and its subject/heading edited).
```
