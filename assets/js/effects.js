/* =====================================================================
   AFRICA'SPY — effects.js
   Animations complémentaires : déchiffrement de texte, boutons
   magnétiques, tracé SVG, barre de progression de lecture, projecteur,
   compte à rebours de l'offre, suivi automatique d'un paiement.
   Toutes respectent prefers-reduced-motion.
   ===================================================================== */
(function () {
  'use strict';
  var doc = document, A = window.AFSPY || {};
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var fine = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  /* Barre de progression de lecture */
  if (!reduced && !doc.body.classList.contains('app')) {
    var bar = doc.createElement('div');
    bar.className = 'scroll-progress';
    bar.setAttribute('aria-hidden', 'true');
    doc.body.appendChild(bar);
    var tick = false;
    window.addEventListener('scroll', function () {
      if (tick) return; tick = true;
      requestAnimationFrame(function () {
        var h = doc.documentElement.scrollHeight - window.innerHeight;
        bar.style.transform = 'scaleX(' + (h > 0 ? Math.min(1, window.scrollY / h) : 0) + ')';
        tick = false;
      });
    }, { passive: true });
  }

  /* Texte déchiffré : les caractères défilent avant de se stabiliser */
  var glyphs = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789#/<>*%$';
  function decrypt(el) {
    if (el._dec) return; el._dec = true;
    var target = el.textContent, len = target.length, frame = 0, total = Math.min(36, 12 + len);
    el.setAttribute('aria-label', target.trim());
    el.classList.add('is-decrypting');
    (function step() {
      var out = '';
      for (var i = 0; i < len; i++) {
        var ch = target[i];
        out += (ch === ' ' || i < (frame / total) * len) ? ch : glyphs[(Math.random() * glyphs.length) | 0];
      }
      el.textContent = out;
      if (++frame <= total) requestAnimationFrame(step);
      else { el.textContent = target; el.classList.remove('is-decrypting'); }
    })();
  }

  /* Tracé progressif des chemins SVG */
  function draw(svg) {
    svg.querySelectorAll('path').forEach(function (p) {
      try { p.style.setProperty('--len', Math.ceil(p.getTotalLength()) + 1); } catch (e) {}
    });
    requestAnimationFrame(function () { requestAnimationFrame(function () { svg.classList.add('is-drawn'); }); });
  }

  if ('IntersectionObserver' in window && !reduced) {
    var io = new IntersectionObserver(function (en) {
      en.forEach(function (x) {
        if (!x.isIntersecting) return;
        if (x.target.hasAttribute('data-decrypt')) decrypt(x.target);
        if (x.target.hasAttribute('data-draw')) draw(x.target);
        io.unobserve(x.target);
      });
    }, { threshold: 0.3 });
    doc.querySelectorAll('[data-decrypt], [data-draw]').forEach(function (el) { io.observe(el); });
  } else {
    doc.querySelectorAll('[data-draw]').forEach(function (s) { s.classList.add('is-drawn'); });
  }

  /* Boutons magnétiques (attirés par le pointeur) */
  if (fine && !reduced) {
    doc.querySelectorAll('[data-magnetic]').forEach(function (b) {
      b.addEventListener('pointermove', function (e) {
        var r = b.getBoundingClientRect();
        var x = (e.clientX - r.left - r.width / 2) * 0.18, y = (e.clientY - r.top - r.height / 2) * 0.3;
        b.style.transform = 'translate(' + x.toFixed(1) + 'px,' + y.toFixed(1) + 'px)';
      });
      b.addEventListener('pointerleave', function () { b.style.transform = ''; });
    });
    /* Projecteur suivant le pointeur */
    doc.querySelectorAll('[data-spotlight]').forEach(function (s) {
      s.addEventListener('pointermove', function (e) {
        var r = s.getBoundingClientRect();
        s.style.setProperty('--sx', (e.clientX - r.left) + 'px');
        s.style.setProperty('--sy', (e.clientY - r.top) + 'px');
      }, { passive: true });
    });
  }

  /* Compte à rebours de l'offre (uniquement si une échéance est configurée) */
  doc.querySelectorAll('[data-countdown]').forEach(function (c) {
    var end = parseInt(c.dataset.countdown, 10) * 1000, out = c.querySelector('[data-cd]');
    (function upd() {
      var s = Math.max(0, Math.floor((end - Date.now()) / 1000));
      var d = Math.floor(s / 86400), h = Math.floor(s % 86400 / 3600), m = Math.floor(s % 3600 / 60), sec = s % 60;
      var pad = function (n) { return (n < 10 ? '0' : '') + n; };
      out.textContent = (d ? d + ' j ' : '') + pad(h) + ':' + pad(m) + ':' + pad(sec);
      if (s > 0) setTimeout(upd, 1000); else c.hidden = true;
    })();
  });

  /* Suivi automatique d'un paiement en cours de confirmation */
  var poll = doc.querySelector('[data-pay-poll]');
  if (poll && A.url) {
    var tries = 0;
    (function check() {
      fetch(A.url('ajax/payment-status.php') + '?ref=' + encodeURIComponent(poll.dataset.payPoll), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }, credentials: 'same-origin' })
        .then(function (r) { return r.json(); })
        .then(function (j) { if (j.status && j.status !== 'pending') location.reload(); else if (++tries < 40) setTimeout(check, 5000); })
        .catch(function () { if (++tries < 40) setTimeout(check, 8000); });
    })();
  }

  /* Accès accordé : effet de complétion */
  var granted = doc.querySelector('[data-granted]');
  if (granted && !reduced) {
    var b = doc.createElement('div');
    b.className = 'burst'; b.setAttribute('aria-hidden', 'true');
    b.innerHTML = '<span class="burst__label">ACCÈS AUTORISÉ</span>';
    doc.body.appendChild(b);
    setTimeout(function () { b.remove(); }, 1600);
  }
})();
