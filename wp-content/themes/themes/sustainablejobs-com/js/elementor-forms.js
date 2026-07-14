/**
 * Elementor Forms – Rich Text Editor via Quill.js
 *
 * Gebruik: geef het Textarea-veld in Elementor de
 * extra CSS-klasse "rich-editor" via de veld-instellingen.
 *
 * Quill wordt geladen via CDN (zie functions.php enqueue).
 */

document.addEventListener('DOMContentLoaded', function () {

  // Search alle textarea's met klasse .rich-editor binnen een Elementor form
  // Selector werkt op beide niveaus: widget-wrapper én veld-niveau
  document.querySelectorAll('.rich-editor textarea, textarea.rich-editor').forEach(function (textarea) {

    // Maak een wrapper + container voor Quill
    var wrapper = document.createElement('div');
    wrapper.className = 'ql-wrapper';

    var container = document.createElement('div');
    container.id = 'ql-editor-' + Math.random().toString(36).substr(2, 6);
    wrapper.appendChild(container);

    // Voeg wrapper in vóór de textarea
    textarea.parentNode.insertBefore(wrapper, textarea);

    // Initialiseer Quill
    var quill = new Quill(container, {
      theme: 'snow',
      placeholder: textarea.getAttribute('placeholder') || 'Schrijf hier je bericht…',
      modules: {
        toolbar: [
          ['bold', 'italic', 'underline'],
          [{ 'list': 'ordered' }, { 'list': 'bullet' }],
          ['link'],
          ['clean']
        ]
      }
    });

    // Zet initiële waarde als textarea al inhoud heeft
    if (textarea.value) {
      quill.root.innerHTML = textarea.value;
    }

    // Sync Quill inhoud terug naar de originele textarea bij submit
    var form = textarea.closest('form');
    if (form) {
      form.addEventListener('submit', function () {
        textarea.value = quill.root.innerHTML;
      });
    }

    // Ook live syncen voor Elementor's eigen validatie
    quill.on('text-change', function () {
      textarea.value = quill.root.innerHTML;
    });
  });
});
