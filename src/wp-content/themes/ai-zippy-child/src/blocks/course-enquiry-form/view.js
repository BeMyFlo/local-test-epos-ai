document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('achiever-enquiry-form');
  if (!form) return;

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(form);
    const data = Object.fromEntries(formData.entries());
    const submitBtn = form.querySelector('button[type="submit"]');

    submitBtn.disabled = true;
    submitBtn.textContent = 'Sending...';

    try {
      const response = await fetch('/wp-json/achiever-art/v1/enquiry', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
      });
      const result = await response.json();
      if (response.ok) {
        form.innerHTML = '<p class="achiever-enquiry-form__success">' + (result.message || 'Thank you!') + '</p>';
      } else {
        throw new Error(result.message || 'Something went wrong');
      }
    } catch (error) {
      submitBtn.disabled = false;
      submitBtn.textContent = form.dataset.submitText || 'SEND ENQUIRY';
      alert(error.message);
    }
  });
});
