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

    /* ---------- Work gallery: filter by what was handed over ----------
       Each card carries data-kit="website system apps" (written by
       folio_kit_list), so a chip is a plain substring test. With
       JavaScript off, every chip is a no-op button and the full grid
       stays on the page — the filter is an addition to the gallery,
       never the thing that makes it work. */
    (function folioFilter() {
      var bar = document.querySelector('[data-folio-filter]');
      var grid = document.querySelector('[data-folio-grid]');
      if (!bar || !grid) { return; }

      var chips = bar.querySelectorAll('[data-filter]');
      var cards = grid.querySelectorAll('.folio-card');
      var counter = bar.querySelector('[data-filter-count]');
      var empty = document.querySelector('[data-filter-empty]');
      var total = cards.length;

      function apply(key) {
        var shown = 0;
        cards.forEach(function (card) {
          var kit = (card.getAttribute('data-kit') || '') + ' ';
          var match = key === '' || kit.indexOf(' ' + key + ' ') !== -1;
          card.hidden = !match;
          if (match) { shown++; }
        });
        chips.forEach(function (chip) {
          var on = (chip.getAttribute('data-filter') || '') === key;
          chip.classList.toggle('is-active', on);
          chip.setAttribute('aria-pressed', on ? 'true' : 'false');
        });
        if (counter) {
          counter.textContent = shown + (shown === 1 ? ' project' : ' projects') +
            (shown === total ? ' shown' : ' of ' + total + ' shown');
        }
        if (empty) { empty.hidden = shown !== 0; }
      }

      bar.addEventListener('click', function (ev) {
        var chip = ev.target.closest ? ev.target.closest('[data-filter]') : null;
        if (chip) { apply(chip.getAttribute('data-filter') || ''); }
      });
      if (empty) {
        empty.addEventListener('click', function (ev) {
          var reset = ev.target.closest ? ev.target.closest('[data-filter-reset]') : null;
          if (reset) { apply(''); }
        });
      }
    })();
  });

  /* ============================================================
     ENGINEERING LAYER — 3D FX, blueprint grids, HUD, counters
     Injected on every public page so no per-page markup needed.
     ============================================================ */
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var isTouch = ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);

  // `resolve` runs fn now (or after DOMContentLoaded) then returns a cleanup.
  function whenReady(fn) {
    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', fn);
    } else {
      fn();
    }
  }

  whenReady(function () {

    /* ---------- Sticky nav shrink ---------- */
    (function navShrink() {
      var bar = document.querySelector('.topbar');
      if (!bar) { return; }
      var onScroll = function () {
        bar.classList.toggle('scrolled', window.scrollY > 12);
      };
      window.addEventListener('scroll', onScroll, { passive: true });
      onScroll();
    })();

    /* ---------- HUD corner brackets ---------- */
    function addCorners(el) {
      if (!el || el.querySelector('.fx-corners')) { return; }
      var c = document.createElement('div');
      c.className = 'fx-corners';
      c.innerHTML = '<span class="c1"></span><span class="c2"></span><span class="c3"></span><span class="c4"></span>';
      c.setAttribute('aria-hidden', 'true');
      el.appendChild(c);
    }

    document.querySelectorAll('.page-hero, .auth-page, .cta').forEach(addCorners);

    /* ---------- Readout strip for page heroes + auth ---------- */
    document.querySelectorAll('.page-hero, .auth-page').forEach(function (el) {
      if (el.querySelector('.fx-readout')) { return; }
      var ro = document.createElement('div');
      ro.className = 'fx-readout';
      ro.setAttribute('aria-hidden', 'true');
      ro.innerHTML = '<span class="ro-blink"></span><span>RSI&nbsp;//&nbsp;SYSTEM&nbsp;ONLINE</span><span>JINJA&nbsp;-&nbsp;UG</span>';
      el.appendChild(ro);
    });

    /* ---------- Glow orbs behind page heroes, auth + cta ---------- */
    document.querySelectorAll('.page-hero, .auth-page, .cta').forEach(function (el) {
      if (el.querySelector('.fx-orbs')) { return; }
      var wrap = document.createElement('div');
      wrap.className = 'fx-orbs';
      wrap.setAttribute('aria-hidden', 'true');
      wrap.innerHTML = '<span class="fx-orb o1"></span><span class="fx-orb o2"></span>';
      el.appendChild(wrap);
    });

    /* ---------- Section watermark numbers ---------- */
    (function secMarks() {
      var heads = document.querySelectorAll('.section-head');
      heads.forEach(function (head, i) {
        if (head.querySelector('.sec-mark')) { return; }
        head.style.position = 'relative';
        var mark = document.createElement('span');
        mark.className = 'sec-mark';
        mark.setAttribute('aria-hidden', 'true');
        mark.textContent = String(i + 1).padStart(2, '0');
        head.appendChild(mark);
      });
    })();

    /* ---------- Scroll reveal ---------- */
    var revealTargets = document.querySelectorAll(
      '.section-head, .split-banner, .value-card, .svc-card, .price-card, ' +
      '.folio-card, .pf-step, .faq-item, .founder-card, .checkout-auth, .deposit-box'
    );
    if ('IntersectionObserver' in window && !reduceMotion) {
      revealTargets.forEach(function (el, i) {
        el.classList.add('reveal');
        if (i % 6 < 3) { el.style.transitionDelay = '40ms'; }
      });
      var robs = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) {
            en.target.classList.add('in');
            robs.unobserve(en.target);
          }
        });
      }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
      revealTargets.forEach(function (el) { robs.observe(el); });
    }

    /* ---------- Network canvas (page heroes + auth) ----------
       The home hero is excluded on purpose: the particle web sat on top of
       the centred copy and made it hard to read. */
    var canvases = [];
    document.querySelectorAll('.page-hero, .auth-page').forEach(function (host) {
      if (!host || host.querySelector('.fx-canvas')) { return; }
      var cv = document.createElement('canvas');
      cv.className = 'fx-canvas';
      cv.setAttribute('aria-hidden', 'true');
      host.appendChild(cv);

      var ctx = cv.getContext('2d');
      var parts = [];
      var rafId = 0;
      var running = false;
      var W = 0, H = 0;

      function size() {
        var r = host.getBoundingClientRect();
        W = cv.width = Math.max(2, Math.round(r.width));
        H = cv.height = Math.max(2, Math.round(r.height));
        var n = Math.min(90, Math.max(34, Math.round((W * H) / 22000)));
        parts.length = 0;
        for (var i = 0; i < n; i++) {
          parts.push({ x: Math.random() * W, y: Math.random() * H, vx: (Math.random() - .5) * .35, vy: (Math.random() - .5) * .35, r: Math.random() * 1.6 + 1 });
        }
      }

      function frame() {
        if (!running) { return; }
        ctx.clearRect(0, 0, W, H);
        var line = '10,123,206';
        var dot = '10,123,206';
        ctx.lineWidth = 1;
        var link = 128;
        for (var i = 0; i < parts.length; i++) {
          var p = parts[i];
          p.x += p.vx; p.y += p.vy;
          if (p.x < -20) { p.x = W + 20; } if (p.x > W + 20) { p.x = -20; }
          if (p.y < -20) { p.y = H + 20; } if (p.y > H + 20) { p.y = -20; }
          for (var j = i + 1; j < parts.length; j++) {
            var q = parts[j];
            var dx = p.x - q.x, dy = p.y - q.y;
            var d2 = dx * dx + dy * dy;
            if (d2 < link * link) {
              var a = (1 - Math.sqrt(d2) / link) * .22;
              ctx.strokeStyle = 'rgba(' + line + ',' + a + ')';
              ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(q.x, q.y); ctx.stroke();
            }
          }
        }
        for (var k = 0; k < parts.length; k++) {
          var s = parts[k];
          ctx.fillStyle = 'rgba(' + dot + ',.4)';
          ctx.beginPath(); ctx.arc(s.x, s.y, s.r, 0, 6.283); ctx.fill();
        }
        rafId = requestAnimationFrame(frame);
      }

      function stop() { running = false; cancelAnimationFrame(rafId); }
      function start() {
        if (running) { return; }
        running = true;
        if (reduceMotion) {
          frame(); running = false;           // single static pass
        } else {
          rafId = requestAnimationFrame(frame);
        }
      }

      if (!reduceMotion && 'IntersectionObserver' in window) {
        var cvObs = new IntersectionObserver(function (entries) {
          entries.forEach(function (en) {
            if (en.isIntersecting) { start(); } else { stop(); }
          });
        }, { threshold: 0 });
        cvObs.observe(cv);
      }
      if (!reduceMotion && !('IntersectionObserver' in window)) { start(); }

      size();
      window.addEventListener('resize', size, { passive: true });
      if (reduceMotion) { start(); }
      canvases.push({ cv: cv, start: start, stop: stop });
    });

    /* ---------- Count-up counters: [data-count] ---------- */
    if ('IntersectionObserver' in window) {
      var cObs = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (!en.isIntersecting) { return; }
          var el = en.target;
          var target = parseFloat(el.getAttribute('data-count'));
          if (isNaN(target)) { return; }
          el.classList.add('ctr');
          var dec = (String(target).split('.')[1] || '').length;
          var dur = 1200;
          var t0 = performance.now();
          if (reduceMotion) { el.textContent = el.getAttribute('data-count'); cObs.unobserve(el); return; }
          function tick(now) {
            var p = Math.min(1, (now - t0) / dur);
            var ease = 1 - Math.pow(1 - p, 3);
            var v = target * ease;
            el.textContent = dec > 0 ? v.toFixed(dec) : Math.round(v).toLocaleString('en-US');
            if (p < 1) { requestAnimationFrame(tick); } else {
              el.textContent = el.getAttribute('data-count');
              cObs.unobserve(el);
            }
          }
          requestAnimationFrame(tick);
        });
      }, { threshold: 0.4 });
      document.querySelectorAll('[data-count]').forEach(function (el) { cObs.observe(el); });
    }

    /* ---------- 3D tilt on cards ---------- */
    if (!isTouch && !reduceMotion) {
      var tiltEls = document.querySelectorAll('.svc-card, .value-card, .price-card, .program-card');
      tiltEls.forEach(function (card) {
        card.classList.add('tilt');
        card.addEventListener('mousemove', function (ev) {
          var r = card.getBoundingClientRect();
          var px = (ev.clientX - r.left) / r.width - .5;
          var py = (ev.clientY - r.top) / r.height - .5;
          card.style.transform = 'perspective(900px) rotateX(' + (-py * 4).toFixed(2) + 'deg) rotateY(' + (px * 4).toFixed(2) + 'deg) translateY(-3px)';
        });
        card.addEventListener('mouseleave', function () {
          card.style.transform = '';
        });
      });
    }

    /* ---------- Home hero slider ---------- */
    var heroSlider = document.getElementById('heroSlider');
    if (heroSlider) {
      var hSlidesWrap = heroSlider.querySelector('.hero-slides');
      var hSlides = Array.prototype.slice.call(heroSlider.querySelectorAll('.hero-slide'));
      var hDotsWrap = heroSlider.querySelector('[data-dots]');
      var hPrev = heroSlider.querySelector('[data-prev]');
      var hNext = heroSlider.querySelector('[data-next]');
      var hProgress = heroSlider.querySelector('[data-progress]');
      var iActive = 0, hTimer = reduceMotion ? 0 : 6400;
      var hRaf = 0, hStart = 0, hRunning = false;

      function heroGoTo (idx) {
        if (hSlides.length === 0) return;
        iActive = (idx + hSlides.length) % hSlides.length;
        hSlides.forEach(function (s, i) {
          s.classList.toggle('s-active', i === iActive);
          s.setAttribute('aria-hidden', i === iActive ? 'false' : 'true');
        });
        Array.prototype.forEach.call(heroSlider.querySelectorAll('.hero-dot'), function (d, i) {
          d.classList.toggle('s-active', i === iActive);
          d.setAttribute('aria-current', i === iActive ? 'true' : 'false');
        });
        var live = heroSlider.querySelector('[aria-live]');
        if (live) {
          var activeTxt = hSlides[iActive].querySelector('.hero-title');
          live.textContent = activeTxt ? activeTxt.textContent.replace(/\s+/g, ' ').trim() : '';
          heroSlider.classList.remove('is-settled');
          void heroSlider.offsetWidth;
          heroSlider.classList.add('is-settled');
        }
      }
      function heroNext () { heroGoTo(iActive + 1); }
      function heroPrev () { heroGoTo(iActive - 1); }

      /* One rAF loop drives both the advance and the progress bar, so the
         bar can never drift out of step with the timer the way a
         setInterval plus a separate CSS animation would. */
      function heroFrame (now) {
        hRaf = 0;
        if (!hRunning) { return; }
        var elapsed = now - hStart;
        if (hProgress) {
          hProgress.style.width = Math.min(100, (elapsed / hTimer) * 100).toFixed(2) + '%';
        }
        if (elapsed >= hTimer) { hStart = now; heroGoTo(iActive + 1); }
        hRaf = requestAnimationFrame(heroFrame);
      }
      function heroStop () {
        hRunning = false;
        if (hRaf) { cancelAnimationFrame(hRaf); hRaf = 0; }
        if (hProgress) { hProgress.classList.remove('is-running'); }
      }
      function heroStart () {
        if (!hTimer || reduceMotion || hRunning) { return; }
        hRunning = true;
        hStart = (window.performance || Date).now();
        if (hProgress) {
          hProgress.style.width = '0%';
          hProgress.classList.add('is-running');
        }
        hRaf = requestAnimationFrame(heroFrame);
      }
      function heroPause () { heroStop(); }
      function heroResume () { if (!hTimer || reduceMotion || document.hidden) return; heroStart(); }

      if (hDotsWrap) {
        hSlides.forEach(function (slide, i) {
          var b = document.createElement('button');
          b.type = 'button';
          b.className = 'hero-dot' + (i === 0 ? ' s-active' : '');
          var label = slide.getAttribute('data-label');
          b.setAttribute('aria-label', 'Show slide ' + (i + 1) + ' of ' + hSlides.length +
            (label ? ': ' + label : ''));
          b.setAttribute('aria-current', i === 0 ? 'true' : 'false');
          b.addEventListener('click', function () { heroGoTo(i); heroResume(); });
          hDotsWrap.appendChild(b);
        });
      }

      if (hPrev) hPrev.addEventListener('click', function () { heroPrev(); heroResume(); });
      if (hNext) hNext.addEventListener('click', function () { heroNext(); heroResume(); });

      heroSlider.addEventListener('mouseenter', heroPause);
      heroSlider.addEventListener('mouseleave', heroResume);
      heroSlider.addEventListener('focusin', heroPause);
      heroSlider.addEventListener('focusout', function (e) {
        if (!heroSlider.contains(e.relatedTarget)) heroResume();
      });

      document.addEventListener('keydown', function (e) {
        if (e.target.closest && e.target.closest('input, textarea, select')) return;
        if (e.key === 'ArrowLeft' && heroSlider.contains(e.target)) { heroPrev(); heroResume(); }
        if (e.key === 'ArrowRight' && heroSlider.contains(e.target)) { heroNext(); heroResume(); }
      });

      var swipeX = null, swipeY = null;
      function heroResetSwipe () {
        if (hSlidesWrap) hSlidesWrap.style.transform = '';
        swipeX = null;
        swipeY = null;
        heroResume();
      }
      heroSlider.addEventListener('touchstart', function (e) {
        swipeX = swipeY = null;
        if (e.touches && e.touches.length === 1) {
          swipeX = e.touches[0].clientX;
          swipeY = e.touches[0].clientY;
        }
        heroPause();
      }, { passive: true });
      heroSlider.addEventListener('touchmove', function (e) {
        if (swipeX !== null && e.touches && e.touches.length === 1 && hSlidesWrap) {
          var dx = e.touches[0].clientX - swipeX;
          var dy = e.touches[0].clientY - swipeY;
          if (Math.abs(dx) > 8 && Math.abs(dx) > Math.abs(dy)) {
            hSlidesWrap.style.transform = 'translateX(' + dx + 'px)';
          }
        }
      }, { passive: true });
      heroSlider.addEventListener('touchend', function (e) {
        if (swipeX === null) return;
        var endX = e.changedTouches ? e.changedTouches[0].clientX : swipeX;
        var dx = endX - swipeX;
        var dy = e.changedTouches ? e.changedTouches[0].clientY - swipeY : 0;
        if (Math.abs(dx) > 46 && Math.abs(dx) > Math.abs(dy)) { dx < 0 ? heroNext() : heroPrev(); }
        heroResetSwipe();
      }, { passive: true });
      heroSlider.addEventListener('touchcancel', heroResetSwipe, { passive: true });

      document.addEventListener('visibilitychange', function () {
        document.hidden ? heroPause() : heroResume();
      });

      heroGoTo(0);
      heroStart();
    }

    /* ---------- Payment details gate ----------
     * The form a client fills in before they can pay. Three jobs, all
     * of them about not surprising them:
     *   1. hide the business-only questions when they say they are not
     *      a business, so nobody is asked for a TIN they do not have;
     *   2. keep the progress meter and the "still needed" list honest
     *      as they type, so submitting is never a surprise;
     *   3. show the number we will actually call, so a typo in a phone
     *      number — the usual reason a payment sits unconfirmed — is
     *      visible before they hand over money rather than after.
     * The server re-checks all of it. This only saves them the
     * round-trip of finding out they were wrong. */
    var billingForm = document.querySelector('[data-billing-form]');

    if (billingForm) {
      var bMeter = billingForm.querySelector('[data-billing-meter]');
      var bFill = billingForm.querySelector('[data-billing-fill]');
      var bDone = billingForm.querySelector('[data-billing-done]');
      var bRequired = Array.prototype.slice.call(billingForm.querySelectorAll('[data-billing-field][data-required="1"]'));
      var bTracked = Array.prototype.slice.call(billingForm.querySelectorAll('[data-billing-item]'));
      var bBusinessOnly = Array.prototype.slice.call(billingForm.querySelectorAll('[data-business-only]'));
      var bPhone = billingForm.querySelector('[data-billing-phone]');
      var bPhoneEcho = billingForm.querySelector('[data-billing-phone-echo]');

      /* Snapshot which fields the server marked required, before any
       * toggling happens. The payer-type switch flips data-required back
       * and off, so without a copy to restore from, switching to
       * "just me" and back would quietly drop the TIN from the list
       * and let the bar reach 100% over a missing TIN. */
      var bDefaultRequired = {};
      Array.prototype.slice.call(billingForm.querySelectorAll('[data-billing-field][data-required]')).forEach(function (el) {
        bDefaultRequired[el.getAttribute('data-billing-field')] = el.getAttribute('data-required') === '1';
      });

      /* Mirrors phone_digits() in inc/functions.php: reduce whatever was
       * typed to a dialable national form. Anything we do not recognise
       * is handed back untouched rather than guessed at. */
      function bDigits(raw) {
        var d = String(raw || '').replace(/\D+/g, '');
        if (!d) { return ''; }
        if (d.indexOf('256') === 0) { return d; }
        if (d.charAt(0) === '0') { d = d.slice(1); }
        if (d.length === 9) { return '256' + d; }
        return d;
      }

      function bGrouped(d) {
        if (d.length === 12 && d.indexOf('256') === 0) {
          return '+' + d.slice(0, 3) + ' ' + d.slice(3, 6) + ' ' + d.slice(6, 9) + ' ' + d.slice(9);
        }
        return d;
      }

      function bFilled(el) {
        if (!el) { return false; }
        if (el.type === 'checkbox') { return el.checked; }
        return String(el.value || '').trim() !== '';
      }

      function bPayerIsBusiness() {
        var checked = billingForm.querySelector('input[name="bill_payer_type"]:checked');
        return !checked || checked.value === 'business';
      }

      function bApplyPayerType() {
        var isBusiness = bPayerIsBusiness();
        bBusinessOnly.forEach(function (wrap) {
          var input = wrap.querySelector('.input, .select');
          if (!input) { return; }
          var key = input.getAttribute('data-billing-field');
          if (isBusiness) {
            wrap.classList.remove('is-hidden');
            input.required = !!bDefaultRequired[key];
            input.setAttribute('data-required', bDefaultRequired[key] ? '1' : '0');
          } else {
            /* Hidden, not disabled. A disabled input is not submitted,
             * which would throw away a trading name or TIN that is
             * still on file from a previous visit. Dropping it from the
             * required list as well is what keeps the meter showing 8
             * for an individual where the server also shows 8. */
            wrap.classList.add('is-hidden');
            input.required = false;
            input.setAttribute('data-required', '0');
          }
        });
        bRefresh();
      }

      function bRefreshPhone() {
        if (!bPhone || !bPhoneEcho) { return; }
        var typed = String(bPhone.value || '').trim();
        if (!typed) {
          bPhoneEcho.textContent = '';
          bPhoneEcho.className = 'phone-echo';
          return;
        }
        var d = bDigits(typed);
        if (d.length >= 9 && d.length <= 15) {
          bPhoneEcho.textContent = 'We will call ' + bGrouped(d) + '.';
          bPhoneEcho.className = 'phone-echo ok';
        } else {
          bPhoneEcho.textContent = 'Keep typing — we need at least 9 digits.';
          bPhoneEcho.className = 'phone-echo warn';
        }
      }

      function bRefresh() {
        /* Count the required units only — the same list billing_missing()
         * reads on the server. Counting the optional questions as well
         * would leave an individual with no TIN and no NIN stuck at 91%
         * having answered everything actually asked of them, and the
         * gate would then disagree with the bar on the same page. */
        var done = 0;
        bRequired.forEach(function (el) {
          /* The payer-type radio always has a selection, so it counts
           * as answered from the start. */
          var ok = el.hasAttribute('data-billing-choice') ? true : bFilled(el);
          if (ok) { done++; }
        });

        var total = bRequired.length;
        var percent = total > 0 ? Math.round((done / total) * 100) : 0;

        if (bFill) { bFill.style.width = percent + '%'; }
        if (bDone) { bDone.textContent = String(done); }
        if (bMeter) { bMeter.classList.toggle('is-complete', done >= total); }

        /* Tick the shared "still needed" list off. It is looked up on
         * the document rather than the form because on the payment
         * details page it sits in the column beside the form. */
        document.querySelectorAll('[data-billing-check]').forEach(function (li) {
          var key = li.getAttribute('data-billing-check');
          var el = billingForm.querySelector('[data-billing-field="' + key + '"]');
          var ok = el ? bFilled(el) : false;
          li.classList.toggle('is-done', ok);
          var tick = li.querySelector('.bl-tick');
          if (tick && li.classList.contains('is-done')) {
            tick.classList.add('ok');
            tick.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m5 13 4 4L19 7"/></svg>';
          }
        });

        /* And mark each field green once it carries something, so the
         * form itself reads as progress rather than a wall of boxes. */
        bTracked.forEach(function (wrap) {
          var input = wrap.querySelector('.input, .select');
          wrap.classList.toggle('is-done', !!input && bFilled(input));
        });

        bRefreshPhone();
      }

      billingForm.addEventListener('input', bRefresh);
      billingForm.addEventListener('change', bRefresh);
      billingForm.addEventListener('blur', bRefresh, true);

      var payerRadios = billingForm.querySelectorAll('input[name="bill_payer_type"]');
      Array.prototype.forEach.call(payerRadios, function (r) {
        r.addEventListener('change', bApplyPayerType);
      });

      bApplyPayerType();
    }
  });
})();