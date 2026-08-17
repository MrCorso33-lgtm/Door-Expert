/**
 * contact.js - Kontakt forma (AJAX, quote model).
 *
 * Salje POST na admin-ajax (action: door_expert_contact) + nonce; prikazuje
 * success/error stanje. Handler: inc/contact-form.php.
 */
(function () {
  'use strict';

  var form = document.getElementById('contactForm');
  if (!form || typeof window.doorExpertContact === 'undefined') {
    return;
  }

  var cfg = window.doorExpertContact;
  var submitBtn = document.getElementById('submitBtn');
  var successBox = document.getElementById('contactSuccess');

  function clearErrors() {
    var errs = form.querySelectorAll('.form-field__error');
    for (var i = 0; i < errs.length; i++) {
      errs[i].textContent = '';
    }
  }

  function showFieldErrors(fields) {
    if (!fields) {
      return;
    }
    Object.keys(fields).forEach(function (name) {
      var el = document.getElementById(name + '-error');
      if (el) {
        el.textContent = fields[name];
      }
    });
  }

  function setLoading(state) {
    if (!submitBtn) {
      return;
    }
    submitBtn.disabled = state;
    submitBtn.classList.toggle('is-loading', state);
  }

  form.addEventListener('submit', function (e) {
    e.preventDefault();
    clearErrors();

    var data = new FormData(form);
    data.append('action', 'door_expert_contact');
    data.append('nonce', cfg.nonce);

    setLoading(true);

    fetch(cfg.ajaxurl, { method: 'POST', credentials: 'same-origin', body: data })
      .then(function (r) {
        return r.json().then(function (j) {
          return { ok: r.ok, j: j };
        });
      })
      .then(function (res) {
        if (res.j && res.j.success) {
          form.hidden = true;
          if (successBox) {
            successBox.hidden = false;
            successBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
          }
          return;
        }
        var d = (res.j && res.j.data) ? res.j.data : {};
        showFieldErrors(d.fields);
        setLoading(false);
        if (d.message) {
          window.alert(d.message);
        }
      })
      .catch(function () {
        setLoading(false);
        window.alert('Slanje trenutno nije moguce. Molimo pozovite nas telefonom.');
      });
  });
})();
