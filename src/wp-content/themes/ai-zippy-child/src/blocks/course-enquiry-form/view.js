function initEnquiryForms() {
  document.querySelectorAll('[data-enquiry-form]').forEach((form) => {
    if (form.dataset.enquiryInitialized === 'true') return;
    form.dataset.enquiryInitialized = 'true';
    form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const submitBtn = form.querySelector('button[type="submit"]');
    const status = form.querySelector('[role="status"]');
    if (!submitBtn) return;

    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';
    form.setAttribute('aria-busy', 'true');
    if (status) status.textContent = '';

    try {
      const response = await fetch('/wp-json/achiever-art/v1/enquiry', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });
      const result = await response.json();
      if (response.ok) {
        form.reset();
        if (status) status.textContent = result.message || form.dataset.successMessage || 'Thank you!';
        submitBtn.disabled = false;
        submitBtn.textContent = form.dataset.submitText || 'SEND ENQUIRY';
        form.removeAttribute('aria-busy');
      } else {
        throw new Error(result.message || 'Something went wrong');
      }
    } catch (error) {
      submitBtn.disabled = false;
      submitBtn.textContent = form.dataset.submitText || 'SEND ENQUIRY';
      form.removeAttribute('aria-busy');
      if (status) status.textContent = error.message;
    }
    });
  });
}

if (document.readyState === 'loading') {
  document.addEventListener('DOMContentLoaded', initEnquiryForms);
} else {
  initEnquiryForms();
}
