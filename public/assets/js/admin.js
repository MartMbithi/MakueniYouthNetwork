/* ───────────────────────────────────────────────────────────────────────
 * Makueni Youth Network — admin back office JS.
 * Wires SweetAlert2, the image-upload widget, TinyMCE, slug auto-fill,
 * and flash-message toasts. Vanilla JS, no build step.
 * ──────────────────────────────────────────────────────────────────── */

(function () {
  'use strict';

  // ───────────────────────  Helpers  ───────────────────────

  function formatBytes(b) {
    if (!b && b !== 0) return '';
    if (b < 1024) return b + ' B';
    if (b < 1024 * 1024) return (b / 1024).toFixed(0) + ' KB';
    return (b / 1024 / 1024).toFixed(1) + ' MB';
  }

  function slugify(s) {
    return String(s)
      .toLowerCase()
      .normalize('NFKD').replace(/[̀-ͯ]/g, '')
      .replace(/[^a-z0-9]+/g, '-')
      .replace(/(^-|-$)/g, '')
      .substring(0, 80);
  }

  // ───────────────────────  Flash → SwAlert toasts  ───────────────────────

  function showFlashToasts() {
    var node = document.getElementById('admin-flash-data');
    if (!node || !window.Swal) return;
    var list = [];
    try { list = JSON.parse(node.textContent || '[]'); } catch (e) { return; }
    if (!Array.isArray(list) || !list.length) return;

    var Toast = Swal.mixin({
      toast: true,
      position: 'top-end',
      showConfirmButton: false,
      timer: 4200,
      timerProgressBar: true,
      didOpen: function (t) {
        t.addEventListener('mouseenter', Swal.stopTimer);
        t.addEventListener('mouseleave', Swal.resumeTimer);
      },
    });

    list.forEach(function (f, i) {
      var iconMap = { success: 'success', error: 'error', info: 'info', warning: 'warning' };
      var icon = iconMap[f.type] || 'info';
      setTimeout(function () {
        Toast.fire({ icon: icon, title: f.message });
      }, i * 350);
    });
  }

  // ───────────────────────  Delete / generic confirm dialogs  ───────────────────────

  function wireConfirmForms() {
    if (!window.Swal) return;
    document.addEventListener('submit', function (e) {
      var form = e.target.closest('form[data-confirm]');
      if (!form) return;
      if (form.dataset.confirmed === '1') return;

      e.preventDefault();
      Swal.fire({
        icon: form.dataset.confirmIcon || 'warning',
        title: form.dataset.confirmTitle || 'Are you sure?',
        text: form.dataset.confirm,
        showCancelButton: true,
        confirmButtonText: form.dataset.confirmButton || 'Yes',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6a6f78',
        reverseButtons: true,
      }).then(function (result) {
        if (result.isConfirmed) {
          form.dataset.confirmed = '1';
          form.submit();
        }
      });
    });
  }

  // ───────────────────────  Image upload widget  ───────────────────────

  function wireImageUpload(widget) {
    var card        = widget.querySelector('.image-upload-card');
    var fileInput   = widget.querySelector('.image-upload-input');
    var removeBtn   = widget.querySelector('.image-upload-remove');
    var removeFlag  = widget.querySelector('.image-upload-remove-flag');
    var previewWrap = widget.querySelector('.image-upload-preview');
    var filename    = widget.querySelector('.image-upload-filename');
    var urlToggle   = widget.querySelector('.image-upload-url-toggle a');
    var urlWrap     = widget.querySelector('.image-upload-url-wrap');
    var urlInput    = widget.querySelector('.image-upload-url-input');
    var chooseLabel = widget.querySelector('.image-upload-actions label .bi-upload');

    if (!card || !fileInput) return;

    function renderPreview(src, name, size) {
      previewWrap.innerHTML = '<img src="' + src + '" alt="" class="image-upload-thumb">';
      filename.textContent = (name || 'image') + (size ? ' · ' + formatBytes(size) : '');
      widget.classList.add('has-image');
      if (removeBtn) removeBtn.hidden = false;
      removeFlag.value = '0';
      if (chooseLabel && chooseLabel.nextSibling) {
        chooseLabel.parentNode.childNodes.forEach(function (n) {
          if (n.nodeType === 3) { n.textContent = ' Replace'; }
        });
      }
    }

    function clearPreview() {
      previewWrap.innerHTML = '<div class="image-upload-empty"><i class="ri ri-image-line"></i></div>';
      filename.innerHTML = '<span class="text-muted">No image selected</span>';
      widget.classList.remove('has-image');
      if (removeBtn) removeBtn.hidden = true;
      fileInput.value = '';
      if (urlInput) urlInput.value = '';
      removeFlag.value = '1';
    }

    fileInput.addEventListener('change', function (e) {
      var file = e.target.files && e.target.files[0];
      if (!file) return;
      var reader = new FileReader();
      reader.onload = function (ev) {
        renderPreview(ev.target.result, file.name, file.size);
      };
      reader.readAsDataURL(file);
    });

    if (removeBtn) {
      removeBtn.addEventListener('click', clearPreview);
    }

    if (urlToggle) {
      urlToggle.addEventListener('click', function (e) {
        e.preventDefault();
        urlWrap.hidden = !urlWrap.hidden;
        if (!urlWrap.hidden && urlInput) urlInput.focus();
      });
    }

    if (urlInput) {
      var urlTimer;
      urlInput.addEventListener('input', function (e) {
        clearTimeout(urlTimer);
        var v = e.target.value.trim();
        if (!v) return;
        urlTimer = setTimeout(function () {
          renderPreview(v, v.split('/').pop().split('?')[0]);
        }, 250);
      });
    }

    // Drag-and-drop on the card
    ['dragover', 'dragenter'].forEach(function (ev) {
      card.addEventListener(ev, function (e) {
        e.preventDefault();
        card.classList.add('dragover');
      });
    });
    ['dragleave', 'drop'].forEach(function (ev) {
      card.addEventListener(ev, function (e) {
        e.preventDefault();
        card.classList.remove('dragover');
      });
    });
    card.addEventListener('drop', function (e) {
      var file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
      if (!file || !file.type.startsWith('image/')) return;
      try {
        var dt = new DataTransfer();
        dt.items.add(file);
        fileInput.files = dt.files;
      } catch (err) {
        // DataTransfer not supported — fall back to preview only
      }
      var reader = new FileReader();
      reader.onload = function (ev) {
        renderPreview(ev.target.result, file.name, file.size);
      };
      reader.readAsDataURL(file);
    });
  }

  function wireAllImageUploads() {
    document.querySelectorAll('.image-upload').forEach(wireImageUpload);
  }

  // ───────────────────────  Slug auto-fill  ───────────────────────

  function wireSlugAutofill() {
    var titleInput = document.getElementById('title');
    var slugInput  = document.getElementById('slug');
    if (!titleInput || !slugInput) return;
    var manuallyEdited = slugInput.value.trim() !== '';
    slugInput.addEventListener('input', function () { manuallyEdited = slugInput.value.trim() !== ''; });
    titleInput.addEventListener('input', function () {
      if (manuallyEdited) return;
      slugInput.value = slugify(titleInput.value);
    });
  }

  // ───────────────────────  TinyMCE  ───────────────────────

  function initTinyMce() {
    if (!window.tinymce) return;
    if (!document.querySelector('textarea[data-richtext]')) return;

    tinymce.init({
      selector: 'textarea[data-richtext]',
      height: 420,
      menubar: false,
      branding: false,
      promotion: false,
      relative_urls: false,
      convert_urls: false,
      plugins: 'lists link image table codesample code wordcount autoresize',
      toolbar: 'undo redo | blocks | bold italic underline | bullist numlist | blockquote link image | codesample | removeformat code',
      block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4; Blockquote=blockquote; Code=pre',
      content_style:
        "body { font-family: 'Hanken Grotesk', sans-serif; font-size: 16px; line-height: 1.6; color: #13181f; padding: 8px 12px; }" +
        " h2, h3, h4 { font-family: 'Fraunces', serif; color: #13181f; }" +
        " a { color: #014e8f; }",
      autoresize_bottom_margin: 24,
      min_height: 380,
      images_upload_url: '/admin/upload-image',
      images_upload_credentials: true,
      automatic_uploads: true,
      paste_data_images: true,
    });
  }

  // ───────────────────────  Boot  ───────────────────────

  function boot() {
    showFlashToasts();
    wireConfirmForms();
    wireAllImageUploads();
    wireSlugAutofill();
    initTinyMce();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }
})();
