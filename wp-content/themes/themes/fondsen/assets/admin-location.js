(function () {
    'use strict';

    var PHOTON = 'https://photon.komoot.io/api/';
    var debounceTimer = null;
    var activeIndex = -1;
    var currentSuggestions = [];

    function formatLabel(p) {
        var parts = [];
        if (p.street) {
            parts.push(p.housenumber ? p.street + ' ' + p.housenumber : p.street);
        } else if (p.name) {
            parts.push(p.name);
        }
        if (p.postcode) parts.push(p.postcode);
        if (p.city)     parts.push(p.city);
        if (!parts.length && p.name) parts.push(p.name);
        return parts.join(', ');
    }

    function buildDropdown() {
        var el = document.createElement('ul');
        el.id = 'fn-loc-dropdown';
        el.setAttribute('role', 'listbox');
        document.body.appendChild(el);
        return el;
    }

    function positionDropdown(input, dropdown) {
        var r = input.getBoundingClientRect();
        dropdown.style.top    = (r.bottom + window.scrollY) + 'px';
        dropdown.style.left   = (r.left   + window.scrollX) + 'px';
        dropdown.style.width  = r.width + 'px';
    }

    function hideDropdown(dropdown) {
        dropdown.innerHTML = '';
        dropdown.style.display = 'none';
        activeIndex = -1;
    }

    function selectSuggestion(input, dropdown, feature) {
        var coords = feature.geometry.coordinates; // [lng, lat]
        var label  = formatLabel(feature.properties);

        input.value = label;
        document.getElementById('fn-geo-lat').value = coords[1];
        document.getElementById('fn-geo-lng').value = coords[0];
        hideDropdown(dropdown);
    }

    function renderSuggestions(input, dropdown, features) {
        currentSuggestions = features;
        activeIndex = -1;
        dropdown.innerHTML = '';

        if (!features.length) {
            hideDropdown(dropdown);
            return;
        }

        features.forEach(function (f, i) {
            var li = document.createElement('li');
            li.setAttribute('role', 'option');
            li.setAttribute('data-index', i);
            li.textContent = formatLabel(f.properties);
            li.addEventListener('mousedown', function (e) {
                e.preventDefault();
                selectSuggestion(input, dropdown, f);
            });
            dropdown.appendChild(li);
        });

        positionDropdown(input, dropdown);
        dropdown.style.display = 'block';
    }

    function fetchSuggestions(query, input, dropdown) {
        var url = PHOTON + '?q=' + encodeURIComponent(query) +
                  '&limit=6&lang=nl&lat=52.3&lon=5.3';

        fetch(url)
            .then(function (r) { return r.json(); })
            .then(function (data) {
                renderSuggestions(input, dropdown, data.features || []);
            })
            .catch(function () { hideDropdown(dropdown); });
    }

    function init() {
        var input = document.querySelector('input[name="_job_location"]');
        if (!input) return;

        var latField = document.getElementById('fn-geo-lat');
        var lngField = document.getElementById('fn-geo-lng');
        if (!latField || !lngField) return;

        var dropdown = buildDropdown();

        input.setAttribute('autocomplete', 'off');

        input.addEventListener('input', function () {
            var val = input.value.trim();

            // Reset cached coords when user edits the field
            latField.value = '';
            lngField.value = '';

            clearTimeout(debounceTimer);
            if (val.length < 3) { hideDropdown(dropdown); return; }

            debounceTimer = setTimeout(function () {
                fetchSuggestions(val, input, dropdown);
            }, 280);
        });

        input.addEventListener('keydown', function (e) {
            var items = dropdown.querySelectorAll('li');
            if (!items.length) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                selectSuggestion(input, dropdown, currentSuggestions[activeIndex]);
                return;
            } else if (e.key === 'Escape') {
                hideDropdown(dropdown);
                return;
            }

            items.forEach(function (li, i) {
                li.classList.toggle('is-active', i === activeIndex);
            });
        });

        document.addEventListener('click', function (e) {
            if (e.target !== input) hideDropdown(dropdown);
        });

        window.addEventListener('scroll', function () {
            if (dropdown.style.display === 'block') {
                positionDropdown(input, dropdown);
            }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
