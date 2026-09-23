/* Reagan Soft Innovation Limited — front-end interactions */
(function () {
  'use strict';

  function ready(fn) {
    if (document.readyState !== 'loading') { fn(); } else { document.addEventListener('DOMContentLoaded', fn); }
  }

  ready(function () {
    /* ---------- Public mobile navigation ---------- */
    var navToggle = document.getElementById('nav-toggle');
    var nav = document.getElementById('primary-nav');
    if (navToggle && nav) {
      navToggle.addEventListener('click', function () {
        var open = nav.classList.toggle('open');
        navToggle.classList.toggle('open', open);
        navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
      nav.querySelectorAll('a').forEach(function (a) {
        a.addEventListener('click', function () {
          nav.classList.remove('open');
          navToggle.classList.remove('open');
          navToggle.setAttribute('aria-expanded', 'false');
        });
      });
    }

    /* ---------- Dashboard sidebar toggle ---------- */
    var sideToggle = document.getElementById('sidebar-toggle');
    var sidebar = document.getElementById('sidebar');
    if (sideToggle && sidebar) {
      sideToggle.addEventListener('click', function () {
        var open = sidebar.classList.toggle('open');
        sideToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
      });
      /* swipe / escape to close */
      document.addEventListener('keydown', function (ev) {
        if (ev.key === 'Escape' && sidebar.classList.contains('open')) {
          sidebar.classList.remove('open');
          sideToggle.setAttribute('aria-expanded', 'false');
        }
      });
    }

    /* ---------- Smooth scroll for same-page anchors ---------- */
    document.querySelectorAll('a[href^="#"]').forEach(function (a) {
      a.addEventListener('click', function (ev) {
        var hash = a.getAttribute('href');
        if (hash.length < 2) { return; }
        var target = document.querySelector(hash);
        if (target) {
          ev.preventDefault();
          target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      });
    });

    /* ---------- Duplicate-submit guard ---------- */
    var submitted = false;
    document.addEventListener('submit', function (ev) {
      var form = ev.target;
      if (!form || form.tagName !== 'FORM') { return; }
      var btn = form.querySelector('button[type="submit"]');
      if (!btn) { return; }
      if (submitted) {
        ev.preventDefault();
        return;
      }
      if (form.dataset.noGuard === '1') { return; }
      submitted = true;
      setTimeout(function () { submitted = false; }, 2500);
    });

    /* Confirmation helpers: forms/links with data-confirm */
    document.addEventListener('click', function (ev) {
      var el = ev.target.closest ? ev.target.closest('[data-confirm]') : null;
      if (el) {
        var msg = el.getAttribute('data-confirm') || 'Are you sure?';
        if (!window.confirm(msg)) {
          ev.preventDefault();
        }
      }
    });

    /* ---------- Modal helpers ---------- */
    function openModal(id) {
      var m = document.getElementById(id);
      if (m) { m.classList.add('open'); document.body.style.overflow = 'hidden'; }
    }
    function closeModal(id) {
      var m = document.getElementById(id);
      if (m) { m.classList.remove('open'); document.body.style.overflow = ''; }
    }
    document.addEventListener('click', function (ev) {
      var opener = ev.target.closest ? ev.target.closest('[data-modal-open]') : null;
      if (opener) { openModal(opener.getAttribute('data-modal-open')); return; }
      var closer = ev.target.closest ? ev.target.closest('[data-modal-close]') : null;
      if (closer) { closeModal(closer.getAttribute('data-modal-close')); return; }
      if (ev.target.classList && ev.target.classList.contains('modal-backdrop')) {
        closeModal(ev.target.id);
      }
    });
    document.addEventListener('keydown', function (ev) {
      if (ev.key === 'Escape') {
        document.querySelectorAll('.modal-backdrop.open').forEach(function (m) {
          m.classList.remove('open');
          document.body.style.overflow = '';
        });
      }
    });
    window.rsiOpenModal = openModal;
    window.rsiCloseModal = closeModal;

    /* ---------- Dynamic line items (quotations / invoices) ---------- */
    var itemCounter = 1;
    document.addEventListener('click', function (ev) {
      var add = ev.target.closest ? ev.target.closest('[data-add-item]') : null;
      if (add) {
        ev.preventDefault();
        var tpl = document.getElementById('line-item-template');
        if (!tpl) { return; }
        var holder = document.querySelector(add.getAttribute('data-target') || '.line-items');
        var clone = document.importNode(tpl.content, true);
        var key = 'k' + Date.now() + '_' + (itemCounter++);
        clone.querySelectorAll('input,textarea,select').forEach(function (el) {
          if (el.name) { el.name = el.name.replace(/items\[n\]/, 'items[' + key + ']'); }
        });
        holder.appendChild(clone);
        var row = holder.querySelector(':scope .line-item:last-of-type');
        var cancel = row ? row.querySelector('.remove-line') : null;
        if (cancel) {
          cancel.addEventListener('click', function (e) { e.preventDefault(); row.remove(); });
        }
      }
    });

    /* ---------- Reading confirmations on POST forms (client actions) ---------- */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
      form.addEventListener('submit', function (ev) {
        var msg = form.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) { ev.preventDefault(); }
      });
    });

    /* ---------- Auto-dismiss transient alerts ---------- */
    document.querySelectorAll('.alert.autodismiss').forEach(function (el) {
      setTimeout(function () {
        el.style.transition = 'opacity .4s';
        el.style.opacity = '0';
        setTimeout(function () { el.remove(); }, 450);
      }, 7000);
    });
  });
})();