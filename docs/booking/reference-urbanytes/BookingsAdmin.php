<?php
// AI-generated
namespace AiZippyChild;

defined('ABSPATH') || exit;

class BookingsAdmin
{
    public static function register(): void
    {
        add_action('admin_menu', [self::class, 'addMenuPage']);
    }

    public static function addMenuPage(): void
    {
        add_menu_page(
            'Bookings',
            'Bookings',
            'manage_options',
            'ibat-bookings',
            [self::class, 'renderAdminPage'],
            'dashicons-calendar-alt',
            26
        );
    }

    public static function renderAdminPage(): void
    {
        $nonce = wp_create_nonce('wp_rest');
        $tab   = isset($_GET['tab']) ? sanitize_key($_GET['tab']) : 'bookings';
        $base  = admin_url('admin.php?page=ibat-bookings');
        ?>
        <div style="margin:20px 20px 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
            <h2 class="nav-tab-wrapper" style="margin-bottom:20px;">
                <a href="<?php echo esc_url($base); ?>" class="nav-tab <?php echo $tab === 'bookings' ? 'nav-tab-active' : ''; ?>">Bookings</a>
                <a href="<?php echo esc_url($base . '&tab=availability'); ?>" class="nav-tab <?php echo $tab === 'availability' ? 'nav-tab-active' : ''; ?>">Availability</a>
            </h2>
        </div>
        <?php
        if ($tab === 'availability') {
            self::renderAvailabilityTab($nonce);
            return;
        }
        ?>
        <div class="wrap ibat-bookings-wrap" style="margin:20px 20px 0 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Oxygen,Ubuntu,Cantarell,sans-serif;">
            <!-- Header section -->
            <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px;">
                <div>
                    <h1 style="font-size:28px; font-weight:700; color:#101828; margin:0 0 6px 0; padding:0; line-height:1.2;">Bookings</h1>
                    <p style="font-size:14px; color:#475467; margin:0;">Manage your group enquiries and session bookings</p>
                </div>
                <div style="display:flex; gap:12px;">
                    <button type="button" onclick="loadBookings()" style="background:#128c7e; color:#ffffff; border:none; padding:10px 18px; border-radius:8px; font-size:14px; font-weight:600; cursor:pointer; display:inline-flex; align-items:center; gap:8px; box-shadow:0 1px 2px rgba(16,24,40,0.05); transition:background 0.2s;">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Refresh Data
                    </button>
                </div>
            </div>

            <!-- Stats Bar -->
            <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px, 1fr)); gap:16px; margin-bottom:24px;">
                <div style="background:#ffffff; border:1px solid #eaecf0; border-radius:12px; padding:18px 20px; box-shadow:0 1px 3px rgba(16,24,40,0.05);">
                    <div style="font-size:13px; color:#475467; font-weight:500;">Total Bookings</div>
                    <div id="stat-total" style="font-size:26px; font-weight:700; color:#101828; margin-top:4px;">-</div>
                </div>
                <div style="background:#ffffff; border:1px solid #eaecf0; border-radius:12px; padding:18px 20px; box-shadow:0 1px 3px rgba(16,24,40,0.05);">
                    <div style="font-size:13px; color:#b54708; font-weight:500;">● New Enquiries</div>
                    <div id="stat-new" style="font-size:26px; font-weight:700; color:#b54708; margin-top:4px;">-</div>
                </div>
                <div style="background:#ffffff; border:1px solid #eaecf0; border-radius:12px; padding:18px 20px; box-shadow:0 1px 3px rgba(16,24,40,0.05);">
                    <div style="font-size:13px; color:#027a48; font-weight:500;">● Confirmed</div>
                    <div id="stat-confirmed" style="font-size:26px; font-weight:700; color:#027a48; margin-top:4px;">-</div>
                </div>
                <div style="background:#ffffff; border:1px solid #eaecf0; border-radius:12px; padding:18px 20px; box-shadow:0 1px 3px rgba(16,24,40,0.05);">
                    <div style="font-size:13px; color:#5925dc; font-weight:500;">● Group Enquiries</div>
                    <div id="stat-group" style="font-size:26px; font-weight:700; color:#5925dc; margin-top:4px;">-</div>
                </div>
            </div>

            <!-- Filters Bar (Search & Selects matching reference image style) -->
            <div style="display:flex; flex-wrap:wrap; gap:12px; margin-bottom:20px; align-items:center;">
                <div style="position:relative; flex:1; min-width:260px; max-width:360px;">
                    <input type="text" id="filter-search" placeholder="Search by name, email, phone..." oninput="debounceLoad()" style="width:100%; height:40px; padding:0 14px 0 38px; border:1px solid #d0d5dd; border-radius:8px; font-size:14px; color:#101828; background:#ffffff; box-shadow:0 1px 2px rgba(16,24,40,0.05); outline:none;">
                    <svg width="16" height="16" fill="none" stroke="#667085" stroke-width="2" viewBox="0 0 24 24" style="position:absolute; left:12px; top:12px;"><circle cx="11" cy="11" r="8"></circle><path d="M21 21l-4.35-4.35"></path></svg>
                </div>
                <select id="filter-status" onchange="loadBookings()" style="height:40px; padding:0 32px 0 14px; border:1px solid #d0d5dd; border-radius:8px; font-size:14px; color:#344054; background:#ffffff; box-shadow:0 1px 2px rgba(16,24,40,0.05); cursor:pointer;">
                    <option value="all">All Status</option>
                    <option value="new">● New</option>
                    <option value="contacted">● Contacted</option>
                    <option value="confirmed">● Confirmed</option>
                    <option value="paid">● Paid</option>
                    <option value="cancelled">● Cancelled</option>
                </select>
                <select id="filter-type" onchange="loadBookings()" style="height:40px; padding:0 32px 0 14px; border:1px solid #d0d5dd; border-radius:8px; font-size:14px; color:#344054; background:#ffffff; box-shadow:0 1px 2px rgba(16,24,40,0.05); cursor:pointer;">
                    <option value="all">All Types</option>
                    <option value="group_enquiry">Group Enquiry</option>
                    <option value="session_booking">Session Booking</option>
                </select>
            </div>

            <!-- Table Card Container (matching reference image) -->
            <div style="background:#ffffff; border:1px solid #eaecf0; border-radius:12px; box-shadow:0 1px 3px rgba(16,24,40,0.05); overflow:hidden;">
                <table style="width:100%; border-collapse:collapse; text-align:left; font-size:14px;">
                    <thead>
                        <tr style="background:#f9fafb; border-bottom:1px solid #eaecf0; color:#475467; font-size:12px; font-weight:600; text-transform:uppercase; letter-spacing:0.04em;">
                            <th style="padding:14px 20px;">Customer ↑↓</th>
                            <th style="padding:14px 16px;">Type</th>
                            <th style="padding:14px 16px;">Details</th>
                            <th style="padding:14px 16px;">Date / Time</th>
                            <th style="padding:14px 16px;">Status</th>
                            <th style="padding:14px 16px;">Submitted ↑↓</th>
                            <th style="padding:14px 20px; text-align:right;">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="bookings-tbody">
                        <tr>
                            <td colspan="7" style="padding:40px; text-align:center; color:#667085;">Loading bookings data...</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Detail Modal Popup -->
        <div id="booking-modal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(16,24,40,0.5); backdrop-filter:blur(4px); z-index:99999; align-items:center; justify-content:center;">
            <div style="background:#ffffff; border-radius:12px; width:90%; max-width:600px; max-height:85vh; overflow-y:auto; padding:28px; box-shadow:0 20px 24px -4px rgba(16,24,40,0.1); font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
                <div style="display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px;">
                    <div>
                        <h3 id="modal-title" style="margin:0; font-size:20px; font-weight:700; color:#101828;">Booking Details</h3>
                        <div id="modal-sub" style="font-size:13px; color:#475467; margin-top:4px;"></div>
                    </div>
                    <button type="button" onclick="closeModal()" style="background:none; border:none; font-size:20px; cursor:pointer; color:#667085;">✕</button>
                </div>
                <div id="modal-body" style="font-size:14px; color:#344054; line-height:1.6;"></div>
                <div style="margin-top:24px; text-align:right;">
                    <button type="button" onclick="closeModal()" style="background:#f2f4f7; color:#344054; border:none; padding:10px 18px; border-radius:8px; font-weight:600; cursor:pointer;">Close</button>
                </div>
            </div>
        </div>

        <script>
        const restNonce = <?php echo json_encode($nonce); ?>;
        let bookingsCache = [];
        let debounceTimer = null;

        function debounceLoad() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(loadBookings, 300);
        }

        function loadBookings() {
            const search = document.getElementById('filter-search').value;
            const status = document.getElementById('filter-status').value;
            const type   = document.getElementById('filter-type').value;

            const url = '/wp-json/ai-zippy-child/v1/bookings?search=' + encodeURIComponent(search) + '&status=' + encodeURIComponent(status) + '&type=' + encodeURIComponent(type);

            fetch(url, {
                headers: { 'X-WP-Nonce': restNonce }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    bookingsCache = data.bookings || [];
                    renderStats(data.stats || {});
                    renderTable(data.bookings || []);
                }
            })
            .catch(err => console.error(err));
        }

        function renderStats(stats) {
            document.getElementById('stat-total').textContent = stats.total || 0;
            document.getElementById('stat-new').textContent = stats.new || 0;
            document.getElementById('stat-confirmed').textContent = stats.confirmed || 0;
            document.getElementById('stat-group').textContent = stats.group || 0;
        }

        function renderTable(items) {
            const tbody = document.getElementById('bookings-tbody');
            if (!items.length) {
                tbody.innerHTML = `<tr><td colspan="7" style="padding:40px; text-align:center; color:#667085;">No bookings found.</td></tr>`;
                return;
            }

            let html = '';
            items.forEach(b => {
                // Type badge
                let typeBadge = '';
                if (b.type === 'group_enquiry') {
                    typeBadge = `<span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:16px; font-size:12px; font-weight:600; background:#f4f3ff; color:#5925dc; border:1px solid #d9d6fe;"><span style="width:6px; height:6px; border-radius:50%; background:#5925dc;"></span>Group Enquiry</span>`;
                } else {
                    typeBadge = `<span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:16px; font-size:12px; font-weight:600; background:#ecfdf3; color:#027a48; border:1px solid #abefc6;"><span style="width:6px; height:6px; border-radius:50%; background:#027a48;"></span>Session Booking</span>`;
                }

                // Status badge
                let statusBadge = '';
                const st = b.status || 'new';
                if (st === 'new') {
                    statusBadge = `<span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:16px; font-size:12px; font-weight:600; background:#fffaeb; color:#b54708; border:1px solid #fedf89;"><span style="width:6px; height:6px; border-radius:50%; background:#b54708;"></span>New</span>`;
                } else if (st === 'contacted') {
                    statusBadge = `<span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:16px; font-size:12px; font-weight:600; background:#f0f9ff; color:#026aa2; border:1px solid #b2ddff;"><span style="width:6px; height:6px; border-radius:50%; background:#026aa2;"></span>Contacted</span>`;
                } else if (st === 'confirmed') {
                    statusBadge = `<span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:16px; font-size:12px; font-weight:600; background:#eff8ff; color:#175cd3; border:1px solid #b2ddff;"><span style="width:6px; height:6px; border-radius:50%; background:#175cd3;"></span>Confirmed</span>`;
                } else if (st === 'paid') {
                    statusBadge = `<span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:16px; font-size:12px; font-weight:600; background:#ecfdf3; color:#027a48; border:1px solid #abefc6;"><span style="width:6px; height:6px; border-radius:50%; background:#027a48;"></span>Paid</span>`;
                } else {
                    statusBadge = `<span style="display:inline-flex; align-items:center; gap:6px; padding:4px 10px; border-radius:16px; font-size:12px; font-weight:600; background:#fef3f2; color:#b42318; border:1px solid #fecdca;"><span style="width:6px; height:6px; border-radius:50%; background:#b42318;"></span>Cancelled</span>`;
                }

                // Details string
                let detailsStr = b.event_type || 'General';
                if (b.group_size) detailsStr += ` (${b.group_size})`;

                // Date/time string
                let dateTimeStr = b.preferred_date || 'Flex';
                if (b.preferred_time) dateTimeStr += ' @ ' + b.preferred_time;

                html += `
                <tr style="border-bottom:1px solid #f2f4f7; transition:background 0.15s;" onmouseover="this.style.background='#f9fafb'" onmouseout="this.style.background='#ffffff'">
                    <td style="padding:16px 20px;">
                        <div style="font-weight:600; color:#101828; font-size:14px;">${escapeHtml(b.name)} ${b.company ? '<span style="color:#667085; font-weight:400;">(' + escapeHtml(b.company) + ')</span>' : ''} ${b.notes ? '<span title="Admin notes: ' + escapeHtml(b.notes) + '" style="margin-left:2px;">📝</span>' : ''}</div>
                        <div style="font-size:13px; color:#475467; margin-top:2px;">${escapeHtml(b.email)} • ${escapeHtml(b.phone)}</div>
                    </td>
                    <td style="padding:16px;">${typeBadge}</td>
                    <td style="padding:16px; color:#344054;">${escapeHtml(detailsStr)}</td>
                    <td style="padding:16px; color:#475467; font-size:13px;">${escapeHtml(dateTimeStr)}</td>
                    <td style="padding:16px;">${statusBadge}</td>
                    <td style="padding:16px; color:#667085; font-size:13px;">${escapeHtml(b.created_at || '-')}</td>
                    <td style="padding:16px 20px; text-align:right;">
                        <div style="display:inline-flex; align-items:center; gap:8px;">
                            <select onchange="updateStatus(${b.id}, this.value)" style="height:32px; font-size:12px; border:1px solid #d0d5dd; border-radius:6px; background:#fff; cursor:pointer; padding:0 8px;">
                                <option value="new" ${st==='new'?'selected':''}>New</option>
                                <option value="contacted" ${st==='contacted'?'selected':''}>Contacted</option>
                                <option value="confirmed" ${st==='confirmed'?'selected':''}>Confirmed</option>
                                <option value="paid" ${st==='paid'?'selected':''}>Paid</option>
                                <option value="cancelled" ${st==='cancelled'?'selected':''}>Cancelled</option>
                            </select>
                            <button type="button" onclick="openModal(${b.id})" style="background:#f2f4f7; border:none; width:32px; height:32px; border-radius:6px; cursor:pointer; color:#344054; display:inline-flex; align-items:center; justify-content:center;" title="View Details"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></button>
                            <button type="button" onclick="deleteBooking(${b.id})" style="background:#fef3f2; border:none; width:32px; height:32px; border-radius:6px; cursor:pointer; color:#b42318; display:inline-flex; align-items:center; justify-content:center;" title="Delete"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg></button>
                        </div>
                    </td>
                </tr>`;
            });

            tbody.innerHTML = html;
        }

        function updateStatus(id, newStatus) {
            fetch('/wp-json/ai-zippy-child/v1/bookings/status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-WP-Nonce': restNonce
                },
                body: JSON.stringify({ id: id, status: newStatus })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadBookings();
                }
            });
        }

        function deleteBooking(id) {
            if (!confirm('Are you sure you want to delete this booking entry?')) return;
            fetch('/wp-json/ai-zippy-child/v1/bookings/' + id, {
                method: 'DELETE',
                headers: { 'X-WP-Nonce': restNonce }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    loadBookings();
                }
            });
        }

        function openModal(id) {
            const item = bookingsCache.find(b => parseInt(b.id) === parseInt(id));
            if (!item) return;

            document.getElementById('modal-title').textContent = item.name + (item.company ? ' (' + item.company + ')' : '');
            document.getElementById('modal-sub').textContent = 'Submitted on ' + item.created_at;

            const bodyHtml = `
                <div style="background:#f9fafb; border-radius:8px; padding:16px; margin-bottom:16px; border:1px solid #eaecf0;">
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                        <div><strong>Email:</strong> <a href="mailto:${escapeHtml(item.email)}">${escapeHtml(item.email)}</a></div>
                        <div><strong>Phone / WhatsApp:</strong> <a href="tel:${escapeHtml(item.phone)}">${escapeHtml(item.phone)}</a></div>
                        <div><strong>Booking Type:</strong> ${item.type === 'group_enquiry' ? 'Group Enquiry' : 'Session Booking'}</div>
                        <div><strong>Status:</strong> ${escapeHtml(item.status).toUpperCase()}</div>
                    </div>
                </div>
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:16px;">
                    <div><strong>Event / Session Type:</strong> ${escapeHtml(item.event_type || 'N/A')}</div>
                    <div><strong>Group Size:</strong> ${escapeHtml(item.group_size || 'N/A')}</div>
                    <div><strong>Preferred Date:</strong> ${escapeHtml(item.preferred_date || 'N/A')}</div>
                    <div><strong>Preferred Time:</strong> ${escapeHtml(item.preferred_time || 'N/A')}</div>
                    ${item.coach_preference ? `<div><strong>Coaches Preference:</strong> ${escapeHtml(item.coach_preference)}</div>` : ''}
                </div>
                <div style="margin-bottom:16px;">
                    <strong>Additional Details / Special Requests:</strong>
                    <div style="background:#fff; border:1px solid #d0d5dd; border-radius:6px; padding:12px; margin-top:6px; min-height:60px; white-space:pre-wrap;">${escapeHtml(item.details || 'No additional details provided.')}</div>
                </div>
                <div style="background:#fffaeb; border:1px solid #fedf89; border-radius:8px; padding:16px; margin-bottom:16px;">
                    <strong style="color:#b54708;">Internal Notes</strong>
                    <p style="margin:4px 0 8px; font-size:12px; color:#475467;">Admin-only memo — never emailed to the customer. Handy while the booking is Contacted, before moving it to the next step.</p>
                    <textarea id="booking-notes" rows="3" placeholder="Admin notes..." style="width:100%; padding:10px 12px; border:1px solid #d0d5dd; border-radius:8px; font-size:13px; resize:vertical;">${escapeHtml(item.notes || '')}</textarea>
                    <button type="button" onclick="saveNotes(${item.id}, this)" style="margin-top:8px; height:34px; padding:0 16px; border:none; border-radius:8px; background:#b54708; color:#fff; font-weight:600; font-size:13px; cursor:pointer;">Save Notes</button>
                    <span id="notes-msg" style="margin-left:10px; font-size:12px;"></span>
                </div>
                ${renderOrderSection(item)}
            `;

            document.getElementById('modal-body').innerHTML = bodyHtml;
            document.getElementById('booking-modal').style.display = 'flex';
        }

        function renderOrderSection(item) {
            const cancelled = (item.status || '') === 'cancelled';
            const order = item.order || null;

            if (order) {
                const payRow = order.pay_url
                    ? `<div style="margin-top:10px;">
                         <div style="font-size:12px; color:#667085; margin-bottom:4px;">Payment link</div>
                         <div style="display:flex; gap:8px;">
                           <input type="text" readonly value="${escapeHtml(order.pay_url)}" id="pay-link-input" style="flex:1; height:34px; padding:0 10px; border:1px solid #d0d5dd; border-radius:6px; font-size:12px; color:#475467;">
                           <button type="button" onclick="copyPayLink()" style="height:34px; padding:0 12px; border:1px solid #d0d5dd; background:#fff; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">Copy</button>
                         </div>
                       </div>`
                    : `<div style="margin-top:10px; font-size:13px; color:#027a48; font-weight:600;">✓ Payment received</div>`;

                return `
                <div style="border:1px solid #d9d6fe; background:#f4f3ff; border-radius:8px; padding:16px;">
                    <div style="display:flex; justify-content:space-between; align-items:center;">
                        <strong style="color:#5925dc;">Order #${escapeHtml(order.number)}</strong>
                        <a href="${escapeHtml(order.edit_url)}" target="_blank" style="font-size:12px;">Open order ↗</a>
                    </div>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:10px; font-size:13px;">
                        <div><strong>Status:</strong> ${escapeHtml(order.status_name || order.status)}</div>
                        <div><strong>Total:</strong> ${escapeHtml(order.total_html)}</div>
                    </div>
                    ${payRow}
                    <button type="button" onclick="resendLink(${item.id}, this)" ${order.is_paid ? 'disabled' : ''} style="margin-top:12px; width:100%; height:38px; border:none; border-radius:8px; background:${order.is_paid ? '#e4e7ec' : '#1B3A1F'}; color:${order.is_paid ? '#98a2b3' : '#fff'}; font-weight:600; font-size:13px; cursor:${order.is_paid ? 'default' : 'pointer'};">Resend payment email</button>
                </div>`;
            }

            if (cancelled) {
                return `<div style="border:1px dashed #d0d5dd; border-radius:8px; padding:16px; color:#667085; font-size:13px;">This enquiry is cancelled. Reopen it (set status) to issue a quote.</div>`;
            }

            return `
            <div style="border:1px solid #cfe6c8; background:#f2f9ef; border-radius:8px; padding:16px;">
                <strong style="color:#1B3A1F;">Confirm &amp; Send Payment Link</strong>
                <p style="margin:6px 0 12px; font-size:12px; color:#475467;">Creates a WooCommerce order (1 order = this booking) and emails the customer a payment link. GST is added on top.</p>
                <div style="display:flex; gap:8px; align-items:center;">
                    <span style="font-size:13px; color:#475467;">Amount (excl. tax)</span>
                    <input type="number" id="confirm-amount" min="0" step="0.01" placeholder="0.00" style="flex:1; height:38px; padding:0 12px; border:1px solid #d0d5dd; border-radius:8px; font-size:14px;">
                </div>
                <textarea id="confirm-note" rows="2" placeholder="Optional note to the customer (shown in the email)..." style="width:100%; margin-top:8px; padding:10px 12px; border:1px solid #d0d5dd; border-radius:8px; font-size:13px; resize:vertical;"></textarea>
                <button type="button" onclick="confirmBooking(${item.id}, this)" style="margin-top:12px; width:100%; height:40px; border:none; border-radius:8px; background:#C41C1C; color:#fff; font-weight:700; font-size:13px; letter-spacing:0.03em; text-transform:uppercase; cursor:pointer;">Create Order &amp; Send</button>
                <div id="confirm-msg" style="margin-top:8px; font-size:12px;"></div>
            </div>`;
        }

        function copyPayLink() {
            const el = document.getElementById('pay-link-input');
            if (!el) return;
            el.select();
            navigator.clipboard.writeText(el.value).then(() => { el.blur(); });
        }

        function confirmBooking(id, btn) {
            const amount = parseFloat(document.getElementById('confirm-amount').value);
            const note   = document.getElementById('confirm-note').value;
            const msg    = document.getElementById('confirm-msg');
            if (!amount || amount <= 0) {
                msg.style.color = '#b42318';
                msg.textContent = 'Enter a valid amount.';
                return;
            }
            btn.disabled = true;
            btn.textContent = 'Creating…';
            fetch('/wp-json/ai-zippy-child/v1/bookings/confirm', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': restNonce },
                body: JSON.stringify({ id: id, amount: amount, note: note })
            })
            .then(res => res.json())
            .then(data => {
                msg.style.color = data.success ? '#027a48' : '#b42318';
                msg.textContent = data.message || (data.success ? 'Done.' : 'Failed.');
                if (data.success) {
                    setTimeout(() => { closeModal(); loadBookings(); }, 900);
                } else {
                    btn.disabled = false;
                    btn.textContent = 'Create Order & Send';
                }
            })
            .catch(() => {
                msg.style.color = '#b42318';
                msg.textContent = 'Request failed.';
                btn.disabled = false;
                btn.textContent = 'Create Order & Send';
            });
        }

        function saveNotes(id, btn) {
            const ta  = document.getElementById('booking-notes');
            const msg = document.getElementById('notes-msg');
            btn.disabled = true;
            fetch('/wp-json/ai-zippy-child/v1/bookings/notes', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': restNonce },
                body: JSON.stringify({ id: id, notes: ta.value })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    const item = bookingsCache.find(b => parseInt(b.id) === parseInt(id));
                    if (item) item.notes = data.notes;
                    msg.style.color = '#027a48';
                    msg.textContent = 'Saved ✓';
                    setTimeout(() => { msg.textContent = ''; }, 2000);
                } else {
                    msg.style.color = '#b42318';
                    msg.textContent = data.message || 'Failed.';
                }
            })
            .catch(() => { msg.style.color = '#b42318'; msg.textContent = 'Request failed.'; })
            .finally(() => { btn.disabled = false; });
        }

        function resendLink(id, btn) {
            btn.disabled = true;
            const original = btn.textContent;
            btn.textContent = 'Sending…';
            fetch('/wp-json/ai-zippy-child/v1/bookings/resend', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': restNonce },
                body: JSON.stringify({ id: id })
            })
            .then(res => res.json())
            .then(data => {
                btn.textContent = data.success ? 'Sent ✓' : 'Failed';
                setTimeout(() => { btn.disabled = false; btn.textContent = original; }, 2000);
            })
            .catch(() => { btn.disabled = false; btn.textContent = original; });
        }

        function closeModal() {
            document.getElementById('booking-modal').style.display = 'none';
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }

        document.addEventListener('DOMContentLoaded', loadBookings);
        </script>
        <?php
    }

    public static function renderAvailabilityTab(string $nonce): void
    {
        $settings = \AiZippyChild\BookingAvailability::get();
        $weekdays = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        ?>
        <div class="wrap" style="max-width:820px; margin:0 20px 40px 0; font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;">
            <h1 style="font-size:24px; font-weight:700; color:#101828; margin:0 0 4px;">Availability</h1>
            <p style="font-size:14px; color:#475467; margin:0 0 24px;">Controls the Preferred Time dropdown and the date picker on the public group enquiry form.</p>

            <!-- Time slots -->
            <div style="background:#fff; border:1px solid #eaecf0; border-radius:12px; padding:20px; margin-bottom:20px;">
                <h2 style="font-size:16px; font-weight:700; color:#101828; margin:0 0 4px;">Time slots</h2>
                <p style="font-size:13px; color:#475467; margin:0 0 14px;">Each slot is a start/end window. Windows may overlap. Leave the label blank to auto-format it (e.g. <em>10am – 12pm</em>). <strong>Slots</strong> = max bookings per date for that window; leave <em>0</em> for unlimited. These are the <strong>default</strong> slots for every open day — use <em>Date-specific time slots</em> below to override individual dates.</p>
                <div id="av-slots"></div>
                <button type="button" onclick="avAddSlot()" style="margin-top:10px; background:#f2f4f7; border:1px solid #d0d5dd; color:#344054; padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">+ Add slot</button>
            </div>

            <!-- Per-weekday overrides -->
            <div style="background:#fff; border:1px solid #eaecf0; border-radius:12px; padding:20px; margin-bottom:20px;">
                <h2 style="font-size:16px; font-weight:700; color:#101828; margin:0 0 4px;">Weekday time slots</h2>
                <p style="font-size:13px; color:#475467; margin:0 0 14px;">Recurring schedule per weekday (e.g. Mon 12pm–6pm, Tue 8am–4pm). Weekdays not listed use the default <strong>Time slots</strong> above. A <em>Date-specific</em> override below still wins over the weekday schedule.</p>
                <div id="av-weekdays"></div>
                <button type="button" onclick="avAddWeekdayOverride()" style="margin-top:10px; background:#f2f4f7; border:1px solid #d0d5dd; color:#344054; padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">+ Add weekday override</button>
            </div>

            <!-- Per-date overrides -->
            <div style="background:#fff; border:1px solid #eaecf0; border-radius:12px; padding:20px; margin-bottom:20px;">
                <h2 style="font-size:16px; font-weight:700; color:#101828; margin:0 0 4px;">Date-specific time slots</h2>
                <p style="font-size:13px; color:#475467; margin:0 0 14px;">Optional overrides for individual dates (e.g. a holiday with shorter hours). Dates not listed use the default <strong>Time slots</strong> above. Add a date with <em>no</em> slots to close just that one day.</p>
                <div id="av-overrides"></div>
                <button type="button" onclick="avAddOverride()" style="margin-top:10px; background:#f2f4f7; border:1px solid #d0d5dd; color:#344054; padding:8px 14px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">+ Add date override</button>
            </div>

            <!-- Weekly closed days -->
            <div style="background:#fff; border:1px solid #eaecf0; border-radius:12px; padding:20px; margin-bottom:20px;">
                <h2 style="font-size:16px; font-weight:700; color:#101828; margin:0 0 4px;">Weekly closed days</h2>
                <p style="font-size:13px; color:#475467; margin:0 0 14px;">Customers can't request these weekdays.</p>
                <div style="display:flex; flex-wrap:wrap; gap:14px;">
                    <?php foreach ($weekdays as $i => $label) : ?>
                        <label style="display:inline-flex; align-items:center; gap:6px; font-size:14px; color:#344054;">
                            <input type="checkbox" class="av-weekday" value="<?php echo $i; ?>" <?php checked(in_array($i, array_map('intval', $settings['closed_weekdays']), true)); ?>>
                            <?php echo esc_html($label); ?>
                        </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Blackout dates -->
            <div style="background:#fff; border:1px solid #eaecf0; border-radius:12px; padding:20px; margin-bottom:20px;">
                <h2 style="font-size:16px; font-weight:700; color:#101828; margin:0 0 4px;">Holiday / blackout dates</h2>
                <p style="font-size:13px; color:#475467; margin:0 0 14px;">One-off closures. Blocked on the enquiry form's date picker.</p>
                <div style="display:flex; gap:8px; align-items:center; margin-bottom:12px;">
                    <input type="date" id="av-blackout-input" style="height:38px; padding:0 12px; border:1px solid #d0d5dd; border-radius:8px; font-size:14px;">
                    <button type="button" onclick="avAddBlackout()" style="height:38px; padding:0 14px; background:#f2f4f7; border:1px solid #d0d5dd; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer;">Add date</button>
                </div>
                <div id="av-blackout-list" style="display:flex; flex-wrap:wrap; gap:8px;"></div>
            </div>

            <!-- Booking window -->
            <div style="background:#fff; border:1px solid #eaecf0; border-radius:12px; padding:20px; margin-bottom:20px;">
                <h2 style="font-size:16px; font-weight:700; color:#101828; margin:0 0 14px;">Booking window</h2>
                <div style="display:flex; gap:24px; flex-wrap:wrap;">
                    <label style="font-size:14px; color:#344054;">Minimum notice (days)<br>
                        <input type="number" id="av-min-notice" min="0" value="<?php echo (int) $settings['min_notice_days']; ?>" style="margin-top:6px; width:120px; height:38px; padding:0 12px; border:1px solid #d0d5dd; border-radius:8px; font-size:14px;">
                    </label>
                    <label style="font-size:14px; color:#344054;">Book up to (days ahead)<br>
                        <input type="number" id="av-max-advance" min="1" value="<?php echo (int) $settings['max_advance_days']; ?>" style="margin-top:6px; width:120px; height:38px; padding:0 12px; border:1px solid #d0d5dd; border-radius:8px; font-size:14px;">
                    </label>
                </div>
            </div>

            <button type="button" id="av-save" onclick="avSave(this)" style="background:#128c7e; color:#fff; border:none; padding:11px 22px; border-radius:8px; font-size:14px; font-weight:700; cursor:pointer;">Save availability settings</button>
            <span id="av-msg" style="margin-left:12px; font-size:13px;"></span>
        </div>

        <script>
        const avNonce = <?php echo json_encode($nonce); ?>;
        let avSlots    = <?php echo json_encode(array_values($settings['time_slots'])); ?>;
        let avBlackout = <?php echo json_encode(array_values($settings['blackout_dates'])); ?>;
        let avOverrides = avMapToList(<?php echo json_encode($settings['date_overrides']); ?>);
        let avWeekdays  = avWeekdayMapToList(<?php echo json_encode($settings['weekday_overrides']); ?>);
        const AV_WEEKDAY_LABELS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        function avMapToList(map) {
            return Object.keys(map || {}).sort().map(d => ({
                date: d,
                slots: (map[d] || []).map(s => ({ start: s.start, end: s.end, label: s.label, capacity: s.capacity }))
            }));
        }

        function avWeekdayMapToList(map) {
            return Object.keys(map || {}).map(k => parseInt(k, 10)).sort((a, b) => a - b).map(w => ({
                weekday: w,
                slots: (map[w] || []).map(s => ({ start: s.start, end: s.end, label: s.label, capacity: s.capacity }))
            }));
        }

        function avEsc(s) {
            return String(s == null ? '' : s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#039;'}[c]));
        }

        function avRenderSlots() {
            const wrap = document.getElementById('av-slots');
            if (!avSlots.length) {
                wrap.innerHTML = '<p style="font-size:13px; color:#98a2b3; margin:0 0 8px;">No slots — the Preferred Time field will be hidden.</p>';
                return;
            }
            wrap.innerHTML = avSlots.map((s, i) => `
                <div style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
                    <input type="time" value="${avEsc(s.start)}" onchange="avSlots[${i}].start=this.value" style="height:36px; padding:0 8px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                    <span style="color:#98a2b3;">→</span>
                    <input type="time" value="${avEsc(s.end)}" onchange="avSlots[${i}].end=this.value" style="height:36px; padding:0 8px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                    <input type="text" value="${avEsc(s.label)}" placeholder="Auto label" oninput="avSlots[${i}].label=this.value" style="flex:1; height:36px; padding:0 10px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                    <input type="number" min="0" value="${parseInt(s.capacity, 10) || 0}" title="Slots (0 = unlimited)" onchange="avSlots[${i}].capacity=parseInt(this.value,10)||0" style="width:64px; height:36px; padding:0 8px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                    <button type="button" onclick="avSlots.splice(${i},1); avRenderSlots();" style="width:36px; height:36px; border:1px solid #fecdca; background:#fef3f2; color:#b42318; border-radius:6px; cursor:pointer;">✕</button>
                </div>`).join('');
        }

        function avAddSlot() {
            avSlots.push({ start: '09:00', end: '11:00', label: '', capacity: 0 });
            avRenderSlots();
        }

        function avOverrideSlotRow(oi, si, s) {
            const base = `avOverrides[${oi}].slots[${si}]`;
            return `
                <div style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
                    <input type="time" value="${avEsc(s.start)}" onchange="${base}.start=this.value" style="height:36px; padding:0 8px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                    <span style="color:#98a2b3;">→</span>
                    <input type="time" value="${avEsc(s.end)}" onchange="${base}.end=this.value" style="height:36px; padding:0 8px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                    <input type="text" value="${avEsc(s.label)}" placeholder="Auto label" oninput="${base}.label=this.value" style="flex:1; height:36px; padding:0 10px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                    <input type="number" min="0" value="${parseInt(s.capacity, 10) || 0}" title="Slots (0 = unlimited)" onchange="${base}.capacity=parseInt(this.value,10)||0" style="width:64px; height:36px; padding:0 8px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                    <button type="button" onclick="avOverrides[${oi}].slots.splice(${si},1); avRenderOverrides();" style="width:36px; height:36px; border:1px solid #fecdca; background:#fef3f2; color:#b42318; border-radius:6px; cursor:pointer;">✕</button>
                </div>`;
        }

        function avRenderOverrides() {
            const wrap = document.getElementById('av-overrides');
            if (!avOverrides.length) {
                wrap.innerHTML = '<p style="font-size:13px; color:#98a2b3; margin:0 0 8px;">No date-specific overrides — every open day uses the default Time slots.</p>';
                return;
            }
            wrap.innerHTML = avOverrides.map((ov, oi) => `
                <div style="border:1px solid #eaecf0; border-radius:8px; padding:12px; margin-bottom:12px; background:#fcfcfd;">
                    <div style="display:flex; gap:8px; align-items:center; margin-bottom:10px;">
                        <strong style="font-size:13px; color:#344054;">Date</strong>
                        <input type="date" value="${avEsc(ov.date)}" onchange="avOverrides[${oi}].date=this.value" style="height:36px; padding:0 10px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                        <button type="button" onclick="avOverrides.splice(${oi},1); avRenderOverrides();" style="margin-left:auto; height:32px; padding:0 10px; border:1px solid #fecdca; background:#fef3f2; color:#b42318; border-radius:6px; cursor:pointer; font-size:12px;">Remove date</button>
                    </div>
                    ${(ov.slots || []).map((s, si) => avOverrideSlotRow(oi, si, s)).join('') || '<p style="font-size:12px; color:#98a2b3; margin:0 0 8px;">No slots — this date is closed.</p>'}
                    <button type="button" onclick="avOverrides[${oi}].slots.push({start:'09:00',end:'11:00',label:'',capacity:0}); avRenderOverrides();" style="background:#f2f4f7; border:1px solid #d0d5dd; color:#344054; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">+ Add slot</button>
                </div>`).join('');
        }

        function avAddOverride() {
            avOverrides.push({ date: '', slots: [] });
            avRenderOverrides();
        }

        function avWeekdaySlotRow(oi, si, s) {
            const base = `avWeekdays[${oi}].slots[${si}]`;
            return `
                <div style="display:flex; gap:8px; align-items:center; margin-bottom:8px;">
                    <input type="time" value="${avEsc(s.start)}" onchange="${base}.start=this.value" style="height:36px; padding:0 8px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                    <span style="color:#98a2b3;">→</span>
                    <input type="time" value="${avEsc(s.end)}" onchange="${base}.end=this.value" style="height:36px; padding:0 8px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                    <input type="text" value="${avEsc(s.label)}" placeholder="Auto label" oninput="${base}.label=this.value" style="flex:1; height:36px; padding:0 10px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                    <input type="number" min="0" value="${parseInt(s.capacity, 10) || 0}" title="Slots (0 = unlimited)" onchange="${base}.capacity=parseInt(this.value,10)||0" style="width:64px; height:36px; padding:0 8px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                    <button type="button" onclick="avWeekdays[${oi}].slots.splice(${si},1); avRenderWeekdays();" style="width:36px; height:36px; border:1px solid #fecdca; background:#fef3f2; color:#b42318; border-radius:6px; cursor:pointer;">✕</button>
                </div>`;
        }

        function avRenderWeekdays() {
            const wrap = document.getElementById('av-weekdays');
            if (!avWeekdays.length) {
                wrap.innerHTML = '<p style="font-size:13px; color:#98a2b3; margin:0 0 8px;">No weekday overrides — every open day uses the default Time slots.</p>';
                return;
            }
            wrap.innerHTML = avWeekdays.map((ov, oi) => `
                <div style="border:1px solid #eaecf0; border-radius:8px; padding:12px; margin-bottom:12px; background:#fcfcfd;">
                    <div style="display:flex; gap:8px; align-items:center; margin-bottom:10px;">
                        <strong style="font-size:13px; color:#344054;">Weekday</strong>
                        <select onchange="avWeekdays[${oi}].weekday=parseInt(this.value,10)" style="height:36px; padding:0 10px; border:1px solid #d0d5dd; border-radius:6px; font-size:13px;">
                            ${AV_WEEKDAY_LABELS.map((label, idx) => `<option value="${idx}"${ov.weekday === idx ? ' selected' : ''}>${label}</option>`).join('')}
                        </select>
                        <button type="button" onclick="avWeekdays.splice(${oi},1); avRenderWeekdays();" style="margin-left:auto; height:32px; padding:0 10px; border:1px solid #fecdca; background:#fef3f2; color:#b42318; border-radius:6px; cursor:pointer; font-size:12px;">Remove weekday</button>
                    </div>
                    ${(ov.slots || []).map((s, si) => avWeekdaySlotRow(oi, si, s)).join('') || '<p style="font-size:12px; color:#98a2b3; margin:0 0 8px;">No slots — this weekday is closed.</p>'}
                    <button type="button" onclick="avWeekdays[${oi}].slots.push({start:'09:00',end:'11:00',label:'',capacity:0}); avRenderWeekdays();" style="background:#f2f4f7; border:1px solid #d0d5dd; color:#344054; padding:6px 12px; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">+ Add slot</button>
                </div>`).join('');
        }

        function avAddWeekdayOverride() {
            avWeekdays.push({ weekday: 1, slots: [] });
            avRenderWeekdays();
        }

        function avRenderBlackout() {
            const wrap = document.getElementById('av-blackout-list');
            if (!avBlackout.length) {
                wrap.innerHTML = '<span style="font-size:13px; color:#98a2b3;">No blackout dates set.</span>';
                return;
            }
            wrap.innerHTML = avBlackout.map((d, i) => `
                <span style="display:inline-flex; align-items:center; gap:6px; background:#fef3f2; color:#b42318; border:1px solid #fecdca; border-radius:16px; padding:4px 8px 4px 12px; font-size:13px;">
                    ${avEsc(d)}
                    <button type="button" onclick="avBlackout.splice(${i},1); avRenderBlackout();" style="border:none; background:none; color:#b42318; cursor:pointer; font-size:13px;">✕</button>
                </span>`).join('');
        }

        function avAddBlackout() {
            const el = document.getElementById('av-blackout-input');
            const v  = el.value;
            if (v && avBlackout.indexOf(v) === -1) {
                avBlackout.push(v);
                avBlackout.sort();
                avRenderBlackout();
            }
            el.value = '';
        }

        function avSave(btn) {
            const msg = document.getElementById('av-msg');
            btn.disabled = true;
            btn.textContent = 'Saving…';
            const payload = {
                time_slots: avSlots,
                closed_weekdays: Array.from(document.querySelectorAll('.av-weekday:checked')).map(c => parseInt(c.value, 10)),
                blackout_dates: avBlackout,
                min_notice_days: parseInt(document.getElementById('av-min-notice').value, 10) || 0,
                max_advance_days: parseInt(document.getElementById('av-max-advance').value, 10) || 90,
                weekday_overrides: avWeekdays,
                date_overrides: avOverrides,
            };
            fetch('/wp-json/ai-zippy-child/v1/bookings/availability', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-WP-Nonce': avNonce },
                body: JSON.stringify(payload)
            })
            .then(r => r.json())
            .then(data => {
                msg.style.color = data.success ? '#027a48' : '#b42318';
                msg.textContent = data.message || (data.success ? 'Saved.' : 'Failed.');
                if (data.success && data.settings) {
                    avSlots    = data.settings.time_slots;
                    avBlackout = data.settings.blackout_dates;
                    avOverrides = avMapToList(data.settings.date_overrides || {});
                    avWeekdays  = avWeekdayMapToList(data.settings.weekday_overrides || {});
                    avRenderSlots();
                    avRenderBlackout();
                    avRenderOverrides();
                    avRenderWeekdays();
                }
            })
            .catch(() => { msg.style.color = '#b42318'; msg.textContent = 'Request failed.'; })
            .finally(() => { btn.disabled = false; btn.textContent = 'Save availability settings'; });
        }

        avRenderSlots();
        avRenderBlackout();
        avRenderOverrides();
        avRenderWeekdays();
        </script>
        <?php
    }
}
