(function () {
  var fieldSelector = '.sj-gf-job-types';
  var nativeClass = 'sj-gf-ms__native';

  function init(root) {
    var scope = root && root.querySelectorAll ? root : document;
    scope.querySelectorAll(fieldSelector).forEach(initField);
  }

  function initField(field) {
    var select = field.querySelector('select[multiple]');

    if (!select || select.dataset.sjGfMultiselect === '1') {
      return;
    }

    select.dataset.sjGfMultiselect = '1';
    select.classList.add(nativeClass);

    var control = document.createElement('div');
    control.className = 'sj-gf-ms';

    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'sj-gf-ms__trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');

    var value = document.createElement('span');
    value.className = 'sj-gf-ms__value';

    var tags = document.createElement('span');
    tags.className = 'sj-gf-ms__tags';

    var arrow = document.createElement('span');
    arrow.className = 'sj-gf-ms__arrow';
    arrow.setAttribute('aria-hidden', 'true');

    trigger.appendChild(value);
    trigger.appendChild(tags);
    trigger.appendChild(arrow);

    var menu = document.createElement('div');
    menu.className = 'sj-gf-ms__menu';

    var search = document.createElement('input');
    search.type = 'search';
    search.className = 'sj-gf-ms__search';
    search.placeholder = 'Zoek dienstverband...';
    search.autocomplete = 'off';

    var list = document.createElement('div');
    list.className = 'sj-gf-ms__list';
    list.setAttribute('role', 'listbox');
    list.setAttribute('aria-multiselectable', 'true');

    menu.appendChild(search);
    menu.appendChild(list);
    control.appendChild(trigger);
    control.appendChild(menu);
    select.insertAdjacentElement('afterend', control);

    var optionButtons = Array.prototype.map.call(select.options, function (option) {
      var button = document.createElement('button');
      button.type = 'button';
      button.className = 'sj-gf-ms__option';
      button.setAttribute('role', 'option');
      button.dataset.value = option.value;
      button.dataset.label = option.textContent.toLowerCase();
      button.innerHTML = '<span class="sj-gf-ms__check" aria-hidden="true"></span><span>' + escapeHtml(option.textContent) + '</span>';

      button.addEventListener('click', function () {
        option.selected = !option.selected;
        dispatchNativeChange(select);
        sync();
      });

      list.appendChild(button);
      return { option: option, button: button };
    });

    trigger.addEventListener('click', function () {
      control.classList.contains('is-open') ? close() : open();
    });

    trigger.addEventListener('keydown', function (event) {
      if (event.key === 'Enter' || event.key === ' ') {
        event.preventDefault();
        open();
      }

      if (event.key === 'Escape') {
        close();
      }
    });

    search.addEventListener('input', function () {
      var query = search.value.trim().toLowerCase();

      optionButtons.forEach(function (item) {
        item.button.hidden = query !== '' && item.button.dataset.label.indexOf(query) === -1;
      });
    });

    select.addEventListener('change', sync);

    document.addEventListener('click', function (event) {
      if (!control.contains(event.target)) {
        close();
      }
    });

    function open() {
      control.classList.add('is-open');
      trigger.setAttribute('aria-expanded', 'true');
      search.focus();
    }

    function close() {
      control.classList.remove('is-open');
      trigger.setAttribute('aria-expanded', 'false');
      search.value = '';

      optionButtons.forEach(function (item) {
        item.button.hidden = false;
      });
    }

    function sync() {
      var selected = optionButtons.filter(function (item) {
        return item.option.selected;
      });

      value.textContent = selected.length ? selected.length + ' geselecteerd' : 'Selecteer dienstverbanden...';
      tags.innerHTML = '';

      selected.slice(0, 3).forEach(function (item) {
        var tag = document.createElement('span');
        tag.className = 'sj-gf-ms__tag';
        tag.textContent = item.option.textContent;
        tags.appendChild(tag);
      });

      if (selected.length > 3) {
        var more = document.createElement('span');
        more.className = 'sj-gf-ms__tag';
        more.textContent = '+' + (selected.length - 3);
        tags.appendChild(more);
      }

      optionButtons.forEach(function (item) {
        item.button.classList.toggle('is-selected', item.option.selected);
        item.button.setAttribute('aria-selected', item.option.selected ? 'true' : 'false');
      });
    }

    sync();
  }

  function dispatchNativeChange(select) {
    select.dispatchEvent(new Event('input', { bubbles: true }));
    select.dispatchEvent(new Event('change', { bubbles: true }));
  }

  function escapeHtml(value) {
    return String(value).replace(/[&<>"']/g, function (char) {
      return {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
      }[char];
    });
  }

  document.addEventListener('DOMContentLoaded', function () {
    init(document);
  });

  if (window.jQuery) {
    window.jQuery(document).on('gform_post_render gform_page_loaded', function () {
      init(document);
    });
  }
})();
