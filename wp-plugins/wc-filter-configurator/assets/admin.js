/* jshint esversion: 6 */
/**
 * WC Filter Configurator — admin UI.
 *
 * Drag & drop editor for the archive filter configuration. Depends on
 * SortableJS (self-hosted in assets/vendor/) and on the wcfcData object
 * localized by includes/admin.php.
 *
 * No jQuery, no build step.
 */
(function () {
    'use strict';

    var data      = window.wcfcData || {};
    var ajaxUrl   = data.ajaxUrl   || '';
    var nonce     = data.nonce     || '';
    var groupsUrl = data.groupsUrl || '';
    var childCats = data.childCats || {};
    var allAttrs  = data.allAttrs  || {};
    var locked    = data.lockedTypes || [];   // attrs whose type must not be edited
    var i18n      = data.i18n      || {};

    var sortables = {};

    /** Translate, with an English fallback baked in. */
    function t(key, fallback) {
        return Object.prototype.hasOwnProperty.call(i18n, key) ? i18n[key] : fallback;
    }

    /** sprintf-lite: replaces %s / %d placeholders left to right. */
    function fmt(str) {
        var args = Array.prototype.slice.call(arguments, 1);
        var i = 0;
        return String(str).replace(/%[sd]/g, function () { return args[i++]; });
    }

    /** Special attributes render as a fixed control; their type is not user-editable. */
    function isLocked(attr) {
        return locked.indexOf(attr) !== -1;
    }

    // ── Badge update ──────────────────────────────────────────────
    function updateGroupCount(listEl) {
        if (!listEl) return;
        var groupEl = listEl.closest ? listEl.closest('.wcfc-pool-group') : null;
        if (!groupEl) return;
        var allChips   = listEl.querySelectorAll('.wcfc-chip');
        var availCount = listEl.querySelectorAll('.wcfc-chip:not(.is-disabled)').length;
        var badge = groupEl.querySelector('.wcfc-pool-group__count');
        if (badge) badge.textContent = availCount;
        groupEl.classList.toggle('is-empty', allChips.length > 0 && availCount === 0);
    }

    // ── Category modal ────────────────────────────────────────────
    var catModal = null;
    var catModalItem = null;

    function ensureCatModal() {
        if (catModal) return catModal;
        var overlay = document.createElement('div');
        overlay.className = 'wcfc-modal-overlay';
        overlay.hidden = true;
        overlay.innerHTML =
            '<div class="wcfc-modal">' +
                '<div class="wcfc-modal__hd">' +
                    '<span class="wcfc-modal__title">' + escHtml(t('categories', 'Categories')) + '</span>' +
                    '<button type="button" class="wcfc-modal__close">&#x2715;</button>' +
                '</div>' +
                '<div class="wcfc-modal__body"></div>' +
                '<div class="wcfc-modal__foot">' +
                    '<button type="button" class="button wcfc-modal__clear">' + escHtml(t('clearAll', 'Clear all')) + '</button>' +
                    '<button type="button" class="button button-primary wcfc-modal__apply">' + escHtml(t('apply', 'Apply')) + '</button>' +
                '</div>' +
            '</div>';
        document.body.appendChild(overlay);
        overlay.addEventListener('click', function (e) { if (e.target === overlay) closeCatModal(); });
        overlay.querySelector('.wcfc-modal__close').addEventListener('click', closeCatModal);
        overlay.querySelector('.wcfc-modal__apply').addEventListener('click', applyCatModal);
        overlay.querySelector('.wcfc-modal__clear').addEventListener('click', function () {
            overlay.querySelectorAll('.wcfc-modal__body input').forEach(function (cb) { cb.checked = false; });
        });
        catModal = overlay;
        return overlay;
    }

    function openCatModal(item, catKey) {
        var cats = childCats[catKey] || [];
        if (cats.length === 0) return;
        var overlay = ensureCatModal();
        catModalItem = item;
        var selected = [];
        try { selected = JSON.parse(item.dataset.categories || '[]'); } catch (e) {}
        var body = overlay.querySelector('.wcfc-modal__body');
        body.innerHTML = '';
        cats.forEach(function (cat) {
            var lbl = document.createElement('label');
            lbl.className = 'wcfc-cat-option' + (cat.depth === 0 ? ' is-parent' : '');
            if (cat.depth > 0) lbl.style.paddingLeft = (cat.depth * 14) + 'px';
            var cb = document.createElement('input');
            cb.type = 'checkbox';
            cb.value = cat.slug;
            cb.checked = selected.indexOf(cat.slug) >= 0;
            lbl.appendChild(cb);
            lbl.appendChild(document.createTextNode(' ' + cat.name));
            body.appendChild(lbl);
        });
        overlay.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeCatModal() {
        if (!catModal) return;
        catModal.hidden = true;
        document.body.style.overflow = '';
        catModalItem = null;
    }

    function applyCatModal() {
        if (!catModal || !catModalItem) return;
        var selected = [];
        catModal.querySelectorAll('.wcfc-modal__body input:checked').forEach(function (cb) { selected.push(cb.value); });
        catModalItem.dataset.categories = JSON.stringify(selected);
        var btn = catModalItem.querySelector('.wcfc-item__cats-btn');
        if (btn) {
            btn.textContent = selected.length === 0
                ? t('allCats', 'All cats')
                : fmt(t('nCats', '%d cats'), selected.length);
            btn.classList.toggle('has-selection', selected.length > 0);
        }
        closeCatModal();
    }

    function buildCatDropdown(item, catKey, selectedCats) {
        var cats = childCats[catKey] || [];
        if (cats.length === 0) return;
        if (item.querySelector('.wcfc-item__cats')) return;
        var catsDiv = document.createElement('div');
        catsDiv.className = 'wcfc-item__cats';
        var btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'wcfc-item__cats-btn';
        var count = selectedCats.length;
        btn.textContent = count === 0 ? t('allCats', 'All cats') : fmt(t('nCats', '%d cats'), count);
        btn.classList.toggle('has-selection', count > 0);
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            openCatModal(item, catKey);
        });
        catsDiv.appendChild(btn);
        var removeBtn = item.querySelector('.wcfc-item__remove');
        if (removeBtn) item.insertBefore(catsDiv, removeBtn);
        else item.appendChild(catsDiv);
    }

    function getCatSelection(item) {
        try { return JSON.parse(item.dataset.categories || '[]'); } catch (e) { return []; }
    }

    // ── Pool chip enable/disable ───────────────────────────────────
    function updatePoolChipState(poolEl, attr, isDisabled) {
        if (!poolEl) return;
        var chip = poolEl.querySelector('.wcfc-chip[data-attr="' + attr + '"]');
        if (!chip) return;
        chip.classList.toggle('is-disabled', isDisabled);
        updateGroupCount(chip.parentNode);
    }

    // ── Build active item ──────────────────────────────────────────
    function buildItem(attr, label, type, collapsed) {
        var li = document.createElement('li');
        li.className = 'wcfc-item';
        li.dataset.attr = attr;
        li.dataset.defaultLabel = label;
        li.dataset.categories = '[]';
        li.dataset.type = type;

        // Special attributes (price slider, category tree) have exactly one
        // rendering, so we show a locked badge instead of a <select>. The old
        // build shipped a select without their option, which silently rewrote
        // the stored type on every save.
        var typeControl = isLocked(attr)
            ? '<span class="wcfc-item__type wcfc-item__type--locked" title="' +
                  escAttr(t('typeLocked', 'This filter has a fixed control type')) + '">' +
                  escHtml(type) + '</span>'
            : '<select class="wcfc-item__type">' +
                  '<option value="swatch"'   + (type === 'swatch'   ? ' selected' : '') + '>' + escHtml(t('typeSwatch', 'Swatch')) + '</option>' +
                  '<option value="checkbox"' + (type === 'checkbox' ? ' selected' : '') + '>' + escHtml(t('typeCheckbox', 'Checkbox')) + '</option>' +
              '</select>';

        li.innerHTML =
            '<span class="wcfc-item__handle" title="' + escAttr(t('dragReorder', 'Drag to reorder')) + '">&#x2807;</span>' +
            '<span class="wcfc-item__attr">' + escHtml(attr) + '</span>' +
            '<input type="text" class="wcfc-item__label" value="' + escAttr(label) + '" placeholder="' + escAttr(t('label', 'Label')) + '">' +
            typeControl +
            '<label class="wcfc-item__collapsed" title="' + escAttr(t('collapsedHint', 'Collapsed on page load')) + '">' +
                '<input type="checkbox" class="wcfc-item__collapsed-cb"' + (collapsed ? ' checked' : '') + '>' +
                '<span>' + escHtml(t('collapsed', 'Collapsed')) + '</span>' +
            '</label>' +
            '<button type="button" class="wcfc-item__remove" title="' + escAttr(t('remove', 'Remove')) + '">&#x2715;</button>';
        return li;
    }

    function bindItemEvents(item) {
        var removeBtn = item.querySelector('.wcfc-item__remove');
        if (!removeBtn) return;
        removeBtn.addEventListener('click', function () {
            var attr     = item.dataset.attr;
            var activeEl = item.parentNode;
            var panel    = activeEl ? activeEl.closest('.wcfc-active-panel') : null;
            var catKey   = panel ? (panel.dataset.cat || '') : '';
            activeEl.removeChild(item);
            if (catKey) {
                var poolEl = document.getElementById('pool-' + catKey);
                updatePoolChipState(poolEl, attr, false);
            }
        });
    }

    // ── Serialize single filter item ───────────────────────────────
    function serializeItem(item) {
        var attr     = item.dataset.attr || '';
        var labelInp = item.querySelector('.wcfc-item__label');
        var typeInp  = item.querySelector('select.wcfc-item__type');
        var collInp  = item.querySelector('.wcfc-item__collapsed-cb');
        return {
            attr:       attr,
            label:      labelInp ? labelInp.value.trim() || attr : attr,
            // Locked items have no <select>; keep whatever type they were stored with.
            type:       typeInp ? typeInp.value : (item.dataset.type || 'checkbox'),
            collapsed:  collInp ? collInp.checked : false,
            categories: getCatSelection(item),
        };
    }

    // ── Serialize active list (flat items + sections) ──────────────
    function serializeActive(catKey) {
        var activeEl = document.getElementById('active-' + catKey);
        if (!activeEl) {
            console.warn('[wcfc] serializeActive: #active-' + catKey + ' not found');
            return [];
        }
        var result = [];
        Array.from(activeEl.children).forEach(function (child) {
            if (child.classList.contains('wcfc-section')) {
                var labelInp = child.querySelector('.wcfc-section__label');
                var collCb   = child.querySelector('.wcfc-section__collapsed-cb');
                var sFilters = [];
                child.querySelectorAll('.wcfc-section__list > .wcfc-item').forEach(function (item) {
                    sFilters.push(serializeItem(item));
                });
                result.push({
                    type:      'section',
                    label:     labelInp ? (labelInp.value.trim() || t('section', 'Section')) : t('section', 'Section'),
                    collapsed: collCb ? collCb.checked : false,
                    filters:   sFilters,
                });
            } else if (child.classList.contains('wcfc-item')) {
                result.push(serializeItem(child));
            }
        });
        return result;
    }

    // ── Toast ──────────────────────────────────────────────────────
    function showToast(msg, isError, href) {
        var toast = document.getElementById('wcfcToast');
        if (!toast) return;
        toast.textContent = msg;
        toast.className = 'wcfc-toast is-visible' + (isError ? ' is-error' : ' is-ok');
        toast._navHref = href || null;
        toast.style.cursor = href ? 'pointer' : '';
        clearTimeout(toast._timer);
        toast._timer = setTimeout(function () { toast.className = 'wcfc-toast'; }, href ? 6000 : 3000);
    }

    function ajaxError(res) {
        return fmt(t('errorPrefix', 'Error: %s'), (res && res.data) ? res.data : t('unknownError', 'unknown error'));
    }

    // ── Save config ────────────────────────────────────────────────
    function saveConfig(catKey, btn) {
        var filters  = serializeActive(catKey);
        var origText = btn ? btn.textContent : '';
        if (btn) { btn.disabled = true; btn.textContent = t('saving', 'Saving…'); }

        var fd = new FormData();
        fd.append('action', 'wcfc_save_config');
        fd.append('nonce', nonce);
        fd.append('cat', catKey);
        fd.append('filters_json', JSON.stringify(filters));

        fetch(ajaxUrl, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (btn) { btn.disabled = false; btn.textContent = origText; }
                if (res && res.success) {
                    showToast(fmt(t('savedConfig', 'Configuration saved (%d filters)'), res.data.count || 0), false);
                } else {
                    showToast(ajaxError(res), true);
                }
            })
            .catch(function () {
                if (btn) { btn.disabled = false; btn.textContent = origText; }
                showToast(t('saveFailed', 'Save failed'), true);
            });
    }

    // ── Pool group order save ──────────────────────────────────────
    function savePoolGroupOrder(poolEl) {
        var groups = [];
        poolEl.querySelectorAll(':scope > .wcfc-pool-group').forEach(function (li) {
            var id   = li.dataset.groupId   || '';
            var name = li.dataset.groupName || '';
            if (!name || !id) return;   // skips the synthetic "Ungrouped" bucket
            var attrs = [];
            li.querySelectorAll('.wcfc-chip').forEach(function (chip) {
                if (chip.dataset.attr) attrs.push(chip.dataset.attr);
            });
            groups.push({ id: id, name: name, attrs: attrs });
        });
        var fd = new FormData();
        fd.append('action', 'wcfc_save_groups');
        fd.append('nonce', nonce);
        groups.forEach(function (g, i) {
            fd.append('groups[' + i + '][id]',   g.id);
            fd.append('groups[' + i + '][name]', g.name);
            g.attrs.forEach(function (a, j) {
                fd.append('groups[' + i + '][attrs][' + j + ']', a);
            });
        });
        fetch(ajaxUrl, { method: 'POST', body: fd })
            .then(function (r) { return r.json(); })
            .then(function (res) {
                if (res && res.success) showToast(t('groupOrderSaved', 'Group order saved'), false);
            });
    }

    // ── Pool accordion ─────────────────────────────────────────────
    function initPoolAccordion(poolEl) {
        if (!poolEl) return;
        poolEl.querySelectorAll('.wcfc-pool-group__hd').forEach(function (hd) {
            var group = hd.closest('.wcfc-pool-group');
            if (!group) return;
            var isFirst = group === poolEl.querySelector('.wcfc-pool-group');
            if (isFirst) group.classList.add('is-open');

            hd.addEventListener('click', function (e) {
                if (e.target.closest('.wcfc-pool-group__drag-handle')) return;
                group.classList.toggle('is-open');
                var arrow = hd.querySelector('.wcfc-pool-group__arrow');
                if (arrow) arrow.textContent = group.classList.contains('is-open') ? '▼' : '▶';
            });
            if (group.classList.contains('is-open')) {
                var arrow = hd.querySelector('.wcfc-pool-group__arrow');
                if (arrow) arrow.textContent = '▼';
            }
        });
    }

    // ── Main panel init ────────────────────────────────────────────
    function initPanel(catKey) {
        var poolEl   = document.getElementById('pool-'   + catKey);
        var activeEl = document.getElementById('active-' + catKey);
        if (!activeEl) return;

        var isFlat      = poolEl && poolEl.classList.contains('wcfc-pool--flat');
        var poolGrpName = 'pool-attrs-' + catKey;

        // ── Pool chip drag (clone) ────────────────────────────────
        if (poolEl) {
            var chipLists = isFlat
                ? [poolEl]
                : Array.from(poolEl.querySelectorAll('.wcfc-pool-group__list'));

            chipLists.forEach(function (listEl) {
                if (listEl._chipSortable) return;
                listEl._chipSortable = Sortable.create(listEl, {
                    group: { name: poolGrpName, pull: 'clone', put: false },
                    sort: false,
                    animation: 150,
                    filter: '.is-disabled',
                });
            });

            // Pool group reorder (drag handle)
            if (!isFlat) {
                if (sortables['pool-' + catKey]) try { sortables['pool-' + catKey].destroy(); } catch (e) {}
                sortables['pool-' + catKey] = Sortable.create(poolEl, {
                    animation: 150,
                    handle: '.wcfc-pool-group__drag-handle',
                    ghostClass: 'wcfc-ghost',
                    group: { name: 'pool-groups-' + catKey, pull: false, put: false },
                    onEnd: function () { savePoolGroupOrder(poolEl); },
                });
            }
        }

        // ── Active list: reorder (items + sections) + accept from pool ──
        if (sortables['active-' + catKey]) try { sortables['active-' + catKey].destroy(); } catch (e) {}
        sortables['active-' + catKey] = Sortable.create(activeEl, {
            group:       { name: 'active-' + catKey, pull: true, put: [poolGrpName, 'active-' + catKey] },
            animation:   150,
            handle:      '.wcfc-item__handle, .wcfc-section__drag',
            ghostClass:  'wcfc-ghost',
            chosenClass: 'wcfc-chosen',
            onAdd: function (evt) {
                var el   = evt.item;
                var attr = el.dataset.attr;

                // Sections do not nest.
                if (el.classList.contains('wcfc-section')) return;

                if (!attr || !el.classList.contains('wcfc-chip')) {
                    // Item moved in from a section — rebind only.
                    bindItemEvents(el);
                    buildCatDropdown(el, catKey, getCatSelection(el));
                    return;
                }

                // Chip dropped — reject duplicates anywhere in this panel.
                var existing = activeEl.querySelectorAll('.wcfc-item');
                for (var i = 0; i < existing.length; i++) {
                    if (existing[i].dataset.attr === attr) {
                        activeEl.removeChild(el);
                        return;
                    }
                }

                var label   = el.dataset.defaultLabel || allAttrs[attr] || attr;
                var type    = el.dataset.defaultType || 'checkbox';
                var newItem = buildItem(attr, label, type, false);
                activeEl.insertBefore(newItem, el);
                activeEl.removeChild(el);
                bindItemEvents(newItem);
                buildCatDropdown(newItem, catKey, []);
                updatePoolChipState(poolEl, attr, true);
            },
        });

        // Init existing sections
        activeEl.querySelectorAll(':scope > .wcfc-section').forEach(function (sectionEl) {
            var listEl = sectionEl.querySelector('.wcfc-section__list');
            if (listEl) initSectionList(listEl, catKey, poolEl);
            bindSectionEvents(sectionEl, catKey, poolEl);
            sectionEl.querySelectorAll('.wcfc-item').forEach(function (item) {
                bindItemEvents(item);
                buildCatDropdown(item, catKey, getCatSelection(item));
            });
        });

        // Bind existing flat active items
        activeEl.querySelectorAll(':scope > .wcfc-item').forEach(function (item) {
            bindItemEvents(item);
            buildCatDropdown(item, catKey, getCatSelection(item));
        });

        // Pool accordion
        if (poolEl) initPoolAccordion(poolEl);
    }

    // ── Build section container ────────────────────────────────────
    function buildSection(label, collapsed) {
        var id = 'sec-' + Date.now();
        var li = document.createElement('li');
        li.className = 'wcfc-section is-open';
        li.dataset.type = 'section';
        li.innerHTML =
            '<div class="wcfc-section__hd">' +
                '<span class="wcfc-section__drag" title="' + escAttr(t('dragSection', 'Drag section')) + '">&#x2807;</span>' +
                '<input type="text" class="wcfc-section__label" value="' + escAttr(label || t('newSection', 'New section')) + '" placeholder="' + escAttr(t('sectionName', 'Section name')) + '">' +
                '<label class="wcfc-section__collapsed-wrap" title="' + escAttr(t('collapsedFrontHint', 'Collapsed on the front end')) + '">' +
                    '<input type="checkbox" class="wcfc-section__collapsed-cb"' + (collapsed ? ' checked' : '') + '>' +
                    '<span>' + escHtml(t('collapsed', 'Collapsed')) + '</span>' +
                '</label>' +
                '<button type="button" class="wcfc-section__toggle" title="' + escAttr(t('expandCollapse', 'Expand / collapse')) + '">&#x25B2;</button>' +
                '<button type="button" class="wcfc-section__remove" title="' + escAttr(t('deleteSection', 'Delete section')) + '">&#x2715;</button>' +
            '</div>' +
            '<ul class="wcfc-section__list" id="' + id + '"></ul>';
        return li;
    }

    // ── SortableJS for a section's inner list ──────────────────────
    function initSectionList(listEl, catKey, poolEl) {
        if (!listEl || listEl._secSortable) return;
        var poolGrpName = 'pool-attrs-' + catKey;
        listEl._secSortable = Sortable.create(listEl, {
            group:       { name: 'active-' + catKey, pull: true, put: [poolGrpName, 'active-' + catKey] },
            animation:   150,
            handle:      '.wcfc-item__handle',
            ghostClass:  'wcfc-ghost',
            chosenClass: 'wcfc-chosen',
            onAdd: function (evt) {
                var el   = evt.item;
                var attr = el.dataset.attr;
                if (!attr) return;
                if (!el.classList.contains('wcfc-chip')) {
                    // Item moved in from the main list or another section — rebind only.
                    bindItemEvents(el);
                    return;
                }
                // Chip dropped — reject duplicates across the whole active area.
                var panel = listEl.closest('.wcfc-active-panel');
                var allItems = panel ? panel.querySelectorAll('.wcfc-item') : [];
                for (var i = 0; i < allItems.length; i++) {
                    if (allItems[i].dataset.attr === attr) { listEl.removeChild(el); return; }
                }
                var label   = el.dataset.defaultLabel || allAttrs[attr] || attr;
                var type    = el.dataset.defaultType || 'checkbox';
                var newItem = buildItem(attr, label, type, false);
                listEl.insertBefore(newItem, el);
                listEl.removeChild(el);
                bindItemEvents(newItem);
                buildCatDropdown(newItem, catKey, []);
                updatePoolChipState(poolEl, attr, true);
            },
        });
    }

    // ── Section events (toggle, remove) ───────────────────────────
    function bindSectionEvents(sectionEl, catKey, poolEl) {
        var toggleBtn = sectionEl.querySelector('.wcfc-section__toggle');
        if (toggleBtn && !toggleBtn._bound) {
            toggleBtn._bound = true;
            toggleBtn.addEventListener('click', function () {
                sectionEl.classList.toggle('is-open');
                toggleBtn.textContent = sectionEl.classList.contains('is-open') ? '▲' : '▼';
            });
        }
        var removeBtn = sectionEl.querySelector('.wcfc-section__remove');
        if (removeBtn && !removeBtn._bound) {
            removeBtn._bound = true;
            removeBtn.addEventListener('click', function () {
                var activeEl = document.getElementById('active-' + catKey);
                if (activeEl) {
                    // Move the section's filters back into the main list rather than dropping them.
                    sectionEl.querySelectorAll('.wcfc-item').forEach(function (item) {
                        activeEl.insertBefore(item, sectionEl);
                        bindItemEvents(item);
                    });
                }
                sectionEl.parentNode.removeChild(sectionEl);
            });
        }
    }

    // ── Escape helpers ─────────────────────────────────────────────
    function escHtml(str) {
        return String(str)
            .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
    function escAttr(str) { return escHtml(str); }

    // ── In-page tab switching ──────────────────────────────────────
    function initTabSwitching() {
        document.querySelectorAll('.wcfc-tab[data-cat]').forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                var catKey = tab.dataset.cat;

                document.querySelectorAll('.wcfc-tab').forEach(function (x) { x.classList.remove('is-active'); });
                tab.classList.add('is-active');

                document.querySelectorAll('.wcfc-pool-panel').forEach(function (p) { p.classList.remove('is-active'); });
                var pp = document.querySelector('.wcfc-pool-panel[data-cat="' + catKey + '"]');
                if (pp) pp.classList.add('is-active');

                document.querySelectorAll('.wcfc-active-panel').forEach(function (p) { p.classList.remove('is-active'); });
                var ap = document.querySelector('.wcfc-active-panel[data-cat="' + catKey + '"]');
                if (ap) ap.classList.add('is-active');

                if (!sortables['active-' + catKey]) initPanel(catKey);

                try {
                    var url = new URL(window.location.href);
                    url.searchParams.set('wcfc_tab', catKey);
                    history.pushState({}, '', url.toString());
                } catch (ex) {}
            });
        });
    }

    // ── Groups editor ──────────────────────────────────────────────
    function initGroupsEditor() {
        var groupList    = document.getElementById('wcfcGroupList');
        var ungrouped    = document.getElementById('wcfcUngroupedPool');
        var addGroupBtn  = document.getElementById('wcfcAddGroup');
        var saveGroupBtn = document.getElementById('wcfcSaveGroups');

        if (!groupList) return;

        Sortable.create(groupList, {
            animation: 150,
            handle: '.wcfc-group__drag',
            ghostClass: 'wcfc-ghost',
        });

        if (ungrouped) {
            Sortable.create(ungrouped, {
                group: { name: 'wcfc-groups', pull: true, put: true },
                animation: 150,
                ghostClass: 'wcfc-ghost',
            });
        }

        function initGroupItemsSortable(itemsEl) {
            Sortable.create(itemsEl, {
                group: { name: 'wcfc-groups', pull: true, put: true },
                animation: 150,
                ghostClass: 'wcfc-ghost',
            });
        }

        groupList.querySelectorAll('.wcfc-group__items').forEach(initGroupItemsSortable);

        function bindGroupDelete(groupEl) {
            var del = groupEl.querySelector('.wcfc-group__delete');
            if (!del) return;
            del.addEventListener('click', function () {
                groupEl.querySelectorAll('.wcfc-chip').forEach(function (chip) {
                    if (ungrouped) ungrouped.appendChild(chip);
                });
                groupEl.parentNode.removeChild(groupEl);
            });
        }
        groupList.querySelectorAll('.wcfc-group').forEach(bindGroupDelete);

        function nextGroupName() {
            var word = t('group', 'Group');
            var max = 0;
            var re  = new RegExp('^' + word.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s+(\\d+)$', 'i');
            groupList.querySelectorAll('.wcfc-group__name').forEach(function (inp) {
                var m = inp.value.match(re);
                if (m) max = Math.max(max, parseInt(m[1], 10));
            });
            return word + ' ' + (max + 1);
        }

        if (addGroupBtn) {
            addGroupBtn.addEventListener('click', function () {
                var id  = 'g' + Date.now();
                var div = document.createElement('div');
                div.className = 'wcfc-group';
                div.dataset.id = id;
                div.innerHTML =
                    '<div class="wcfc-group__hd">' +
                        '<span class="wcfc-group__drag" title="' + escAttr(t('dragGroup', 'Drag group')) + '">&#x2807;</span>' +
                        '<input type="text" class="wcfc-group__name" value="' + escAttr(nextGroupName()) + '" placeholder="' + escAttr(t('groupName', 'Group name')) + '">' +
                        '<button type="button" class="wcfc-group__delete" title="' + escAttr(t('deleteGroup', 'Delete group')) + '">&#x2715;</button>' +
                    '</div>' +
                    '<ul class="wcfc-group__items" id="grp-' + id + '"></ul>';
                groupList.appendChild(div);
                initGroupItemsSortable(div.querySelector('.wcfc-group__items'));
                bindGroupDelete(div);
                var nameInp = div.querySelector('.wcfc-group__name');
                nameInp.focus();
                nameInp.select();
            });
        }

        var resetBtn = document.getElementById('wcfcResetGroups');
        if (resetBtn) {
            resetBtn.addEventListener('click', function () {
                if (!confirm(t('confirmResetGroups', 'Delete all groups? The attributes go back to Ungrouped.'))) return;
                groupList.querySelectorAll('.wcfc-group').forEach(function (gEl) {
                    gEl.querySelectorAll('.wcfc-chip').forEach(function (chip) {
                        if (ungrouped) ungrouped.appendChild(chip);
                    });
                    gEl.parentNode.removeChild(gEl);
                });
            });
        }

        if (saveGroupBtn) {
            saveGroupBtn.addEventListener('click', function () {
                var groups = [];
                groupList.querySelectorAll('.wcfc-group').forEach(function (gEl) {
                    var id   = gEl.dataset.id || ('g' + Date.now());
                    var name = (gEl.querySelector('.wcfc-group__name') || {}).value || '';
                    if (!name.trim()) return;
                    var attrs = [];
                    gEl.querySelectorAll('.wcfc-chip').forEach(function (chip) {
                        if (chip.dataset.attr) attrs.push(chip.dataset.attr);
                    });
                    groups.push({ id: id, name: name.trim(), attrs: attrs });
                });

                var origText = saveGroupBtn.textContent;
                saveGroupBtn.disabled = true;
                saveGroupBtn.textContent = t('saving', 'Saving…');

                var fd = new FormData();
                fd.append('action', 'wcfc_save_groups');
                fd.append('nonce', nonce);
                groups.forEach(function (g, i) {
                    fd.append('groups[' + i + '][id]',   g.id);
                    fd.append('groups[' + i + '][name]', g.name);
                    g.attrs.forEach(function (a, j) {
                        fd.append('groups[' + i + '][attrs][' + j + ']', a);
                    });
                });

                fetch(ajaxUrl, { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        saveGroupBtn.disabled = false;
                        saveGroupBtn.textContent = origText;
                        if (res && res.success) {
                            showToast(fmt(t('savedGroups', 'Groups saved (%d)'), res.data.count || 0), false);
                        } else {
                            showToast(ajaxError(res), true);
                        }
                    })
                    .catch(function () {
                        saveGroupBtn.disabled = false;
                        saveGroupBtn.textContent = origText;
                        showToast(t('saveFailed', 'Save failed'), true);
                    });
            });
        }
    }

    // ── DOMContentLoaded ───────────────────────────────────────────
    document.addEventListener('DOMContentLoaded', function () {

        var activePanel = document.querySelector('.wcfc-active-panel.is-active');
        if (activePanel) initPanel(activePanel.dataset.cat);

        initTabSwitching();
        initGroupsEditor();

        var toastEl = document.getElementById('wcfcToast');
        if (toastEl) {
            toastEl.addEventListener('click', function () {
                if (toastEl._navHref) window.location.href = toastEl._navHref;
            });
        }

        document.querySelectorAll('.wcfc-save').forEach(function (btn) {
            btn.addEventListener('click', function () { saveConfig(btn.dataset.cat, btn); });
        });

        // ── Flush the category/product-id cache ───────────────────
        var flushBtn = document.getElementById('wcfcFlushCache');
        if (flushBtn) {
            flushBtn.addEventListener('click', function () {
                flushBtn.disabled = true;
                var original = flushBtn.innerHTML;
                flushBtn.textContent = t('flushing', 'Flushing…');
                var fd = new FormData();
                fd.append('action', 'wcfc_flush_cache');
                fd.append('nonce', nonce);
                fetch(ajaxUrl, { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        flushBtn.disabled = false;
                        flushBtn.innerHTML = original;
                        if (res && res.success) {
                            showToast(t('cacheFlushed', 'Filter cache flushed'), false);
                        } else {
                            showToast(ajaxError(res), true);
                        }
                    })
                    .catch(function () {
                        flushBtn.disabled = false;
                        flushBtn.innerHTML = original;
                        showToast(t('cacheFlushFailed', 'Cache flush failed'), true);
                    });
            });
        }

        // ── Add section ───────────────────────────────────────────
        document.querySelectorAll('.wcfc-add-section').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var catKey   = btn.dataset.cat;
                var activeEl = document.getElementById('active-' + catKey);
                var poolEl   = document.getElementById('pool-' + catKey);
                if (!activeEl) return;

                if (!sortables['active-' + catKey]) initPanel(catKey);

                var sectionEl = buildSection(t('newSection', 'New section'), false);
                activeEl.appendChild(sectionEl);

                var listEl = sectionEl.querySelector('.wcfc-section__list');
                if (listEl) initSectionList(listEl, catKey, poolEl);
                bindSectionEvents(sectionEl, catKey, poolEl);

                var labelInp = sectionEl.querySelector('.wcfc-section__label');
                if (labelInp) { labelInp.focus(); labelInp.select(); }
            });
        });

        // ── Save the active filters as an attribute group ─────────
        document.querySelectorAll('.wcfc-save-as-group').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var catKey   = btn.dataset.cat;
                var catName  = btn.dataset.catname || catKey;
                var activeEl = document.getElementById('active-' + catKey);
                if (!activeEl) return;

                var attrs = [];
                activeEl.querySelectorAll('.wcfc-item').forEach(function (item) {
                    if (item.dataset.attr) attrs.push(item.dataset.attr);
                });

                if (attrs.length === 0) { showToast(t('noActiveFilters', 'No active filters to save'), true); return; }

                var name = window.prompt(t('groupNamePrompt', 'Group name:'), catName);
                if (name === null) return;
                name = name.trim();
                if (!name) return;

                btn.disabled = true;
                var origText = btn.textContent;
                btn.textContent = t('saving', 'Saving…');

                var fd = new FormData();
                fd.append('action', 'wcfc_add_group');
                fd.append('nonce', nonce);
                fd.append('name', name);
                attrs.forEach(function (a, i) { fd.append('attrs[' + i + ']', a); });

                fetch(ajaxUrl, { method: 'POST', body: fd })
                    .then(function (r) { return r.json(); })
                    .then(function (res) {
                        btn.disabled = false;
                        btn.textContent = origText;
                        if (res && res.success) {
                            showToast(fmt(t('groupSaved', '"%s" saved (%d attributes) — click to open Grouping'), name, attrs.length), false, groupsUrl);
                        } else {
                            showToast(ajaxError(res), true);
                        }
                    })
                    .catch(function () {
                        btn.disabled = false;
                        btn.textContent = origText;
                        showToast(t('saveFailed', 'Save failed'), true);
                    });
            });
        });

        // Ctrl/Cmd + S saves the visible panel.
        document.addEventListener('keydown', function (e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                var saveGroups = document.getElementById('wcfcSaveGroups');
                if (saveGroups && saveGroups.offsetParent !== null) { saveGroups.click(); return; }
                var panel = document.querySelector('.wcfc-active-panel.is-active');
                if (!panel) return;
                var btn = panel.querySelector('.wcfc-save');
                if (btn) saveConfig(panel.dataset.cat, btn);
            }
        });
    });

})();
