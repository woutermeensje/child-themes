(function () {
    const storageKey = 'sjFavoriteJobs';
    const config = window.SJJobFavoritesConfig || {};
    let fetchedPageJobs = false;

    function readJobs() {
        try {
            const raw = window.localStorage.getItem(storageKey);
            if (!raw) return [];

            const parsed = JSON.parse(raw);
            const list = Array.isArray(parsed) ? parsed : Object.values(parsed || {});
            return uniqueJobs(list.map(normalizeJob).filter(Boolean));
        } catch (error) {
            return [];
        }
    }

    function writeJobs(jobs) {
        const cleanJobs = uniqueJobs(jobs.map(normalizeJob).filter(Boolean));

        try {
            window.localStorage.setItem(storageKey, JSON.stringify(cleanJobs));
        } catch (error) {
            return cleanJobs;
        }

        return cleanJobs;
    }

    function normalizeJob(job) {
        if (!job || !job.id) return null;

        return {
            id: String(job.id),
            title: decodeHtml(job.title || ''),
            url: String(job.url || ''),
            company: decodeHtml(job.company || ''),
            location: decodeHtml(job.location || ''),
            excerpt: decodeHtml(job.excerpt || ''),
            date: String(job.date || '')
        };
    }

    function decodeHtml(value) {
        const textarea = document.createElement('textarea');
        textarea.innerHTML = String(value);
        return textarea.value;
    }

    function uniqueJobs(jobs) {
        const seen = new Set();
        const unique = [];

        jobs.forEach((job) => {
            if (!job || seen.has(job.id)) return;
            seen.add(job.id);
            unique.push(job);
        });

        return unique;
    }

    function getButtonJob(button) {
        return normalizeJob({
            id: button.dataset.jobId,
            title: button.dataset.jobTitle,
            url: button.dataset.jobUrl,
            company: button.dataset.jobCompany,
            location: button.dataset.jobLocation,
            excerpt: button.dataset.jobExcerpt,
            date: button.dataset.jobDate
        });
    }

    function toggleJob(job) {
        if (!job) return;

        const jobs = readJobs();
        const index = jobs.findIndex((item) => item.id === job.id);

        if (index >= 0) {
            jobs.splice(index, 1);
        } else {
            jobs.unshift(job);
        }

        refresh(writeJobs(jobs));
    }

    function removeJob(jobId) {
        const jobs = readJobs().filter((job) => job.id !== String(jobId));
        refresh(writeJobs(jobs));
    }

    function refresh(jobs) {
        const currentJobs = jobs || readJobs();
        updateButtons(currentJobs);
        updateCounts(currentJobs);
        renderFavoritesPage(currentJobs);
    }

    function updateButtons(jobs) {
        const ids = new Set(jobs.map((job) => job.id));

        document.querySelectorAll('[data-sj-favorite-button]').forEach((button) => {
            const active = ids.has(String(button.dataset.jobId));
            const label = active ? 'Remove from my jobs' : 'Save job';

            button.classList.toggle('is-favorite', active);
            button.setAttribute('aria-pressed', active ? 'true' : 'false');
            button.setAttribute('aria-label', label);
            button.setAttribute('title', label);

            const visibleLabel = button.querySelector('.sj-favorite-button__label');
            if (visibleLabel) {
                visibleLabel.textContent = active ? 'Saved' : 'Save job';
            }
        });
    }

    function updateCounts(jobs) {
        const count = jobs.length;

        document.querySelectorAll('[data-sj-favorites-count]').forEach((countEl) => {
            countEl.textContent = String(count);
            countEl.hidden = count === 0;
        });

        document.querySelectorAll('[data-sj-favorites-nav-link]').forEach((link) => {
            link.classList.toggle('has-favorites', count > 0);
            link.setAttribute('aria-label', count > 0 ? `My saved jobs (${count})` : 'My saved jobs');
        });
    }

    function fetchPageJobs(jobs) {
        const list = document.querySelector('[data-sj-favorites-list]');
        if (!list || fetchedPageJobs || !config.ajaxUrl || jobs.length === 0) return;

        fetchedPageJobs = true;
        const params = new URLSearchParams({
            action: 'sj_get_favorite_jobs',
            ids: jobs.map((job) => job.id).join(',')
        });

        window.fetch(`${config.ajaxUrl}?${params.toString()}`, {
            credentials: 'same-origin'
        })
            .then((response) => response.json())
            .then((response) => {
                if (!response || !response.success || !response.data || !Array.isArray(response.data.jobs)) {
                    return;
                }

                const freshJobs = writeJobs(response.data.jobs);
                refresh(freshJobs);
            })
            .catch(() => {});
    }

    function renderFavoritesPage(jobs) {
        const list = document.querySelector('[data-sj-favorites-list]');
        if (!list) return;

        const empty = document.querySelector('[data-sj-favorites-empty]');
        list.innerHTML = '';

        if (jobs.length === 0) {
            list.hidden = true;
            if (empty) empty.hidden = false;
            return;
        }

        list.hidden = false;
        if (empty) empty.hidden = true;

        jobs.forEach((job) => {
            const article = document.createElement('article');
            article.className = 'sj-favorites-page__card';

            const body = document.createElement('div');
            body.className = 'sj-favorites-page__card-body';

            const title = document.createElement('h2');
            title.className = 'sj-favorites-page__card-title';
            const titleLink = document.createElement('a');
            titleLink.href = job.url || '#';
            titleLink.textContent = job.title || 'Job';
            title.appendChild(titleLink);
            body.appendChild(title);

            if (job.company || job.location) {
                const meta = document.createElement('p');
                meta.className = 'sj-favorites-page__card-meta';
                meta.textContent = [job.company, job.location].filter(Boolean).join(' · ');
                body.appendChild(meta);
            }

            if (job.excerpt) {
                const excerpt = document.createElement('p');
                excerpt.className = 'sj-favorites-page__card-excerpt';
                excerpt.textContent = job.excerpt;
                body.appendChild(excerpt);
            }

            const actions = document.createElement('div');
            actions.className = 'sj-favorites-page__card-actions';

            const viewLink = document.createElement('a');
            viewLink.href = job.url || '#';
            viewLink.className = 'sj-favorites-page__view';
            viewLink.textContent = 'View job';
            actions.appendChild(viewLink);

            const removeBtn = document.createElement('button');
            removeBtn.type = 'button';
            removeBtn.className = 'sj-favorites-page__remove';
            removeBtn.setAttribute('data-sj-favorite-remove', job.id);
            removeBtn.textContent = 'Remove';
            actions.appendChild(removeBtn);

            body.appendChild(actions);
            article.appendChild(body);
            list.appendChild(article);
        });

        fetchPageJobs(jobs);
    }

    document.addEventListener('click', function (event) {
        const button = event.target.closest('[data-sj-favorite-button]');
        if (!button) return;

        event.preventDefault();
        event.stopPropagation();
        toggleJob(getButtonJob(button));
    });

    document.addEventListener('click', function (event) {
        const removeButton = event.target.closest('[data-sj-favorite-remove]');
        if (!removeButton) return;

        event.preventDefault();
        removeJob(removeButton.dataset.sjFavoriteRemove);
    });

    window.addEventListener('storage', function (event) {
        if (event.key === storageKey) {
            refresh();
        }
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            refresh();
        });
    } else {
        refresh();
    }

    window.SJJobFavorites = {
        read: readJobs,
        refresh: refresh
    };
})();
