/**
 * Front-end behaviour for the Booking Calendar block (vanilla JS, no jQuery).
 *
 * Reads the JSON config blob rendered by render.php, wires the
 * studio/programme selects, paints the month grid from the public
 * /booking/availability endpoint (live remaining places, never prices) and
 * submits enquiries to /booking/enquiry. All state is scoped to the block
 * instance, so multiple calendars can live on one page.
 */

const BOOKING_MONTHS = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
const BOOKING_MONTHS_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
const BOOKING_DAYS_SHORT = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
const BOOKING_WEEKDAYS = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

function escapeHtml(value) {
  return String(value).replace(/[&<>"']/g, (ch) => ({
    '&': '&amp;',
    '<': '&lt;',
    '>': '&gt;',
    '"': '&quot;',
    "'": '&#039;',
  }[ch]));
}

function bookingMonthLabel(ym) {
  const parts = ym.split('-');
  return BOOKING_MONTHS[parseInt(parts[1], 10) - 1] + ' ' + parts[0];
}

function bookingShiftMonth(ym, delta) {
  let year = parseInt(ym.slice(0, 4), 10);
  let month = parseInt(ym.slice(5, 7), 10) + delta;
  while (month < 1) {
    month += 12;
    year -= 1;
  }
  while (month > 12) {
    month -= 12;
    year += 1;
  }
  return year + '-' + String(month).padStart(2, '0');
}

function bookingWeekdayOf(ymd) {
  const parts = ymd.split('-').map(Number);
  return new Date(Date.UTC(parts[0], parts[1] - 1, parts[2])).getUTCDay();
}

function bookingFormatDate(ymd) {
  const parts = ymd.split('-').map(Number);
  return BOOKING_DAYS_SHORT[bookingWeekdayOf(ymd)] + ', ' + parts[2] + ' ' + BOOKING_MONTHS_SHORT[parts[1] - 1] + ' ' + parts[0];
}

