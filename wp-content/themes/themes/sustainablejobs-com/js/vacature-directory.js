(function () {
    function normalize(value) {
        return String(value || '').trim().toLowerCase();
    }

    function selectedValues(select) {
        return Array.from(select.options)
            .filter((option) => option.selected && option.value)
            .map((option) => option.value);
    }

    function closeAll(directory) {
        directory.querySelectorAll('.sj-directory-select.is-open').forEach((select) => {
            select.classList.remove('is-open');
        });
    }

    function buildSelect(directory, select, onChange) {
        const placeholder = select.dataset.placeholder || 'Selecteer';
        const wrap = document.createElement('div');
        wrap.className = 'sj-directory-select-wrap';

        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(select);
        select.classList.add('sj-directory-hidden-select');

        const root = document.createElement('div');
        root.className = 'sj-directory-select';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'sj-directory-select-btn';
        button.innerHTML = '<span class="sj-directory-select-btn__label"></span><span class="sj-directory-select-btn__chev" aria-hidden="true"></span>';

        const label = button.querySelector('.sj-directory-select-btn__label');

        const clearButton = document.createElement('button');
        clearButton.type = 'button';
        clearButton.className = 'sj-directory-select-clear';
        clearButton.setAttribute('aria-label', 'Clear selection');
        clearButton.textContent = 'x';

        const options = document.createElement('div');
        options.className = 'sj-directory-options';
        options.setAttribute('role', 'listbox');
        options.setAttribute('aria-multiselectable', 'true');

        const searchWrap = document.createElement('div');
        searchWrap.className = 'sj-directory-option-search';
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.placeholder = 'Search in ' + placeholder.toLowerCase();
        searchInput.className = 'sj-directory-option-search__input';
        searchWrap.appendChild(searchInput);
        options.appendChild(searchWrap);

        const rows = Array.from(select.options).map((option) => {
            const row = document.createElement('button');
            row.type = 'button';
            row.className = 'sj-directory-option';
            row.setAttribute('role', 'option');
            row.textContent = option.textContent.trim();
            row.dataset.value = option.value;

            row.addEventListener('click', () => {
                option.selected = !option.selected;
                renderState();
                onChange();
            });

            options.appendChild(row);
            return { option, row };
        });

        function renderState() {
            const selected = rows.filter(({ option }) => option.selected);
            label.textContent = selected.length ? placeholder + ' (' + selected.length + ')' : placeholder;
            clearButton.hidden = selected.length === 0;

            rows.forEach(({ option, row }) => {
                row.classList.toggle('is-selected', option.selected);
                row.setAttribute('aria-selected', option.selected ? 'true' : 'false');
            });
        }

        function filterRows() {
            const term = normalize(searchInput.value);
            rows.forEach(({ option, row }) => {
                row.hidden = term !== '' && !normalize(option.textContent).includes(term);
            });
        }

        select.addEventListener('change', renderState);

        button.addEventListener('click', (event) => {
            event.preventDefault();
            const isOpen = root.classList.contains('is-open');
            closeAll(directory);
            root.classList.toggle('is-open', !isOpen);

            if (!isOpen) {
                searchInput.value = '';
                filterRows();
                window.setTimeout(() => searchInput.focus(), 10);
            }
        });

        clearButton.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            rows.forEach(({ option }) => {
                option.selected = false;
            });
            renderState();
            onChange();
        });

        searchInput.addEventListener('input', filterRows);
        searchInput.addEventListener('click', (event) => event.stopPropagation());

        root.appendChild(button);
        root.appendChild(clearButton);
        root.appendChild(options);
        wrap.appendChild(root);

        renderState();
    }

    function renderActiveFilters(directory, searchInput, termSelects, applyFilters) {
        const activeFilters = directory.querySelector('[data-sj-directory-active-filters]');
        if (!activeFilters) return;

        activeFilters.innerHTML = '';

        const searchTerm = searchInput ? searchInput.value.trim() : '';
        if (searchTerm) {
            const chip = document.createElement('button');
            chip.type = 'button';
            chip.className = 'active-filter';
            chip.innerHTML = '<span class="active-filter-text"></span><span class="active-filter-x" aria-hidden="true">x</span>';
            chip.querySelector('.active-filter-text').textContent = 'Search: ' + searchTerm;
            chip.addEventListener('click', () => {
                searchInput.value = '';
                applyFilters();
            });
            activeFilters.appendChild(chip);
        }

        termSelects.forEach((select) => {
            Array.from(select.options).forEach((option) => {
                if (!option.selected) return;

                const chip = document.createElement('button');
                chip.type = 'button';
                chip.className = 'active-filter';
                chip.innerHTML = '<span class="active-filter-text"></span><span class="active-filter-x" aria-hidden="true">x</span>';
                chip.querySelector('.active-filter-text').textContent = option.textContent.trim();
                chip.addEventListener('click', () => {
                    option.selected = false;
                    select.dispatchEvent(new Event('change', { bubbles: true }));
                    applyFilters();
                });
                activeFilters.appendChild(chip);
            });
        });

        activeFilters.style.display = activeFilters.children.length ? 'flex' : 'none';
    }

    function initDirectory(directory) {
        const filterForm = directory.querySelector('[data-sj-directory-filter]');
        const searchInput = directory.querySelector('[data-sj-directory-search]');
        const termSelects = Array.from(directory.querySelectorAll('[data-sj-directory-term-filter]'));
        const items = Array.from(directory.querySelectorAll('[data-sj-directory-item]'));
        const sections = Array.from(directory.querySelectorAll('[data-sj-directory-section]'));
        const empty = directory.querySelector('[data-sj-directory-empty]');

        if (termSelects.length === 0 || items.length === 0) return;

        function applyFilters() {
            const term = normalize(searchInput ? searchInput.value : '');

            const activeFilters = {};
            termSelects.forEach((select) => {
                const taxonomy = select.dataset.sjDirectoryTermFilter;
                const selected = selectedValues(select);
                if (selected.length > 0) {
                    activeFilters[taxonomy] = selected;
                }
            });

            let visibleCount = 0;

            items.forEach((item) => {
                const taxonomy = item.dataset.taxonomy;
                const slug = item.dataset.termSlug;
                const matchesFilter = !activeFilters[taxonomy] || activeFilters[taxonomy].includes(slug);
                const matchesSearch = term === '' || normalize(item.dataset.search).includes(term);
                const isVisible = matchesFilter && matchesSearch;

                item.hidden = !isVisible;
                if (isVisible) visibleCount += 1;
            });

            sections.forEach((section) => {
                const visibleItems = Array.from(section.querySelectorAll('[data-sj-directory-item]'))
                    .filter((item) => !item.hidden);
                const count = section.querySelector('[data-sj-directory-section-count]');

                section.hidden = visibleItems.length === 0;
                if (count) {
                    count.textContent = String(visibleItems.length);
                }
            });

            if (empty) {
                empty.hidden = visibleCount > 0;
            }

            renderActiveFilters(directory, searchInput, termSelects, applyFilters);
        }

        termSelects.forEach((select) => {
            buildSelect(directory, select, applyFilters);
        });

        if (filterForm) {
            filterForm.addEventListener('submit', (event) => {
                event.preventDefault();
                applyFilters();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', applyFilters);
        }

        directory.addEventListener('click', (event) => {
            if (!event.target.closest('.sj-directory-select')) {
                closeAll(directory);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeAll(directory);
            }
        });

        applyFilters();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-sj-directory]').forEach(initDirectory);
        });
    } else {
        document.querySelectorAll('[data-sj-directory]').forEach(initDirectory);
    }
}());
