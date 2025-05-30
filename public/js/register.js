// public/js/register.js

'use strict';

document.addEventListener('DOMContentLoaded', () => {
  const form    = document.querySelector('.login-form');
  const pwd     = form.querySelector('input[name="password"]');
  const pwdConf = form.querySelector('input[name="password_confirm"]');  // nazwa zgodna z widokiem
  const errBox  = document.getElementById('js-errors');

  form.addEventListener('submit', (e) => {
    const errors = [];
    const val    = pwd.value;

    // Reset error box
    errBox.innerHTML   = '';
    errBox.style.display = 'none';

    // Validate password
    if (val.length < 8) {
      errors.push('Hasło musi mieć co najmniej 8 znaków.');
    }
    if (!/[A-Z]/.test(val)) {
      errors.push('Hasło musi zawierać co najmniej jedną wielką literę.');
    }
    if (!/[a-z]/.test(val)) {
      errors.push('Hasło musi zawierać co najmniej jedną małą literę.');
    }
    if (!/\d/.test(val)) {
      errors.push('Hasło musi zawierać co najmniej jedną cyfrę.');
    }
    // Confirm match
    if (val !== pwdConf.value) {
      errors.push('Hasła nie są takie same.');
    }

    if (errors.length > 0) {
      e.preventDefault();
      errBox.innerHTML   = '<ul><li>' + errors.join('</li><li>') + '</li></ul>';
      errBox.style.display = 'block';
    }
  });
});
