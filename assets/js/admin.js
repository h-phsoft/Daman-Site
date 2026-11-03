/* ===== سكربتات لوحة التحكم: TinyMCE + تأكيد الحذف ===== */
(function () {
  'use strict';
  if (typeof tinymce !== 'undefined') {
    tinymce.init({
      selector: 'textarea.html-editor',
      language: 'ar',
      base_url: '/assets/vendor/tinymce',
      suffix: '.min',
      rtl: true,
      skin: document.documentElement.classList.contains('light') ? 'oxide' : 'oxide-dark',
      promotion: false,
      branding: false,
      height: 380,
      menubar: 'edit insert format table',
      plugins: 'lists link image table code autolink charmap preview searchreplace wordcount directionality fullscreen',
      toolbar: 'undo redo | blocks bold italic underline strikethrough | forecolor backcolor | alignright aligncenter alignleft alignjustify | bullist numlist outdent indent | link image table | codesample blockquote | ltr rtl | removeformat code preview fullscreen',
      content_style: "body{font-family:'Cairo','Tajawal',Tahoma,Arial,sans-serif;font-size:15px;line-height:1.8;direction:rtl;} img{max-width:100%;height:auto;}",
      content_css: '/assets/vendor/fonts/fonts.css,/assets/css/style.css',
      convert_urls: false,
      relative_urls: false,
      removed_menuitems: 'newdocument',
      image_caption: true,
      automatic_uploads: false,
      paste_data_images: false
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
