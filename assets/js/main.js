/* =====================================================================
   AFRICA'SPY : main.js
   Socle : transitions de pages, en-tête, révélations, compteurs,
   particules (Canvas), parallaxe, séquence d'ouverture, utilitaires AJAX.
   ===================================================================== */
(function () {
  'use strict';

  var doc = document;
  var root = doc.documentElement;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  var meta = function (n) { var m = doc.querySelector('meta[name="' + n + '"]'); return m ? m.content : ''; };

  /* ------------------------------------------------------------------
     API globale minimale
     ------------------------------------------------------------------ */
  var AFSPY = window.AFSPY = {
    reduced: reduced,
    finePointer: finePointer,
    base: meta('base-path'),
    url: function (p) { return AFSPY.base + '/' + String(p).replace(/^\//, ''); },

    /** Requête AJAX JSON protégée par jeton CSRF. */
    request: function (url, opts) {
      opts = opts || {};
      var headers = { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json', 'X-CSRF-Token': meta('csrf-token') };
      var body = opts.body;
      if (body && !(body instanceof FormData)) {
        var fd = new FormData();
        Object.keys(body).forEach(function (k) { fd.append(k, body[k]); });
        body = fd;
      }
      return fetch(url, { method: opts.method || (body ? 'POST' : 'GET'), headers: headers, body: body, credentials: 'same-origin' })
        .then(function (r) {
          return r.json().catch(function () { return { ok: false, message: 'Réponse inattendue du serveur (' + r.status + ').' }; })
            .then(function (j) { j._status = r.status; return j; });
        })
        .catch(function () { return { ok: false, message: 'Connexion au serveur impossible. Vérifiez votre réseau.' }; });
    },

    /** Notification élégante. */
    toast: function (type, msg, ttl) {
      var box = doc.querySelector('.toasts');
      if (!box) return;
      var tags = { success: 'OK', error: 'ERR', warning: 'ALERTE', info: 'INFO' };
      var t = doc.createElement('div');
      t.className = 'toast toast--' + (tags[type] ? type : 'info');
      t.innerHTML = '<span class="toast__tag"></span><span></span><button class="toast__close" type="button" aria-label="Fermer">×</button>';
      t.children[0].textContent = tags[type] || 'INFO';
      t.children[1].textContent = msg;
      box.appendChild(t);
      armToast(t, ttl || 6000);
    },

    observe: null
  };

  function armToast(t, ttl) {
    var close = function () {
      if (t.classList.contains('is-leaving')) return;
      t.classList.add('is-leaving');
      setTimeout(function () { t.remove(); }, 380);
    };
    var btn = t.querySelector('.toast__close');
    if (btn) btn.addEventListener('click', close);
    var tm = setTimeout(close, ttl);
    t.addEventListener('mouseenter', function () { clearTimeout(tm); });
    t.addEventListener('mouseleave', function () { tm = setTimeout(close, 2500); });
  }
  doc.querySelectorAll('.toast').forEach(function (t) { armToast(t, t.classList.contains('toast--error') ? 9000 : 6000); });

  /* ------------------------------------------------------------------
     Transitions entre pages (voile géométrique)
     ------------------------------------------------------------------ */
  requestAnimationFrame(function () { doc.body.classList.add('is-loaded'); });
  window.addEventListener('pageshow', function (e) { if (e.persisted) doc.body.classList.remove('is-leaving'); });
  if (!reduced) {
    doc.addEventListener('click', function (e) {
      var a = e.target.closest('a[href]');
      if (!a || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
      if (a.target && a.target !== '_self') return;
      if (a.hasAttribute('download') || a.dataset.noTransition !== undefined) return;
      var href = a.getAttribute('href');
      if (!href || href.charAt(0) === '#' || /^(mailto|tel|javascript):/i.test(href)) return;
      var u = new URL(a.href, location.href);
      if (u.origin !== location.origin) return;
      if (u.pathname === location.pathname && u.search === location.search && u.hash) return;
      if (/\.(pdf|zip|mp4|webm)$/i.test(u.pathname) || /download\.php|certificate-pdf\.php|support-pdf\.php/.test(u.pathname)) return;
      e.preventDefault();
      doc.body.classList.add('is-leaving');
      setTimeout(function () { location.href = a.href; }, 360);
    });
  }

  /* ------------------------------------------------------------------
     En-tête : mode compact au défilement + menu mobile
     ------------------------------------------------------------------ */
  var header = doc.querySelector('.site-header');
  var toggle = doc.querySelector('.nav-toggle');
  var onScrollHeader = function () { if (header) header.classList.toggle('is-compact', window.scrollY > 40); };
  onScrollHeader();
  if (toggle) {
    toggle.addEventListener('click', function () {
      var open = !doc.body.classList.contains('nav-open');
      doc.body.classList.toggle('nav-open', open);
      toggle.setAttribute('aria-expanded', String(open));
      toggle.setAttribute('aria-label', open ? 'Fermer le menu' : 'Ouvrir le menu');
      doc.body.style.overflow = open ? 'hidden' : '';
    });
    doc.addEventListener('keydown', function (e) { if (e.key === 'Escape' && doc.body.classList.contains('nav-open')) toggle.click(); });
  }

  /* ------------------------------------------------------------------
     Parallaxe légère (transform uniquement, rAF)
     ------------------------------------------------------------------ */
  var parallax = reduced ? [] : Array.prototype.slice.call(doc.querySelectorAll('[data-parallax]'));
  var ticking = false;
  function onScroll() {
    if (ticking) return;
    ticking = true;
    requestAnimationFrame(function () {
      onScrollHeader();
      var vh = window.innerHeight;
      parallax.forEach(function (el) {
        var r = el.parentElement.getBoundingClientRect();
        if (r.bottom < -200 || r.top > vh + 200) return;
        var speed = parseFloat(el.dataset.parallax) || 0.2;
        var offset = (r.top + r.height / 2 - vh / 2) * -speed;
        el.style.transform = 'translate3d(0,' + offset.toFixed(1) + 'px,0)';
      });
      ticking = false;
    });
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ------------------------------------------------------------------
     Découpage des titres en lettres (data-split)
     ------------------------------------------------------------------ */
  doc.querySelectorAll('[data-split]').forEach(function (el) {
    if (reduced) return;
    var text = el.textContent;
    el.setAttribute('aria-label', text.trim());
    el.textContent = '';
    var i = 0;
    text.split(/(\s+)/).forEach(function (word) {
      if (/^\s+$/.test(word)) { el.appendChild(doc.createTextNode(' ')); return; }
      var w = doc.createElement('span');
      w.style.display = 'inline-block';
      w.setAttribute('aria-hidden', 'true');
      word.split('').forEach(function (ch) {
        var s = doc.createElement('span');
        s.className = 'split-char';
        s.textContent = ch;
        s.style.transitionDelay = (i++ * 28) + 'ms';
        w.appendChild(s);
      });
      el.appendChild(w);
    });
  });

  /* ------------------------------------------------------------------
     Révélations au défilement (IntersectionObserver)
     ------------------------------------------------------------------ */
  doc.querySelectorAll('[data-stagger]').forEach(function (g) {
    var step = parseInt(g.dataset.stagger, 10) || 90;
    Array.prototype.forEach.call(g.children, function (c, i) { c.style.transitionDelay = (i * step) + 'ms'; });
  });
  var revealSel = '.reveal, [data-stagger], .img-reveal, .scanner, [data-split], [data-count]';
  var io = ('IntersectionObserver' in window) ? new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (!en.isIntersecting) return;
      var el = en.target;
      el.classList.add('is-visible');
      if (el.hasAttribute('data-count')) countUp(el);
      io.unobserve(el);
    });
  }, { rootMargin: '0px 0px -8% 0px', threshold: 0.12 }) : null;
  AFSPY.observe = function (scope) {
    (scope || doc).querySelectorAll(revealSel).forEach(function (el) {
      if (el.classList.contains('is-visible')) return;
      if (io && !reduced) io.observe(el);
      else { el.classList.add('is-visible'); if (el.hasAttribute('data-count')) countUp(el); }
    });
  };

  /* Compteurs animés */
  function countUp(el) {
    var target = parseFloat(el.dataset.count) || 0;
    var dec = (String(el.dataset.count).split('.')[1] || '').length;
    if (reduced || target === 0) { el.textContent = target.toLocaleString('fr-FR', { minimumFractionDigits: dec }); return; }
    var dur = 1600, t0 = null;
    function frame(ts) {
      if (!t0) t0 = ts;
      var p = Math.min(1, (ts - t0) / dur);
      var eased = 1 - Math.pow(1 - p, 4);
      el.textContent = (target * eased).toLocaleString('fr-FR', { minimumFractionDigits: dec, maximumFractionDigits: dec });
      if (p < 1) requestAnimationFrame(frame);
    }
    requestAnimationFrame(frame);
  }

  /* Barres de progression : animation à l'apparition */
  doc.querySelectorAll('.progress > span[data-p]').forEach(function (bar) {
    var set = function () { bar.style.setProperty('--p', bar.dataset.p + '%'); };
    if (!io || reduced) { set(); return; }
    var o = new IntersectionObserver(function (en) { if (en[0].isIntersecting) { set(); o.disconnect(); } });
    o.observe(bar);
  });

  /* ------------------------------------------------------------------
     Horloge UTC + coordonnées (données techniques réelles, pas simulées)
     ------------------------------------------------------------------ */
  var clocks = doc.querySelectorAll('[data-utc-clock]');
  if (clocks.length) {
    var tick = function () {
      var d = new Date();
      var s = 'UTC ' + d.toISOString().substr(11, 8);
      clocks.forEach(function (c) { c.textContent = s; });
    };
    tick(); setInterval(tick, 1000);
  }

  /* ------------------------------------------------------------------
     Indicateur de robustesse du mot de passe
     ------------------------------------------------------------------ */
  doc.querySelectorAll('[data-pw-meter]').forEach(function (meter) {
    var input = doc.getElementById(meter.dataset.pwMeter);
    var bar = meter.querySelector('span');
    if (!input || !bar) return;
    input.addEventListener('input', function () {
      var v = input.value, s = 0;
      if (v.length >= 10) s++;
      if (v.length >= 14) s++;
      if (/[a-zà-ÿ]/.test(v) && /[A-ZÀ-Ý]/.test(v)) s++;
      if (/\d/.test(v)) s++;
      if (/[^\w\s]/.test(v)) s++;
      var colors = ['#FF4057', '#FF4057', '#D5B36A', '#D5B36A', '#00C8FF', '#1AFF9C'];
      bar.style.width = (s / 5 * 100) + '%';
      bar.style.background = colors[s];
    });
  });

  /* ------------------------------------------------------------------
     Curseur d'appoint (anneau), sans masquer le curseur natif
     ------------------------------------------------------------------ */
  if (finePointer && !reduced && !doc.body.classList.contains('app')) {
    var ring = doc.createElement('div');
    ring.className = 'cursor-ring';
    ring.setAttribute('aria-hidden', 'true');
    doc.body.appendChild(ring);
    var cx = 0, cy = 0, rx = 0, ry = 0, raf = null;
    var loop = function () {
      rx += (cx - rx) * 0.22; ry += (cy - ry) * 0.22;
      ring.style.transform = 'translate3d(' + rx + 'px,' + ry + 'px,0)';
      raf = (Math.abs(cx - rx) + Math.abs(cy - ry) > 0.3) ? requestAnimationFrame(loop) : null;
    };
    doc.addEventListener('pointermove', function (e) {
      cx = e.clientX; cy = e.clientY;
      ring.classList.add('is-on');
      if (!raf) raf = requestAnimationFrame(loop);
    }, { passive: true });
    doc.addEventListener('pointerleave', function () { ring.classList.remove('is-on'); });
    doc.addEventListener('pointerover', function (e) {
      ring.classList.toggle('is-hover', !!e.target.closest('a, button, [role="tab"], .zone, input, select, textarea, summary'));
    });
  }

  /* ------------------------------------------------------------------
     Particules numériques (Canvas) : réseau réactif au curseur
     ------------------------------------------------------------------ */
  function particles(canvas) {
    var ctx = canvas.getContext('2d');
    if (!ctx) return;
    var dpr = Math.min(window.devicePixelRatio || 1, 2);
    var W = 0, H = 0, pts = [], mouse = { x: -9999, y: -9999 }, running = false, visible = true, rafId = null;
    var mobile = window.innerWidth < 760;
    function resize() {
      var r = canvas.getBoundingClientRect();
      W = r.width; H = r.height;
      canvas.width = W * dpr; canvas.height = H * dpr;
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      var n = Math.round(Math.min(mobile ? 34 : 90, (W * H) / (mobile ? 16000 : 14000)));
      pts = [];
      for (var i = 0; i < n; i++) {
        pts.push({ x: Math.random() * W, y: Math.random() * H, vx: (Math.random() - .5) * .25, vy: (Math.random() - .5) * .25, r: Math.random() * 1.4 + .4, g: Math.random() < .12 });
      }
    }
    function draw() {
      ctx.clearRect(0, 0, W, H);
      var linkDist = mobile ? 0 : 150, i, j, p, q, dx, dy, d;
      for (i = 0; i < pts.length; i++) {
        p = pts[i];
        p.x += p.vx; p.y += p.vy;
        if (p.x < 0 || p.x > W) p.vx *= -1;
        if (p.y < 0 || p.y > H) p.vy *= -1;
        dx = p.x - mouse.x; dy = p.y - mouse.y; d = Math.sqrt(dx * dx + dy * dy);
        var near = d < 180;
        ctx.beginPath();
        ctx.arc(p.x, p.y, p.r + (near ? 1 : 0), 0, Math.PI * 2);
        ctx.fillStyle = p.g ? 'rgba(213,179,106,.8)' : (near ? 'rgba(0,240,208,.95)' : 'rgba(0,200,255,.5)');
        ctx.fill();
        // Connexions uniquement à proximité du curseur : le réseau « s'allume »
        if (near && linkDist) {
          for (j = i + 1; j < pts.length; j++) {
            q = pts[j];
            var ex = p.x - q.x, ey = p.y - q.y, e2 = Math.sqrt(ex * ex + ey * ey);
            if (e2 < linkDist) {
              ctx.strokeStyle = 'rgba(0,240,208,' + (0.42 * (1 - e2 / linkDist) * (1 - d / 180)).toFixed(3) + ')';
              ctx.lineWidth = .8;
              ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(q.x, q.y); ctx.stroke();
            }
          }
          ctx.strokeStyle = 'rgba(0,200,255,' + (0.35 * (1 - d / 180)).toFixed(3) + ')';
          ctx.beginPath(); ctx.moveTo(p.x, p.y); ctx.lineTo(mouse.x, mouse.y); ctx.stroke();
        }
      }
      rafId = running ? requestAnimationFrame(draw) : null;
    }
    function start() { if (!running && visible && !doc.hidden) { running = true; rafId = requestAnimationFrame(draw); } }
    function stop() { running = false; if (rafId) cancelAnimationFrame(rafId); rafId = null; }
    resize();
    var host = canvas.parentElement;
    host.addEventListener('pointermove', function (e) { var r = canvas.getBoundingClientRect(); mouse.x = e.clientX - r.left; mouse.y = e.clientY - r.top; }, { passive: true });
    host.addEventListener('pointerleave', function () { mouse.x = mouse.y = -9999; });
    var rt; window.addEventListener('resize', function () { clearTimeout(rt); rt = setTimeout(function () { mobile = window.innerWidth < 760; resize(); }, 200); });
    doc.addEventListener('visibilitychange', function () { doc.hidden ? stop() : start(); });
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (en) { visible = en[0].isIntersecting; visible ? start() : stop(); }).observe(canvas);
    }
    if (reduced) { running = false; draw(); } else start();
  }
  doc.querySelectorAll('canvas[data-particles]').forEach(particles);

  /* ------------------------------------------------------------------
     Séquence d'ouverture (une fois par session)
     ------------------------------------------------------------------ */
  var intro = doc.getElementById('intro');
  if (intro) {
    var seen = false;
    try { seen = sessionStorage.getItem('afspy-intro') === '1'; } catch (e) {}
    if (seen || reduced) {
      intro.remove();
      doc.body.classList.add('intro-skipped');
    } else {
      intro.hidden = false;
      doc.body.style.overflow = 'hidden';
      doc.body.classList.add('intro-running');
      var finish = function () {
        if (intro.classList.contains('is-done')) return;
        try { sessionStorage.setItem('afspy-intro', '1'); } catch (e) {}
        intro.classList.add('is-done');
        doc.body.style.overflow = '';
        doc.body.classList.remove('intro-running');
        setTimeout(function () { intro.remove(); }, 900);
      };
      var timer = setTimeout(finish, 3300);
      var skip = intro.querySelector('.intro__skip');
      if (skip) { skip.addEventListener('click', function () { clearTimeout(timer); finish(); }); skip.focus({ preventScroll: true }); }
      doc.addEventListener('keydown', function esc(e) { if (e.key === 'Escape') { clearTimeout(timer); finish(); doc.removeEventListener('keydown', esc); } });
    }
  }

  AFSPY.observe();
})();
