(function () {
    var editors = new WeakMap();

    function setHidden(element, hidden) {
        if (!element) {
            return;
        }

        element.hidden = hidden;
    }

    function initEditors() {
        document.querySelectorAll('[data-iif-form]').forEach(function (form) {
            var editorElement = form.querySelector('[data-iif-editor]');
            var fallback = form.querySelector('[data-iif-fallback]');

            if (!editorElement || editors.has(form) || typeof window.Quill === 'undefined') {
                return;
            }

            var editor = new window.Quill(editorElement, {
                theme: 'snow',
                placeholder: 'Omschrijf kort je vraag of aanvraag.',
                modules: {
                    toolbar: [
                        ['bold', 'italic', 'underline'],
                        [{ list: 'ordered' }, { list: 'bullet' }],
                        ['link'],
                        ['clean']
                    ]
                }
            });

            editors.set(form, editor);

            if (fallback) {
                fallback.hidden = true;
                fallback.disabled = true;
                fallback.required = false;
            }
        });
    }

    function getMessageHtml(form) {
        var editor = editors.get(form);
        var hiddenMessage = form.querySelector('[data-iif-message]');
        var fallback = form.querySelector('[data-iif-fallback]');
        var html = '';

        if (editor) {
            html = editor.root.innerHTML;

            if (!editor.getText().trim()) {
                html = '';
            }
        } else if (fallback) {
            html = fallback.value;
        }

        if (hiddenMessage) {
            hiddenMessage.value = html;
        }

        return html;
    }

    function resetEditor(form) {
        var editor = editors.get(form);

        if (editor) {
            editor.setContents([]);
        }
    }

    function setSubmitting(form, submitting) {
        var submit = form.querySelector('.iif-submit');
        var label = form.querySelector('[data-iif-submit-label]');
        var loading = form.querySelector('[data-iif-submit-loading]');

        form.setAttribute('aria-busy', submitting ? 'true' : 'false');

        if (submit) {
            submit.disabled = submitting;
        }

        setHidden(label, submitting);
        setHidden(loading, !submitting);
    }

    document.addEventListener('submit', function (event) {
        var form = event.target;

        if (!form.matches('[data-iif-form]')) {
            return;
        }

        event.preventDefault();

        var config = window.InhurenInfoFormConfig || {};
        var wrap = form.closest('.iif-wrap');
        var error = wrap ? wrap.querySelector('[data-iif-error]') : null;
        var success = wrap ? wrap.querySelector('[data-iif-success]') : null;

        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        if (!getMessageHtml(form)) {
            if (error) {
                error.textContent = 'Vul het berichtveld in.';
                setHidden(error, false);
            }

            var editor = editors.get(form);
            if (editor) {
                editor.focus();
            }

            return;
        }

        setHidden(error, true);
        setHidden(success, true);
        setSubmitting(form, true);

        fetch(config.ajaxUrl, {
            method: 'POST',
            body: new FormData(form),
            credentials: 'same-origin'
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (result) {
                if (result && result.success) {
                    form.reset();
                    resetEditor(form);
                    setHidden(form, true);
                    setHidden(success, false);

                    if (success) {
                        success.focus();
                    }

                    return;
                }

                if (error) {
                    error.textContent = result && result.data ? result.data : 'Er is iets misgegaan. Probeer het opnieuw.';
                    setHidden(error, false);
                }

                setSubmitting(form, false);
            })
            .catch(function () {
                if (error) {
                    error.textContent = 'Er is een verbindingsfout opgetreden. Probeer het opnieuw.';
                    setHidden(error, false);
                }

                setSubmitting(form, false);
            });
    });

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initEditors);
    } else {
        initEditors();
    }
}());
