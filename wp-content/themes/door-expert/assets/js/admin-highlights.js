/*
 * Proizvodi → Istaknuti atributi (admin ekran).
 *
 * Tri sitnice koje forma ne moze sama:
 *   1. pregled izabrane ikone pored padajuceg menija (da se ne pogadja po imenu),
 *   2. pomjeranje reda gore/dolje (redoslijed redova = redoslijed na sajtu),
 *   3. pokrivenost: koliko proizvoda u kategoriji uopste ima tu vrijednost.
 *
 * Bez jQuery-ja i bez vendor biblioteka. Redovi se ne premjestaju u DOM-u nego im
 * se zamijene vrijednosti: imena polja su indeksirana (rows[0], rows[1]...) pa bi
 * premjestanje <tr> elemenata razbilo indekse.
 *
 * Ako skripta ne ucita, forma i dalje radi - sve troje je dopuna, ne uslov.
 */
(function () {
  'use strict';

  var data = window.doorExpertHighlights || {};
  var icons = data.icons || {};
  var i18n = data.i18n || {};

  var SVG_NS = 'http://www.w3.org/2000/svg';

  /**
   * Ispuni pregled ikone za jedan red.
   *
   * @param {HTMLElement} row Red tabele.
   */
  function renderPreview(row) {
    var select = row.querySelector('.de-hl-icon');
    var target = row.querySelector('.de-hl-preview');

    if (!select || !target) {
      return;
    }

    target.textContent = '';

    var markup = icons[select.value];
    if (!markup) {
      return;
    }

    // innerHTML na SVG cvoru ne radi svuda isto; pravimo cijeli <svg> pa ga ubacimo.
    var wrapper = document.createElementNS(SVG_NS, 'svg');
    wrapper.setAttribute('width', '22');
    wrapper.setAttribute('height', '22');
    wrapper.setAttribute('viewBox', '0 0 24 24');
    wrapper.setAttribute('fill', 'none');
    wrapper.setAttribute('stroke', 'currentColor');
    wrapper.setAttribute('stroke-width', '2');
    wrapper.setAttribute('stroke-linecap', 'round');
    wrapper.setAttribute('stroke-linejoin', 'round');
    wrapper.innerHTML = markup;

    target.appendChild(wrapper);
  }

  /**
   * Trazi i ispisi pokrivenost za jedan red.
   *
   * @param {HTMLElement} row Red tabele.
   */
  function loadCoverage(row) {
    var select = row.querySelector('.de-hl-attr');
    var target = row.querySelector('.de-hl-coverage');

    if (!select || !target || !data.ajaxUrl) {
      return;
    }

    target.className = 'de-hl-coverage';

    if (!select.value) {
      target.textContent = '';
      return;
    }

    target.textContent = i18n.checking || '';

    var body = new FormData();
    body.append('action', 'door_expert_highlights_coverage');
    body.append('nonce', data.nonce);
    body.append('context', select.getAttribute('data-context') || 'default');
    body.append('attr', select.value);

    window
      .fetch(data.ajaxUrl, {
        method: 'POST',
        credentials: 'same-origin',
        body: body
      })
      .then(function (response) {
        return response.json();
      })
      .then(function (payload) {
        if (!payload || !payload.success || !payload.data) {
          target.textContent = '';
          return;
        }

        target.textContent = payload.data.text || '';

        if (payload.data.empty) {
          target.className = 'de-hl-coverage is-empty';
        } else if (payload.data.warn) {
          target.className = 'de-hl-coverage is-warn';
        }
      })
      .catch(function () {
        target.textContent = i18n.error || '';
      });
  }

  /**
   * Zamijeni sadrzaj dva reda (izvor, ikona, labela, rezervni tekst).
   *
   * @param {HTMLElement} a Prvi red.
   * @param {HTMLElement} b Drugi red.
   */
  function swapRows(a, b) {
    ['.de-hl-attr', '.de-hl-icon'].forEach(function (selector) {
      var fieldA = a.querySelector(selector);
      var fieldB = b.querySelector(selector);

      if (fieldA && fieldB) {
        var value = fieldA.value;
        fieldA.value = fieldB.value;
        fieldB.value = value;
      }
    });

    var inputsA = a.querySelectorAll('.de-hl-input');
    var inputsB = b.querySelectorAll('.de-hl-input');

    for (var i = 0; i < inputsA.length && i < inputsB.length; i += 1) {
      var text = inputsA[i].value;
      inputsA[i].value = inputsB[i].value;
      inputsB[i].value = text;
    }

    [a, b].forEach(function (row) {
      renderPreview(row);
      loadCoverage(row);
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    var rows = Array.prototype.slice.call(document.querySelectorAll('.de-hl-row'));

    if (!rows.length) {
      return;
    }

    rows.forEach(function (row) {
      renderPreview(row);
      loadCoverage(row);

      var icon = row.querySelector('.de-hl-icon');
      if (icon) {
        icon.addEventListener('change', function () {
          renderPreview(row);
        });
      }

      var attr = row.querySelector('.de-hl-attr');
      if (attr) {
        attr.addEventListener('change', function () {
          loadCoverage(row);
        });
      }

      row.querySelectorAll('.de-hl-move').forEach(function (button) {
        button.addEventListener('click', function () {
          var index = rows.indexOf(row);
          var target = 'up' === button.getAttribute('data-dir') ? index - 1 : index + 1;

          if (target < 0 || target >= rows.length) {
            return;
          }

          swapRows(row, rows[target]);
        });
      });
    });

    // Namjerno prazna kategorija: tabela ostaje vidljiva, ali je jasno da ne vazi.
    var none = document.querySelector('.de-hl-none__input');
    var table = document.querySelector('.de-hl-table');

    if (none && table) {
      var sync = function () {
        table.classList.toggle('is-disabled', none.checked);
      };

      none.addEventListener('change', sync);
      sync();
    }
  });
})();