function setupBookingCalendar(root) {
  const configEl = root.querySelector('.achiever-booking-calendar__config');
  if (!configEl) {
    return;
  }
  let config;
  try {
    config = JSON.parse(configEl.textContent);
  } catch (error) {
    return;
  }

  const studioSelect = root.querySelector('[data-booking-calendar-studio]');
  const programmeSelect = root.querySelector('[data-booking-calendar-programme]');
  const body = root.querySelector('[data-booking-calendar-body]');
  const panel = root.querySelector('[data-booking-calendar-panel]');

  const i18n = config.i18n || {};
  const remainingText = i18n.remainingText || '%d places left';
  const unlimitedText = i18n.unlimitedText || 'Open';
  const fullText = i18n.fullText || 'FULL';
  const enrollText = i18n.enrollText || 'ENROLL NOW';
  const daySlotsText = i18n.daySlotsText || '%d slots';
  const noSlotsText = i18n.noSlotsText || 'No classes on this date.';
  const hint = i18n.hint || 'Pick a studio and programme to see live availability.';

  const state = {
    studio: config.selectedStudio || '',
    programme: config.selectedProgramme || '',
    month: config.month || null,
    ym: '',
    expandedDate: '',
    selectedSlot: '',
  };
  if (state.month && /^\d{4}-\d{2}$/.test(state.month.ym || '')) {
    state.ym = state.month.ym;
  } else {
    state.month = null;
  }

  function studioDisplayName(slug) {
    const entry = (config.studios || []).find((studio) => studio.slug === slug);
    return entry ? entry.name : slug;
  }

  function monthDays(ymd) {
    if (state.month && state.month.ym === state.ym && state.month.days && state.month.days[ymd]) {
      return state.month.days[ymd];
    }
    return null;
  }

  function classifyDay(ymd) {
    const month = state.month;
    if (!month || !month.bounds) {
      return 'out';
    }
    if (ymd < month.bounds.min || ymd > month.bounds.max) {
      return 'out';
    }
    if ((month.closedWeekdays || []).indexOf(bookingWeekdayOf(ymd)) !== -1) {
      return 'closed';
    }
    if ((month.blackoutDates || []).indexOf(ymd) !== -1) {
      return 'closed';
    }
    return 'open';
  }

  function canPrev() {
    return state.month && state.month.bounds && state.ym > state.month.bounds.min.slice(0, 7);
  }

  function canNext() {
    return state.month && state.month.bounds && state.ym < state.month.bounds.max.slice(0, 7);
  }

  function renderCalendar() {
    if (!body) {
      return;
    }
    if (!state.studio || !state.programme || !state.month) {
      body.innerHTML = '<p class="achiever-booking-calendar__hint">' + escapeHtml(hint) + '</p>';
      return;
    }

    const year = parseInt(state.ym.slice(0, 4), 10);
    const month = parseInt(state.ym.slice(5, 7), 10);
    const firstWeekday = new Date(Date.UTC(year, month - 1, 1)).getUTCDay();
    const daysInMonth = new Date(Date.UTC(year, month, 0)).getUTCDate();

    let html = '<div class="achiever-booking-calendar__calendar">';
    html += '<div class="achiever-booking-calendar__nav">';
    html += '<button type="button" class="achiever-booking-calendar__nav-btn" data-booking-calendar-prev aria-label="Previous month"' + (canPrev() ? '' : ' disabled') + '>&lsaquo;</button>';
    html += '<span class="achiever-booking-calendar__month-label">' + escapeHtml(bookingMonthLabel(state.ym)) + '</span>';
    html += '<button type="button" class="achiever-booking-calendar__nav-btn" data-booking-calendar-next aria-label="Next month"' + (canNext() ? '' : ' disabled') + '>&rsaquo;</button>';
    html += '</div>';
    html += '<div class="achiever-booking-calendar__grid">';
    BOOKING_WEEKDAYS.forEach((weekday) => {
      html += '<span class="achiever-booking-calendar__weekday">' + weekday + '</span>';
    });
    for (let i = 0; i < firstWeekday; i++) {
      html += '<span class="achiever-booking-calendar__day achiever-booking-calendar__day--pad" aria-hidden="true"></span>';
    }
    for (let day = 1; day <= daysInMonth; day++) {
      const ymd = state.ym + '-' + String(day).padStart(2, '0');
      const cls = classifyDay(ymd);
      const dayNum = '<span class="achiever-booking-calendar__day-num">' + day + '</span>';
      if (cls !== 'open') {
        html += '<span class="achiever-booking-calendar__day achiever-booking-calendar__day--' + cls + '" aria-disabled="true">' + dayNum + '</span>';
        continue;
      }
      const info = monthDays(ymd);
      let classes = 'achiever-booking-calendar__day';
      let meta = '';
      if (info) {
        const slots = info.slots || [];
        const bookable = slots.filter((slot) => slot[1] === null || slot[1] > 0).length;
        if (slots.length === 0) {
          meta = noSlotsText;
        } else if (bookable === 0) {
          meta = fullText;
          classes += ' achiever-booking-calendar__day--full';
        } else {
          meta = daySlotsText.replace('%d', String(bookable));
        }
      }
      if (state.expandedDate === ymd) {
        classes += ' achiever-booking-calendar__day--expanded';
      }
      html += '<button type="button" class="' + classes + '" data-date="' + escapeHtml(ymd) + '" aria-expanded="' + (state.expandedDate === ymd) + '">';
      html += dayNum;
      if (meta) {
        html += '<span class="achiever-booking-calendar__day-meta">' + escapeHtml(meta) + '</span>';
      }
      html += '</button>';
    }
    html += '</div>';
    html += '<div class="achiever-booking-calendar__slots" data-booking-calendar-slots' + (state.expandedDate ? '' : ' hidden') + '></div>';
    html += '</div>';
    body.innerHTML = html;
  }

  function slotsEl() {
    return body ? body.querySelector('[data-booking-calendar-slots]') : null;
  }

  function showCalendarMessage(message) {
    const el = slotsEl();
    if (!el) {
      return;
    }
    el.hidden = false;
    el.innerHTML = '<p class="achiever-booking-calendar__slots-empty">' + escapeHtml(message) + '</p>';
  }

  function renderSlots(date, rows) {
    const el = slotsEl();
    if (!el) {
      return;
    }
    if (!rows || rows.length === 0) {
      el.hidden = false;
      el.innerHTML = '<p class="achiever-booking-calendar__slots-empty">' + escapeHtml(noSlotsText) + '</p>';
      return;
    }
    let html = '<p class="achiever-booking-calendar__slots-date">' + escapeHtml(bookingFormatDate(date)) + '</p>';
    rows.forEach((row) => {
      const label = row[0];
      const remaining = row[1];
      const full = remaining === 0;
      const remainingLabel = remaining === null ? unlimitedText : remainingText.replace('%d', String(remaining));
      html += '<div class="achiever-booking-calendar__slot' + (full ? ' achiever-booking-calendar__slot--full' : '') + (state.selectedSlot === label ? ' achiever-booking-calendar__slot--selected' : '') + '">';
      html += '<span class="achiever-booking-calendar__slot-label">' + escapeHtml(label) + '</span>';
      html += '<span class="achiever-booking-calendar__remaining">' + escapeHtml(remainingLabel) + '</span>';
      if (full) {
        html += '<button type="button" class="achiever-booking-calendar__enroll" disabled>' + escapeHtml(fullText) + '</button>';
      } else {
        html += '<button type="button" class="achiever-booking-calendar__enroll achiever-btn achiever-btn--primary" data-slot="' + escapeHtml(label) + '">' + escapeHtml(enrollText) + '</button>';
      }
      html += '</div>';
    });
    el.hidden = false;
    el.innerHTML = html;
  }

  function fetchAvailability(options) {
    const params = new URLSearchParams();
    params.set('studio', state.studio);
    params.set('programme', state.programme);
    params.set('month', state.ym);
    if (options && options.date) {
      params.set('date', options.date);
    }
    return fetch(config.restUrl + '/booking/availability?' + params.toString())
      .then((response) => response.json().then((data) => {
        if (!response.ok) {
          throw new Error((data && data.message) || 'Could not load availability.');
        }
        return data;
      }));
  }

  function applyMonthData(data) {
    state.month = {
      ym: state.ym,
      bounds: data.bounds,
      closedWeekdays: data.closedWeekdays || [],
      blackoutDates: data.blackoutDates || [],
      days: (state.month && state.month.ym === state.ym && state.month.days) || {},
    };
  }

  function loadFirstMonth() {
    const now = new Date();
    state.ym = now.getFullYear() + '-' + String(now.getMonth() + 1).padStart(2, '0');
    state.month = null;
    if (body) {
      body.innerHTML = '<p class="achiever-booking-calendar__hint">Loading&hellip;</p>';
    }
    fetchAvailability({})
      .then((data) => {
        applyMonthData(data);
        const minYm = data.bounds.min.slice(0, 7);
        const maxYm = data.bounds.max.slice(0, 7);
        if (state.ym < minYm) {
          state.ym = minYm;
        } else if (state.ym > maxYm) {
          state.ym = maxYm;
        }
        state.month.ym = state.ym;
        renderCalendar();
      })
      .catch((error) => {
        state.month = null;
        renderCalendar();
        showCalendarMessage(error.message);
      });
  }

  function goToMonth(ym) {
    state.ym = ym;
    state.expandedDate = '';
    state.selectedSlot = '';
    if (panel) {
      panel.hidden = true;
    }
    renderCalendar();
    fetchAvailability({})
      .then((data) => {
        applyMonthData(data);
        renderCalendar();
      })
      .catch((error) => showCalendarMessage(error.message));
  }

  function loadDaySlots(date) {
    const el = slotsEl();
    if (el) {
      el.hidden = false;
      el.innerHTML = '<p class="achiever-booking-calendar__slots-empty">Loading&hellip;</p>';
    }
    fetchAvailability({ date })
      .then((data) => {
        if (state.expandedDate !== date) {
          return;
        }
        applyMonthData(data);
        if (state.month.days) {
          state.month.days[date] = { open: true, slots: data.slotAvailability || [] };
        }
        renderCalendar();
        renderSlots(date, data.slotAvailability || []);
      })
      .catch((error) => {
        if (state.expandedDate === date) {
          showCalendarMessage(error.message);
        }
      });
  }

  function toggleDay(date) {
    if (state.expandedDate === date) {
      state.expandedDate = '';
      state.selectedSlot = '';
      if (panel) {
        panel.hidden = true;
      }
      renderCalendar();
      return;
    }
    state.expandedDate = date;
    state.selectedSlot = '';
    if (panel) {
      panel.hidden = true;
    }
    renderCalendar();
    loadDaySlots(date);
  }

  function openPanel(slotLabel) {
    state.selectedSlot = slotLabel;
    renderSlots(state.expandedDate, currentSlotRows());
    if (!panel) {
      return;
    }
    const studioName = studioDisplayName(state.studio);
    const set = (selector, value) => {
      const el = panel.querySelector(selector);
      if (el) {
        el.textContent = value;
      }
    };
    set('[data-booking-calendar-summary-studio]', studioName);
    set('[data-booking-calendar-summary-programme]', state.programme);
    set('[data-booking-calendar-summary-date]', bookingFormatDate(state.expandedDate));
    set('[data-booking-calendar-summary-time]', slotLabel);

    const field = (selector, value) => {
      const el = panel.querySelector(selector);
      if (el) {
        el.value = value;
      }
    };
    field('[data-booking-calendar-field-studio]', studioName);
    field('[data-booking-calendar-field-programme]', state.programme);
    field('[data-booking-calendar-field-date]', state.expandedDate);
    field('[data-booking-calendar-field-time]', slotLabel);

    const status = panel.querySelector('[role="status"]');
    if (status) {
      status.textContent = '';
    }
    panel.hidden = false;
    panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    const nameInput = panel.querySelector('input[name="name"]');
    if (nameInput) {
      nameInput.focus();
    }
  }

  function currentSlotRows() {
    const info = monthDays(state.expandedDate);
    return info ? info.slots || [] : [];
  }

  if (body) {
    body.addEventListener('click', (event) => {
      const enrollBtn = event.target.closest('[data-slot]');
      if (enrollBtn && !enrollBtn.disabled) {
        openPanel(enrollBtn.getAttribute('data-slot'));
        return;
      }
      const dayBtn = event.target.closest('button[data-date]');
      if (dayBtn) {
        toggleDay(dayBtn.getAttribute('data-date'));
        return;
      }
      const prevBtn = event.target.closest('[data-booking-calendar-prev]');
      if (prevBtn && !prevBtn.disabled) {
        goToMonth(bookingShiftMonth(state.ym, -1));
        return;
      }
      const nextBtn = event.target.closest('[data-booking-calendar-next]');
      if (nextBtn && !nextBtn.disabled) {
        goToMonth(bookingShiftMonth(state.ym, 1));
      }
    });
  }

  if (studioSelect) {
    studioSelect.addEventListener('change', () => {
      state.studio = studioSelect.value;
      state.programme = '';
      state.month = null;
      state.ym = '';
      state.expandedDate = '';
      state.selectedSlot = '';
      if (programmeSelect) {
        programmeSelect.value = '';
        programmeSelect.disabled = state.studio === '';
      }
      if (panel) {
        panel.hidden = true;
      }
      renderCalendar();
    });
  }

  if (programmeSelect) {
    programmeSelect.addEventListener('change', () => {
      state.programme = programmeSelect.value;
      state.expandedDate = '';
      state.selectedSlot = '';
      if (panel) {
        panel.hidden = true;
      }
      if (!state.studio || !state.programme) {
        state.month = null;
        state.ym = '';
        renderCalendar();
        return;
      }
      loadFirstMonth();
    });
  }

  if (panel) {
    panel.addEventListener('submit', async (event) => {
      event.preventDefault();
      if (!state.selectedSlot || !state.expandedDate) {
        return;
      }
      const submitBtn = panel.querySelector('button[type="submit"]');
      const status = panel.querySelector('[role="status"]');
      if (!submitBtn) {
        return;
      }
      const submitText = submitBtn.textContent;

      submitBtn.disabled = true;
      submitBtn.textContent = 'Submitting…';
      panel.setAttribute('aria-busy', 'true');
      if (status) {
        status.textContent = '';
      }

      try {
        const response = await fetch(config.restUrl + '/booking/enquiry', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(Object.fromEntries(new FormData(panel))),
        });
        const result = await response.json();
        if (!response.ok) {
          throw new Error((result && result.message) || 'Something went wrong');
        }
        panel.innerHTML = '<p class="achiever-booking-calendar__success">' + escapeHtml(config.successMessage || 'Thank you!') + '</p>';
        panel.removeAttribute('aria-busy');
        if (state.expandedDate) {
          loadDaySlots(state.expandedDate);
        }
      } catch (error) {
        submitBtn.disabled = false;
        submitBtn.textContent = submitText;
        panel.removeAttribute('aria-busy');
        if (status) {
          status.textContent = error.message;
        }
      }
    });
  }

  renderCalendar();
}

function initBookingCalendars() {
  document.querySelectorAll('[data-booking-calendar]').forEach((root) => {
    if (root.dataset.bookingCalendarInitialized === 'true') {
      return;
    }
    root.dataset.bookingCalendarInitialized = 'true';
    setupBookingCalendar(root);
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initBookingCalendars);
} else {
  initBookingCalendars();
}
