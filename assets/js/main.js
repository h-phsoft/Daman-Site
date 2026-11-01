/* ==========================================================================
   سكربت الموقع العام: قائمة الجوال، القوائم المنسدلة ARIA، السلايدر، الأكورديون
   ========================================================================== */
(function () {
  'use strict';

  /* ------------------- القائمة الرئيسية + Hamburger ------------------- */
  var toggle = document.getElementById('navToggle');
  var nav = document.getElementById('primaryNav');
  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      toggle.setAttribute('aria-label', open ? 'إغلاق القائمة' : 'فتح القائمة');
    });
  }

  // القوائم المنسدلة: ماوس + لوحة مفاتيح + لمس
  var parents = Array.prototype.slice.call(document.querySelectorAll('.nav-item.has-sub'));
  var isMobile = function () { return window.matchMedia('(max-width: 980px)').matches; };

  parents.forEach(function (li) {
    var link = li.querySelector(':scope > .nav-link');
    var sub = li.querySelector(':scope > .sub-menu');
    if (!link || !sub) return;

    function setOpen(open) {
      li.classList.toggle('open', open);
      link.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    link.addEventListener('click', function (ev) {
      ev.preventDefault();
      var isOpen = li.classList.contains('open');
      parents.forEach(function (p) {
        p.classList.remove('open');
        var l = p.querySelector(':scope > .nav-link');
        if (l) l.setAttribute('aria-expanded', 'false');
      });
      if (!isOpen) setOpen(true);
    });

    li.addEventListener('mouseenter', function () { if (!isMobile()) setOpen(true); });
    li.addEventListener('mouseleave', function () { if (!isMobile()) setOpen(false); });

    link.addEventListener('keydown', function (ev) {
      if (ev.key === 'ArrowDown' || ev.key === 'Enter' || ev.key === ' ') {
        if (ev.key !== 'Enter') ev.preventDefault();
        setOpen(true);
        var first = sub.querySelector('a');
        if (first) first.focus();
      } else if (ev.key === 'Escape') {
        setOpen(false);
      }
    });

    sub.addEventListener('keydown', function (ev) {
      var items = Array.prototype.slice.call(sub.querySelectorAll('a'));
      var idx = items.indexOf(document.activeElement);
      if (ev.key === 'ArrowDown') { ev.preventDefault(); (items[idx + 1] || items[0]).focus(); }
      else if (ev.key === 'ArrowUp') { ev.preventDefault(); (items[idx - 1] || items[items.length - 1]).focus(); }
      else if (ev.key === 'Escape') { setOpen(false); link.focus(); }
    });
  });

  document.addEventListener('click', function (ev) {
    if (!ev.target.closest('.nav-item')) {
      parents.forEach(function (p) {
        p.classList.remove('open');
        var l = p.querySelector(':scope > .nav-link');
        if (l) l.setAttribute('aria-expanded', 'false');
      });
    }
  });

  /* ------------------- السلايدر الرئيسي (Autoplay + Loop) ------------------- */
  var slider = document.querySelector('[data-slider]');
  if (slider) {
    var slides = Array.prototype.slice.call(slider.querySelectorAll('.slide'));
    var dotsWrap = slider.querySelector('.slider-dots');
    var prevBtn = slider.querySelector('.slider-btn.prev');
    var nextBtn = slider.querySelector('.slider-btn.next');
    var current = 0, timer = null;
    var INTERVAL = parseInt(slider.getAttribute('data-interval') || '5500', 10);

    function show(i) {
      current = (i + slides.length) % slides.length;
      slides.forEach(function (s, k) {
        s.classList.toggle('is-active', k === current);
        s.setAttribute('aria-hidden', k === current ? 'false' : 'true');
      });
      if (dotsWrap) {
        Array.prototype.forEach.call(dotsWrap.children, function (d, k) {
          d.setAttribute('aria-current', k === current ? 'true' : 'false');
        });
      }
    }
    function next() { show(current + 1); }
    function prev() { show(current - 1); }
    function play() { stop(); if (slides.length > 1) timer = setInterval(next, INTERVAL); }
    function stop() { if (timer) clearInterval(timer); timer = null; }

    if (dotsWrap && slides.length) {
      slides.forEach(function (_, i) {
        var b = document.createElement('button');
        b.type = 'button';
        b.setAttribute('aria-label', 'الانتقال إلى الشريحة ' + (i + 1));
        b.addEventListener('click', function () { show(i); play(); });
        dotsWrap.appendChild(b);
      });
    }
    if (prevBtn) prevBtn.addEventListener('click', function () { prev(); play(); });
    if (nextBtn) nextBtn.addEventListener('click', function () { next(); play(); });

    slider.addEventListener('mouseenter', stop);
    slider.addEventListener('mouseleave', play);
    slider.addEventListener('focusin', stop);
    slider.addEventListener('focusout', play);
    slider.addEventListener('keydown', function (ev) {
      if (ev.key === 'ArrowRight') { prev(); play(); } // RTL: يمين = السابق بصريًا
      if (ev.key === 'ArrowLeft') { next(); play(); }
    });

    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    show(0);
    if (!reduceMotion) play();
  }

  /* ------------------- Accordion مع ARIA كامل ------------------- */
  var accordions = document.querySelectorAll('[data-accordion]');
  accordions.forEach(function (acc) {
    var triggers = Array.prototype.slice.call(acc.querySelectorAll('.acc-trigger'));

    function closeAll(except) {
      triggers.forEach(function (t) {
        if (t === except) return;
        t.setAttribute('aria-expanded', 'false');
        var panel = document.getElementById(t.getAttribute('aria-controls'));
        if (panel) panel.setAttribute('data-open', 'false');
      });
    }

    triggers.forEach(function (t, i) {
      t.addEventListener('click', function () {
        var expanded = t.getAttribute('aria-expanded') === 'true';
        closeAll(t);
        t.setAttribute('aria-expanded', expanded ? 'false' : 'true');
        var panel = document.getElementById(t.getAttribute('aria-controls'));
        if (panel) panel.setAttribute('data-open', expanded ? 'false' : 'true');
      });

      t.addEventListener('keydown', function (ev) {
        var target = null;
        if (ev.key === 'ArrowDown') target = triggers[(i + 1) % triggers.length];
        else if (ev.key === 'ArrowUp') target = triggers[(i - 1 + triggers.length) % triggers.length];
        else if (ev.key === 'Home') target = triggers[0];
        else if (ev.key === 'End') target = triggers[triggers.length - 1];
        if (target) { ev.preventDefault(); target.focus(); }
      });
    });

    // فتح أول عنصر افتراضيًا
    if (triggers.length && acc.getAttribute('data-first-open') !== 'false') {
      triggers[0].setAttribute('aria-expanded', 'true');
      var p0 = document.getElementById(triggers[0].getAttribute('aria-controls'));
      if (p0) p0.setAttribute('data-open', 'true');
    }
  });
})();
