// VALT Academy — Registration Form JS

(function () {
  'use strict';

  var currentStep = 1;
  var totalSteps  = 4;
  var schoolsData = {};

  // Load schools JSON
  fetch('data/schools.json')
    .then(function (r) { return r.json(); })
    .then(function (data) { schoolsData = data; });

  // Province → School cascade
  var provinceSelect = document.getElementById('province');
  var schoolSelect   = document.getElementById('school_name');
  var schoolOtherWrap = document.getElementById('school_other_wrap');
  var schoolOtherInput = document.getElementById('school_other');

  if (provinceSelect) {
    provinceSelect.addEventListener('change', function () {
      var province = this.value;
      schoolSelect.innerHTML = '<option value="">-- Select School --</option>';
      schoolOtherWrap.style.display = 'none';
      schoolOtherInput.required = false;

      if (province && schoolsData[province]) {
        schoolsData[province].forEach(function (school) {
          var opt = document.createElement('option');
          opt.value = school;
          opt.textContent = school;
          schoolSelect.appendChild(opt);
        });
      }
      var otherOpt = document.createElement('option');
      otherOpt.value = 'Other';
      otherOpt.textContent = 'Other (not listed)';
      schoolSelect.appendChild(otherOpt);
    });
  }

  if (schoolSelect) {
    schoolSelect.addEventListener('change', function () {
      if (this.value === 'Other') {
        schoolOtherWrap.style.display = 'block';
        schoolOtherInput.required = true;
      } else {
        schoolOtherWrap.style.display = 'none';
        schoolOtherInput.required = false;
      }
    });
  }

  // Validation rules per field
  function validateField(input) {
    var val   = input.value.trim();
    var name  = input.name;
    var error = '';

    switch (name) {
      case 'first_name':
      case 'last_name':
        if (!val) error = 'This field is required.';
        else if (!/^[a-zA-Z\s'\-]{2,50}$/.test(val)) error = 'Letters only, 2–50 characters.';
        break;

      case 'date_of_birth':
        if (!val) { error = 'Date of birth is required.'; break; }
        var dob = new Date(val);
        var age = Math.floor((Date.now() - dob) / (365.25 * 24 * 3600 * 1000));
        if (isNaN(age) || age < 12 || age > 22) error = 'Age must be between 12 and 22.';
        break;

      case 'gender':
        if (!val) error = 'Please select your gender.';
        break;

      case 'whatsapp_number':
      case 'parent_guardian_number':
        if (!val) error = 'This field is required.';
        else if (!/^\+?[0-9\s\-]{10,15}$/.test(val)) error = 'Enter a valid phone number (10+ digits).';
        break;

      case 'other_number':
        if (val && !/^\+?[0-9\s\-]{10,15}$/.test(val)) error = 'Enter a valid phone number.';
        break;

      case 'email':
        if (!val) error = 'Email is required.';
        else if (!/^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(val)) error = 'Enter a valid email address.';
        break;

      case 'grade':
        if (!val) error = 'Please select your grade.';
        break;

      case 'province':
        if (!val) error = 'Please select your province.';
        break;

      case 'city':
        if (!val) error = 'Please enter your city or town.';
        break;

      case 'school_name':
        if (!val) error = 'Please select your school.';
        break;

      case 'school_other':
        if (schoolSelect && schoolSelect.value === 'Other' && !val)
          error = 'Please enter your school name.';
        break;
    }

    showFieldState(input, error);
    return error === '';
  }

  function showFieldState(input, error) {
    var errEl = input.parentElement.querySelector('.field-error') ||
                input.closest('.reg-form-group').querySelector('.field-error');
    input.classList.remove('is-valid', 'is-invalid');
    if (error) {
      input.classList.add('is-invalid');
      if (errEl) { errEl.textContent = error; errEl.classList.add('show'); }
    } else if (input.value.trim()) {
      input.classList.add('is-valid');
      if (errEl) errEl.classList.remove('show');
    }
  }

  // Attach live validation
  document.querySelectorAll('.reg-input, .reg-select').forEach(function (el) {
    el.addEventListener('blur', function () { validateField(this); });
    el.addEventListener('input', function () {
      if (this.classList.contains('is-invalid')) validateField(this);
    });
  });

  // Validate all fields in current step
  function validateStep(step) {
    var pane   = document.querySelector('.step-pane[data-step="' + step + '"]');
    var fields = pane.querySelectorAll('.reg-input[required], .reg-select[required]');
    var valid  = true;
    fields.forEach(function (f) {
      if (!validateField(f)) valid = false;
    });
    return valid;
  }

  // Step navigation
  function goToStep(step) {
    document.querySelectorAll('.step-pane').forEach(function (p) { p.classList.remove('active'); });
    document.querySelector('.step-pane[data-step="' + step + '"]').classList.add('active');

    // Update indicators
    document.querySelectorAll('.step-item').forEach(function (item, idx) {
      item.classList.remove('active', 'completed');
      var n = idx + 1;
      if (n < step)       item.classList.add('completed');
      else if (n === step) item.classList.add('active');
    });

    // Update progress bar
    var pct = ((step - 1) / (totalSteps - 1)) * 100;
    document.querySelector('.progress-fill').style.width = pct + '%';

    currentStep = step;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }

  // Next buttons
  document.querySelectorAll('.btn-reg-next').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (validateStep(currentStep)) goToStep(currentStep + 1);
    });
  });

  // Prev buttons
  document.querySelectorAll('.btn-reg-prev').forEach(function (btn) {
    btn.addEventListener('click', function () {
      goToStep(currentStep - 1);
    });
  });

  // Final submit
  var form = document.getElementById('registerForm');
  if (form) {
    form.addEventListener('submit', function (e) {
      if (!validateStep(currentStep)) {
        e.preventDefault();
        return;
      }
      var btn = form.querySelector('.btn-reg-submit');
      btn.textContent = 'Submitting...';
      btn.disabled = true;
    });
  }

})();
