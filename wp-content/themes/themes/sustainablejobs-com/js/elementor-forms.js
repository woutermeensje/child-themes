/**
 * Elementor Forms – Rich Text Editor via Quill.js
 *
 * Usage: give the Textarea field in Elementor the
 * extra CSS class "rich-editor" via the field settings.
 *
 * Quill is loaded via CDN (see functions.php enqueue).
 */

document.addEventListener('DOMContentLoaded', function () {

  // Find all textareas with class .rich-editor within an Elementor form
  document.querySelectorAll('.rich-editor textarea, textarea.rich-editor').forEach(function (textarea) {

    // Create a wrapper + container for Quill
    var wrapper = document.createElement('div');
    wrapper.className = 'ql-wrapper';

    var container = document.createElement('div');
    container.id = 'ql-editor-' + Math.random().toString(36).substr(2, 6);
    wrapper.appendChild(container);

    // Insert wrapper before the textarea
    textarea.parentNode.insertBefore(wrapper, textarea);

    // Initialise Quill
    var quill = new Quill(container, {
      theme: 'snow',
      placeholder: textarea.getAttribute('placeholder') || 'Write your message here…',
      modules: {
        toolbar: [
          ['bold', 'italic', 'underline'],
          [{ 'list': 'ordered' }, { 'list': 'bullet' }],
          ['link'],
          ['clean']
        ]
      }
    });

    // Set initial value if textarea already has content
    if (textarea.value) {
      quill.root.innerHTML = textarea.value;
    }

    // Sync Quill content back to the original textarea on submit
    var form = textarea.closest('form');
    if (form) {
      form.addEventListener('submit', function () {
        textarea.value = quill.root.innerHTML;
      });
    }

    // Also sync live for Elementor's own validation
    quill.on('text-change', function () {
      textarea.value = quill.root.innerHTML;
    });
  });
});
