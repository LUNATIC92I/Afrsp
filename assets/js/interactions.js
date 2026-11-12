/* =====================================================================
   AFRICA'SPY — interactions.js
   Cartes holographiques, centre de commandement, fenêtres modales,
   Afrique holographique, radar, catalogue filtrable (AJAX), formulaires AJAX.
   ===================================================================== */
(function () {
  'use strict';
  var doc = document;
  var A = window.AFSPY || {};
  var reduced = !!A.reduced;
  var fine = !!A.finePointer;
  var readJSON = function (id) { var el = doc.getElementById(id); try { return el ? JSON.parse(el.textContent) : null; } catch (e) { return null; } };

  /* ------------------------------------------------------------------
     Cartes holographiques : inclinaison + reflet suivant le pointeur
     ------------------------------------------------------------------ */
  function bindTilt(scope) {
    if (!fine || reduced) return;
    (scope || doc).querySelectorAll('[data-tilt]').forEach(function (card) {
      if (card._tilt) return;
      card._tilt = true;
      var raf = null;
      card.addEventListener('pointermove', function (e) {
        var r = card.getBoundingClientRect();
        var px = (e.clientX - r.left) / r.width, py = (e.clientY - r.top) / r.height;
        if (raf) cancelAnimationFrame(raf);
        raf = requestAnimationFrame(function () {
          card.style.setProperty('--mx', (px * 100).toFixed(1) + '%');
          card.style.setProperty('--my', (py * 100).toFixed(1) + '%');
          card.style.setProperty('--ry', ((px - .5) * 8).toFixed(2) + 'deg');
          card.style.setProperty('--rx', ((.5 - py) * 7).toFixed(2) + 'deg');
        });
      });
      card.addEventListener('pointerleave', function () {
        card.style.setProperty('--rx', '0deg'); card.style.setProperty('--ry', '0deg');
      });
    });
  }
  bindTilt();

  /* ------------------------------------------------------------------
     Fenêtres modales accessibles (focus piégé, Échap, retour du focus)
     ------------------------------------------------------------------ */
  var lastFocus = null;
  function openModal(m) {
    if (!m) return;
    lastFocus = doc.activeElement;
    m.hidden = false;
    doc.body.style.overflow = 'hidden';
    var f = m.querySelector('button, a, input, [tabindex]');
    if (f) f.focus();
  }
  function closeModal(m) {
    m.hidden = true;
    doc.body.style.overflow = '';
    if (lastFocus) lastFocus.focus();
  }
  A.openModal = openModal;
  doc.addEventListener('click', function (e) {
    var o = e.target.closest('[data-modal-open]');
    if (o) { e.preventDefault(); openModal(doc.getElementById(o.dataset.modalOpen)); return; }
    var c = e.target.closest('[data-modal-close]');
    if (c) { closeModal(c.closest('.modal')); return; }
    if (e.target.classList && e.target.classList.contains('modal')) closeModal(e.target);
  });
  doc.addEventListener('keydown', function (e) {
    var m = doc.querySelector('.modal:not([hidden])');
    if (!m) return;
    if (e.key === 'Escape') closeModal(m);
    if (e.key === 'Tab') {
      var f = m.querySelectorAll('a[href], button:not([disabled]), input, select, textarea, [tabindex]:not([tabindex="-1"])');
      if (!f.length) return;
      var first = f[0], last = f[f.length - 1];
      if (e.shiftKey && doc.activeElement === first) { e.preventDefault(); last.focus(); }
      else if (!e.shiftKey && doc.activeElement === last) { e.preventDefault(); first.focus(); }
    }
  });

  /* ------------------------------------------------------------------
     Centre de commandement : onglets (ARIA, flèches clavier)
     ------------------------------------------------------------------ */
  doc.querySelectorAll('[data-command]').forEach(function (cmd) {
    var tabs = Array.prototype.slice.call(cmd.querySelectorAll('[role="tab"]'));
    var current = cmd.querySelector('[data-cmd-current]');
    function select(tab) {
      tabs.forEach(function (t) {
        var on = t === tab;
        t.setAttribute('aria-selected', String(on));
        t.tabIndex = on ? 0 : -1;
        var p = doc.getElementById(t.getAttribute('aria-controls'));
        if (!p) return;
        p.hidden = !on;
        if (on && !reduced && p.animate) {
          p.animate([{ opacity: 0, transform: 'translateX(24px)', filter: 'blur(4px)' }, { opacity: 1, transform: 'none', filter: 'blur(0)' }], { duration: 520, easing: 'cubic-bezier(.22,.8,.2,1)' });
        }
      });
      if (current) current.textContent = tab.dataset.code;
    }
    tabs.forEach(function (t, i) {
      t.addEventListener('click', function () { select(t); });
      t.addEventListener('keydown', function (e) {
        var k = e.key, n = null;
        if (k === 'ArrowDown' || k === 'ArrowRight') n = tabs[(i + 1) % tabs.length];
        if (k === 'ArrowUp' || k === 'ArrowLeft') n = tabs[(i - 1 + tabs.length) % tabs.length];
        if (k === 'Home') n = tabs[0];
        if (k === 'End') n = tabs[tabs.length - 1];
        if (n) { e.preventDefault(); n.focus(); select(n); }
      });
    });
  });

  /* ------------------------------------------------------------------
     Afrique holographique : perspective au pointeur + fiches de zones
     ------------------------------------------------------------------ */
  var stage = doc.querySelector('[data-holomap]');
  if (stage) {
    var tilt = stage.querySelector('.holomap__tilt');
    var zones = readJSON('zones-data') || [];
    var card = doc.querySelector('[data-zone-card]');
    if (fine && !reduced) {
      stage.addEventListener('pointermove', function (e) {
        var r = stage.getBoundingClientRect();
        var x = (e.clientX - r.left) / r.width - .5, y = (e.clientY - r.top) / r.height - .5;
        tilt.style.transform = 'rotateX(' + (-y * 12).toFixed(2) + 'deg) rotateY(' + (x * 14).toFixed(2) + 'deg)';
      });
      stage.addEventListener('pointerleave', function () { tilt.style.transform = ''; });
    }
    function showZone(id) {
      var z = zones.filter(function (q) { return q.id === id; })[0];
      if (!z || !card) return;
      stage.querySelectorAll('.zone').forEach(function (g) { g.classList.toggle('is-active', g.dataset.zone === id); });
      var set = function (k, v) { var el = card.querySelector('[data-f="' + k + '"]'); if (el) el.textContent = v; };
      set('code', 'ZONE D\'ÉTUDE · ' + z.id.toUpperCase());
      set('name', z.name); set('theme', z.theme); set('issues', z.issues); set('domain', z.domain);
      var link = card.querySelector('[data-f="link"]');
      if (link) { link.href = z.url; link.textContent = 'Formation associée'; }
      if (!reduced && card.animate) card.animate([{ opacity: .2, clipPath: 'inset(0 0 100% 0)' }, { opacity: 1, clipPath: 'inset(0 0 0 0)' }], { duration: 600, easing: 'cubic-bezier(.7,0,.2,1)' });
    }
    stage.querySelectorAll('.zone').forEach(function (g) {
      g.addEventListener('click', function () { showZone(g.dataset.zone); });
      g.addEventListener('keydown', function (e) { if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); showZone(g.dataset.zone); } });
    });
    if (zones.length) showZone(zones[1] ? zones[1].id : zones[0].id);
  }

  /* ------------------------------------------------------------------
     Radar de renseignement (Canvas) — purement décoratif et pédagogique
     ------------------------------------------------------------------ */
  doc.querySelectorAll('[data-radar]').forEach(function (box) {
    var canvas = box.querySelector('canvas'), tip = box.querySelector('.radar__tip');
    var ctx = canvas.getContext('2d');
    var items = readJSON('radar-data') || [];
    var dpr = Math.min(window.devicePixelRatio || 1, 2), S = 0, R = 0;
    var blips = items.map(function (it, i) {
      var ang = (i / items.length) * Math.PI * 2 + .4, rad = .3 + ((i * 37) % 60) / 100;
      return { a: ang, r: rad, it: it, glow: 0 };
    });
    var angle = 0, running = false, visible = true, last = 0, hover = null;
    function size() {
      var w = box.clientWidth; S = w; R = w / 2 - 6;
      canvas.width = w * dpr; canvas.height = w * dpr; canvas.style.width = w + 'px'; canvas.style.height = w + 'px';
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    }
    function draw(ts) {
      var dt = last ? Math.min(50, ts - last) : 16; last = ts;
      if (!reduced) angle = (angle + dt * 0.0012) % (Math.PI * 2);
      var c = S / 2;
      ctx.clearRect(0, 0, S, S);
      ctx.strokeStyle = 'rgba(0,200,255,.18)'; ctx.lineWidth = 1;
      for (var k = 1; k <= 4; k++) { ctx.beginPath(); ctx.arc(c, c, R * k / 4, 0, Math.PI * 2); ctx.stroke(); }
      ctx.beginPath(); ctx.moveTo(c - R, c); ctx.lineTo(c + R, c); ctx.moveTo(c, c - R); ctx.lineTo(c, c + R); ctx.stroke();
      for (var t = 0; t < 72; t++) {
        var ta = t / 72 * Math.PI * 2, l = t % 6 ? 4 : 9;
        ctx.beginPath(); ctx.moveTo(c + Math.cos(ta) * R, c + Math.sin(ta) * R); ctx.lineTo(c + Math.cos(ta) * (R - l), c + Math.sin(ta) * (R - l)); ctx.stroke();
      }
      // Faisceau
      var grad = ctx.createConicGradient ? ctx.createConicGradient(angle - .9, c, c) : null;
      if (grad) {
        grad.addColorStop(0, 'rgba(0,240,208,0)'); grad.addColorStop(.14, 'rgba(0,240,208,.28)'); grad.addColorStop(.1435, 'rgba(0,240,208,.0)');
        ctx.fillStyle = grad; ctx.beginPath(); ctx.arc(c, c, R, 0, Math.PI * 2); ctx.fill();
      }
      ctx.strokeStyle = 'rgba(0,240,208,.85)'; ctx.lineWidth = 1.5;
      ctx.beginPath(); ctx.moveTo(c, c); ctx.lineTo(c + Math.cos(angle) * R, c + Math.sin(angle) * R); ctx.stroke();
      // Points : s'illuminent au passage du faisceau
      blips.forEach(function (b) {
        var diff = (angle - b.a + Math.PI * 4) % (Math.PI * 2);
        if (diff < .08) b.glow = 1;
        b.glow = reduced ? .8 : Math.max(.15, b.glow - dt * 0.0006);
        var x = c + Math.cos(b.a) * R * b.r, y = c + Math.sin(b.a) * R * b.r;
        b.x = x; b.y = y;
        ctx.beginPath(); ctx.arc(x, y, hover === b ? 6 : 4, 0, Math.PI * 2);
        ctx.fillStyle = hover === b ? 'rgba(213,179,106,1)' : 'rgba(26,255,156,' + b.glow.toFixed(2) + ')';
        ctx.shadowColor = '#1AFF9C'; ctx.shadowBlur = 12 * b.glow; ctx.fill(); ctx.shadowBlur = 0;
      });
      if (running) requestAnimationFrame(draw);
    }
    function start() { if (!running && visible && !doc.hidden) { running = !reduced; requestAnimationFrame(draw); } }
    size();
    window.addEventListener('resize', function () { size(); if (!running) requestAnimationFrame(draw); });
    if ('IntersectionObserver' in window) new IntersectionObserver(function (en) { visible = en[0].isIntersecting; if (visible) start(); else running = false; }).observe(box);
    else start();
    doc.addEventListener('visibilitychange', function () { if (doc.hidden) running = false; else start(); });
    box.addEventListener('pointermove', function (e) {
      var r = canvas.getBoundingClientRect(), mx = e.clientX - r.left, my = e.clientY - r.top;
      hover = null;
      blips.forEach(function (b) { if (Math.hypot(b.x - mx, b.y - my) < 14) hover = b; });
      if (hover) {
        tip.innerHTML = '<b></b><span></span>';
        tip.children[0].textContent = hover.it.t; tip.children[1].textContent = hover.it.d;
        tip.style.left = hover.x + 'px'; tip.style.top = hover.y + 'px';
        tip.classList.add('is-on');
      } else tip.classList.remove('is-on');
      if (!running) requestAnimationFrame(draw);
    });
    box.addEventListener('pointerleave', function () { hover = null; tip.classList.remove('is-on'); });
  });

  /* ------------------------------------------------------------------
     Catalogue : filtres + recherche en AJAX (mise à jour de l'URL)
     ------------------------------------------------------------------ */
  var catalog = doc.querySelector('[data-catalog]');
  if (catalog) {
    var results = doc.getElementById('catalog-results');
    var status = doc.querySelector('[data-catalog-status]');
    var form = catalog;
    var state = { domaine: form.dataset.domaine || '', niveau: '', q: '' };
    var levelSel = form.querySelector('[name="niveau"]'), qInput = form.querySelector('[name="q"]');
    state.niveau = levelSel ? levelSel.value : ''; state.q = qInput ? qInput.value : '';
    var timer = null, ctrl = null;
    function load() {
      var params = new URLSearchParams();
      Object.keys(state).forEach(function (k) { if (state[k]) params.set(k, state[k]); });
      history.replaceState(null, '', location.pathname + (params.toString() ? '?' + params : ''));
      results.setAttribute('aria-busy', 'true');
      results.style.opacity = '.45';
      if (status) status.textContent = 'Recherche en cours…';
      if (ctrl) ctrl.abort();
      ctrl = ('AbortController' in window) ? new AbortController() : null;
      fetch(A.url('ajax/courses.php') + '?' + params.toString(), { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }, signal: ctrl ? ctrl.signal : undefined, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (j) {
          results.innerHTML = j.html;
          if (status) status.textContent = j.label;
          results.removeAttribute('aria-busy');
          results.style.opacity = '';
          if (A.observe) A.observe(results);
          bindTilt(results);
          results.querySelectorAll('.progress > span[data-p]').forEach(function (b) { b.style.setProperty('--p', b.dataset.p + '%'); });
        })
        .catch(function (e) { if (e.name !== 'AbortError') { results.style.opacity = ''; if (A.toast) A.toast('error', 'Impossible de charger le catalogue.'); } });
    }
    form.addEventListener('submit', function (e) { e.preventDefault(); state.q = qInput.value.trim(); load(); });
    form.querySelectorAll('[data-domain]').forEach(function (chip) {
      chip.addEventListener('click', function () {
        state.domaine = chip.dataset.domain;
        form.querySelectorAll('[data-domain]').forEach(function (c) { c.setAttribute('aria-pressed', String(c === chip)); });
        load();
      });
    });
    if (levelSel) levelSel.addEventListener('change', function () { state.niveau = levelSel.value; load(); });
    if (qInput) qInput.addEventListener('input', function () { clearTimeout(timer); timer = setTimeout(function () { state.q = qInput.value.trim(); load(); }, 320); });
  }

  /* ------------------------------------------------------------------
     Index du dossier institutionnel (section active)
     ------------------------------------------------------------------ */
  var idx = doc.querySelector('.dossier__index');
  if (idx && 'IntersectionObserver' in window) {
    var links = idx.querySelectorAll('a');
    var io = new IntersectionObserver(function (en) {
      en.forEach(function (x) {
        if (!x.isIntersecting) return;
        links.forEach(function (l) { l.classList.toggle('is-active', l.getAttribute('href') === '#' + x.target.id); });
      });
    }, { rootMargin: '-40% 0px -55% 0px' });
    doc.querySelectorAll('.dossier__section[id]').forEach(function (s) { io.observe(s); });
  }

  /* ------------------------------------------------------------------
     Formulaires AJAX génériques (data-ajax-form)
     ------------------------------------------------------------------ */
  doc.querySelectorAll('form[data-ajax-form]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = f.querySelector('[type="submit"]');
      if (btn) btn.classList.add('is-loading');
      f.querySelectorAll('.err').forEach(function (x) { x.remove(); });
      f.querySelectorAll('[aria-invalid]').forEach(function (x) { x.removeAttribute('aria-invalid'); });
      A.request(f.action, { body: new FormData(f) }).then(function (j) {
        if (btn) btn.classList.remove('is-loading');
        if (j.ok) { A.toast('success', j.message); f.reset(); return; }
        A.toast('error', j.message || 'Le formulaire contient des erreurs.');
        if (j.errors) Object.keys(j.errors).forEach(function (k) {
          var input = f.querySelector('[name="' + k + '"]');
          if (!input) return;
          input.setAttribute('aria-invalid', 'true');
          var s = doc.createElement('span'); s.className = 'err'; s.textContent = j.errors[k];
          input.parentNode.appendChild(s);
        });
      });
    });
  });

  /* Confirmation des actions sensibles */
  doc.addEventListener('submit', function (e) {
    var f = e.target;
    if (f.dataset && f.dataset.confirm && !window.confirm(f.dataset.confirm)) e.preventDefault();
  }, true);
})();
