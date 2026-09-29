# Achiever Art — Booking System Requirements

Client: **Achiever Art** (Singapore children's art studio chain).
Target repo: `achiever-art` — WordPress child theme `src/wp-content/themes/ai-zippy-child`.
Reference implementation to port from: `docs/booking/REFERENCE-BOOKING-SYSTEM.md` (the iBAT / Urbanytes booking system) plus the PHP class copies under `docs/booking/reference-urbanytes/`.

This document is the single source of truth for the booking feature. Every subtask must cite real evidence from this repo (existing blocks, theme `inc/`, `functions.php`) or from the reference implementation — never a guess.

---

## 1. Business flow (from client requirement)

1. Customer opens the public booking page, picks a **studio**, then a **programme / class type**, then a **date + time slot** on an oclass-style calendar.
2. Customer submits an **enquiry** (name, phone, email, child age, preferred contact, notes). The booking is created with status **`new`** and the chosen slot is **held immediately** ("vừa đặt là mất slot luôn").
3. Staff reviews the enquiry, **keys in the quoted amount** for that booking (Option A pricing — staff-entered, no public price on slot rows), and marks it **`confirmed`**. Confirming triggers a **payment link** email to the customer.
4. Customer opens the payment link, clicks **Place Order**; the **zippy-pay** plugin renders the **PayNow QR**. Payment completion moves the booking to **`paid`**.
5. Unpaid bookings **auto-cancel** on a TTL (see §5). Cancelled bookings release their slot.

---

## 2. Studios (per-studio isolation is mandatory)

The three studios are the canonical entries already present in `src/blocks/studios-directory/block.json`:

| Studio (display name)        | Suggested slug   | Address                                                     | Phone      |
| ---------------------------- | ---------------- | ----------------------------------------------------------- | ---------- |
| Tampines Studio (OTH)        | `tampines-oth`   | 1 Tampines Walk, #02-86 Our Tampines Hub, Singapore 528523  | 6204 1662  |
| Pasir Ris Studio (DTE)       | `pasir-ris-dte`  | 1 Pasir Ris Close, #03-102 E!HUB @ Downtown East, SG 519599 | 6951 1732  |
| Bedok Studio (HBB)           | `bedok-hbb`      | 11 Bedok North St 1, #01-22 Heartbeat @ Bedok, SG 469662    | 6929 3225  |

**Every** schedule, slot definition, capacity and TTL is configured **per studio** and fully isolated. There is no global default that leaks across studios. A studio's bookings, availability and settings are only ever queried/scoped by that studio's id.

---

## 3. Programme / class types

The authoritative taxonomy is the `programmeOptions` array already in `src/blocks/course-enquiry-form/block.json`:

1. Regular Art Class
2. Art Workshop
3. Art Camp
4. Art Short Course
5. Beyond the Canvas
6. Parties / Events

(The home page `home-class-types` block shows five marketing tiles — Regular Art Classes / Art Camps / Art Workshop / Short Courses / Express Art Classes — these are presentation groupings, **not** the booking taxonomy. Booking binds to the six programme types above.)

Class binding precedence for schedule/price/capacity overrides:
**class + specific date** > **class + weekday** > **studio default**.

---

## 4. Schedule, slots and capacity (per studio)

- Each studio has its own **weekly schedule template**: which weekdays are open, and the time slots on each open weekday.
- Each slot has a **capacity**, default **4 places** (per-studio and per-class overridable).
- Remaining places for a slot = `capacity − (count of non-cancelled bookings holding that slot)`. A slot with 0 remaining is shown as full/unbookable.
- Slot is held from the moment a booking is created at status `new` (not only at `confirmed`).
- Support **closed days / blackout dates** per studio (holidays, closures) that override the weekly template.
- Public calendar renders an **oclass-style** UI: month grid navigation + per-day slot rows showing remaining places and an **Enroll Now** action. **No prices** are shown publicly (Option A).

---

## 5. Status lifecycle and TTL auto-cancel

Statuses: `new` → `confirmed` → `paid`; plus terminal `cancelled`.

| From status   | TTL                                    | Timer starts at    | Rationale                                   |
| ------------- | -------------------------------------- | ------------------ | ------------------------------------------- |
| `new`         | **24 hours** (per-studio configurable) | `created_at`       | Enquiry not yet actioned by staff.          |
| `confirmed`   | **60 minutes** (per-studio configurable) | `confirmed_at`   | Payment link lifetime is ~1 hour (zippy-pay). |
| `paid`        | none                                   | —                  | Terminal success.                           |
| `cancelled`   | none                                   | —                  | Terminal; slot released.                    |

- A **WP-Cron** job runs **every 5 minutes** and cancels any booking that has exceeded its TTL for its current status.
- The public calendar **also releases expired slots at render time** (on-render expiry), so a customer never sees a slot that a just-expired booking is still nominally holding, even between cron ticks.
- Cancelling (manual or TTL) **releases the slot** so remaining-places recomputes immediately.

---

## 6. Payment (zippy-pay)

- Payment is handled entirely by the existing **zippy-pay** plugin rendering a **PayNow QR** on the WooCommerce order/pay page. This feature must **not** build a custom payment page.
- On `confirm`, the system creates the WooCommerce order bridge (see reference `BookingOrders.php` / `WC_Email_IBAT_Booking_Payment.php`) with the **staff-keyed amount**, then emails the customer the **payment link**.
- Payment completion (WooCommerce order `processing`/`completed`) moves the booking to `paid`.

---

## 7. Admin area (studio-first information architecture)

- Admin lands on a **studio gateway / selector** first. Nothing else loads until a studio is chosen.
- After selecting a studio, load that studio's management screen with tabs:
  - **Bookings** — list/filter/action that studio's bookings (view, key amount, confirm → send payment link, cancel).
  - **Lịch & slot** (Schedule & slots) — edit the weekly schedule template, slots, capacities, closed/blackout days for that studio.
  - **Cài đặt** (Settings) — per-studio TTLs (new / confirmed), default capacity, notification email settings.
- All admin queries are strictly scoped to the selected studio.

---

## 8. Public form and calendar

- Public booking page: **select studio first**, then the calendar/slots shown belong to that studio only.
- Enquiry form fields mirror the existing `course-enquiry-form` block labels: name, phone, email, child age, preferred mode of contact, studio, programme type, message.
- On submit: create booking at `new`, hold the slot, send staff notification; show the block's `successMessage`.

---

## 9. Data model (port of the reference)

Port the reference schema, rebranded **iBAT → Achiever Art**, with a **`studio_id`** column threaded through every table so isolation is enforced at the data layer:

- `{prefix}aa_bookings` — one row per booking: studio_id, programme/class ref, slot date+time, customer fields, quoted_amount, status, created_at, confirmed_at, paid_at, cancelled_at, wc_order_id.
- Per-studio availability/schedule option (port of `ibat_booking_availability` option) — weekly template + slots + capacity + closed days + TTLs, keyed by studio.
- Server-side `validateSubmission` gate (port of reference) must re-check slot capacity/availability at submit time (never trust the client), holding the slot atomically.

Reference classes to port (under `docs/booking/reference-urbanytes/`): `BookingsDb.php`, `BookingAvailability.php`, `EnquiryApi.php`, `BookingsAdmin.php`, `BookingOrders.php`, `BookingMailer.php`, `Email/WC_Email_IBAT_Booking_Payment.php`.

---

## 10. Constraints & acceptance

- Must live inside the existing child theme structure (`ai-zippy-child`), follow its block/`inc/`/`functions.php` conventions, and reuse existing studio + programme data rather than hardcoding duplicates.
- WooCommerce + zippy-pay are assumed present at runtime (plugins are gitignored in this repo) — integrate against their APIs, do not vendor them.
- No custom payment page; no public prices.
- Rebrand every user-facing string from iBAT to Achiever Art.
- Acceptance: a customer can pick studio → programme → slot, submit an enquiry that immediately holds the slot; staff can key an amount and confirm (sending a payment link); payment marks it paid; unpaid bookings auto-cancel on TTL and free the slot; admin manages all of the above per studio behind a studio-first gateway; the public calendar is oclass-style with live remaining-places.
