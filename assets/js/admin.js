/* ===== سكربتات لوحة التحكم: TinyMCE + تأكيد الحذف ===== */
(function () {
  'use strict';
  // مسار الأصول يُستنتج من رابط هذا الملف (assets/js/admin.js) فيعمل المحرر حتى لو كان الموقع في مجلد فرعي
  var self = document.currentScript || document.querySelector('script[src*="js/admin.js"]');
  var assetsBase = self ? new URL('../', self.src).href : '/assets/';

  if (typeof tinymce !== 'undefined' && document.querySelector('textarea.html-editor')) {
    tinymce.init({
      selector: 'textarea.html-editor',
      language: 'ar',
      base_url: assetsBase + 'vendor/tinymce',
      suffix: '.min',
      rtl: true,
      skin: document.documentElement.classList.contains('light') ? 'oxide' : 'oxide-dark',
      promotion: false,
      branding: false,
      height: 320,
      min_height: 220,
      resize: true,
      menubar: 'edit insert format table',
      plugins: 'lists link image table code autolink charmap preview searchreplace wordcount directionality fullscreen',
      toolbar: 'undo redo | blocks bold italic underline strikethrough | forecolor backcolor | alignright aligncenter alignleft alignjustify | bullist numlist outdent indent | link image table | codesample blockquote | ltr rtl | removeformat code preview fullscreen',
      content_style: "body{font-family:'Cairo','Tajawal',Tahoma,Arial,sans-serif;font-size:15px;line-height:1.8;direction:rtl;} img{max-width:100%;height:auto;}",
      content_css: assetsBase + 'vendor/fonts/fonts.css,' + assetsBase + 'css/style.css',
      convert_urls: false,
      relative_urls: false,
      removed_menuitems: 'newdocument',
      image_caption: true,
      automatic_uploads: false,
      paste_data_images: false,
      setup: function (editor) {
        // منطقة الكتابة تستخدم ألوان النمط الحالي (فاتح/داكن) نفسها
        editor.on('init', function () {
          if (document.documentElement.classList.contains('light')) {
            editor.getDoc().documentElement.classList.add('light');
          }
        });
      }
    });
  }
  // تأكيد الحذف
  document.addEventListener('click', function (ev) {
    var btn = ev.target.closest('[data-confirm]');
    if (btn && !window.confirm(btn.getAttribute('data-confirm'))) {
      ev.preventDefault();
      ev.stopPropagation();
    }
  });
})();
