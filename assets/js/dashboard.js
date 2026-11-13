/* =====================================================================
   AFRICA'SPY — dashboard.js
   Espace apprenant & administration : menu, notifications, progression,
   QCM, chronomètre d'examen, graphiques Canvas, aides d'édition.
   ===================================================================== */
(function () {
  'use strict';
  var doc = document, A = window.AFSPY || {};
  var reduced = !!A.reduced;

  /* Menu latéral mobile */
  var toggle = doc.querySelector('.side-toggle');
  if (toggle) {
    toggle.addEventListener('click', function () {
      var open = !doc.body.classList.contains('side-open');
      doc.body.classList.toggle('side-open', open);
      toggle.setAttribute('aria-expanded', String(open));
    });
    doc.addEventListener('click', function (e) {
      if (doc.body.classList.contains('side-open') && !e.target.closest('.app-side') && !e.target.closest('.side-toggle')) {
        doc.body.classList.remove('side-open'); toggle.setAttribute('aria-expanded', 'false');
      }
    });
  }

  /* Notifications (annonces) */
  doc.querySelectorAll('[data-notif]').forEach(function (n) {
    var btn = n.querySelector('.notif__btn'), panel = n.querySelector('.notif__panel');
    btn.addEventListener('click', function (e) {
      e.stopPropagation();
      panel.hidden = !panel.hidden;
      btn.setAttribute('aria-expanded', String(!panel.hidden));
    });
    doc.addEventListener('click', function (e) { if (!n.contains(e.target)) { panel.hidden = true; btn.setAttribute('aria-expanded', 'false'); } });
    n.querySelectorAll('[data-ann]').forEach(function (item) {
      item.addEventListener('click', function () {
        if (!item.classList.contains('is-unread')) return;
        A.request(A.url('ajax/announcement.php'), { body: { id: item.dataset.ann } }).then(function (j) {
          if (!j.ok) return;
          item.classList.remove('is-unread');
          var c = Math.max(0, (parseInt(btn.dataset.badge, 10) || 0) - 1);
          btn.dataset.badge = c;
          btn.setAttribute('aria-label', 'Annonces (' + c + ' non lues)');
        });
      });
    });
  });

  /* Animation de complétion */
  function burst(label) {
    if (reduced) return;
    var b = doc.createElement('div');
    b.className = 'burst';
    b.setAttribute('aria-hidden', 'true');
    b.innerHTML = '<span class="burst__label"></span>';
    b.firstChild.textContent = label;
    doc.body.appendChild(b);
    setTimeout(function () { b.remove(); }, 1500);
  }

  /* Leçon terminée (AJAX) */
  doc.querySelectorAll('[data-complete]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      btn.classList.add('is-loading');
      A.request(A.url('ajax/progress.php'), { body: { lesson_id: btn.dataset.complete } }).then(function (j) {
        btn.classList.remove('is-loading');
        if (!j.ok) { A.toast('error', j.message || 'Enregistrement impossible.'); return; }
        btn.hidden = true;
        var lbl = doc.querySelector('[data-done-label]'); if (lbl) lbl.hidden = false;
        var item = doc.querySelector('[data-lesson-item="' + btn.dataset.complete + '"]');
        if (item) { item.classList.add('is-done'); var st = item.querySelector('.st'); if (st) st.textContent = '✓'; }
        var bar = doc.querySelector('[data-course-progress]'); if (bar) bar.style.setProperty('--p', j.progress + '%');
        var pl = doc.querySelector('[data-course-progress-label]'); if (pl) pl.textContent = j.progress;
        burst(j.course_complete ? 'FORMATION COMPLÈTE' : (j.chapter_complete ? 'CHAPITRE VALIDÉ' : 'LEÇON VALIDÉE'));
        A.toast('success', j.message);
      });
    });
  });

  /* QCM formatif de chapitre */
  doc.querySelectorAll('form[data-quiz]').forEach(function (f) {
    f.addEventListener('submit', function (e) {
      e.preventDefault();
      var btn = f.querySelector('[type="submit"]');
      btn.classList.add('is-loading');
      f.querySelectorAll('.q__exp').forEach(function (x) { x.remove(); });
      f.querySelectorAll('.opt').forEach(function (o) { o.classList.remove('is-correct', 'is-wrong'); });
      A.request(f.action, { body: new FormData(f) }).then(function (j) {
        btn.classList.remove('is-loading');
        if (!j.ok) { A.toast('error', j.message); return; }
        j.results.forEach(function (r) {
          var fs = f.querySelector('[data-q="' + r.question + '"]');
          if (!fs) return;
          var good = fs.querySelector('[data-a="' + r.correct + '"]');
          if (good) good.classList.add('is-correct');
          if (!r.ok && r.chosen) { var bad = fs.querySelector('[data-a="' + r.chosen + '"]'); if (bad) bad.classList.add('is-wrong'); }
          if (r.explanation) { var p = doc.createElement('p'); p.className = 'q__exp'; p.textContent = (r.ok ? '✓ ' : '✗ ') + r.explanation; fs.appendChild(p); }
        });
        var res = f.querySelector('[data-quiz-result]');
        res.textContent = j.message;
        res.style.color = j.score === j.total ? 'var(--green)' : 'var(--gold)';
        if (j.score === j.total) burst('ÉVALUATION RÉUSSIE');
      });
    });
  });

  /* Examen : chronomètre, progression, soumission automatique */
  var exam = doc.querySelector('form[data-exam]');
  if (exam) {
    var deadline = Date.now() + (parseInt(exam.dataset.remaining, 10) || 0) * 1000;
    var timer = doc.querySelector('[data-timer]');
    var answered = doc.querySelector('[data-answered]');
    var bar = doc.querySelector('[data-exam-bar]');
    var total = exam.querySelectorAll('fieldset.q').length;
    var submitting = false;
    function count() {
      var n = 0;
      exam.querySelectorAll('fieldset.q').forEach(function (fs) { if (fs.querySelector('input:checked')) n++; });
      if (answered) answered.textContent = n + ' / ' + total;
      if (bar) bar.style.setProperty('--p', (n / total * 100) + '%');
      return n;
    }
    exam.addEventListener('change', count);
    count();
    function tick() {
      var left = Math.max(0, Math.round((deadline - Date.now()) / 1000));
      var m = Math.floor(left / 60), s = left % 60;
      if (timer) { timer.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s; timer.classList.toggle('is-low', left <= 60); }
      if (left === 0 && !submitting) {
        submitting = true;
        A.toast('warning', 'Temps écoulé : vos réponses sont soumises automatiquement.');
        exam.dataset.auto = '1';
        exam.submit();
        return;
      }
      setTimeout(tick, 1000);
    }
    tick();
    exam.addEventListener('submit', function (e) {
      if (exam.dataset.auto) return;
      var n = count();
      var msg = n < total ? 'Vous n\'avez répondu qu\'à ' + n + ' question(s) sur ' + total + '. Les questions sans réponse seront comptées fausses. Soumettre quand même ?' : 'Confirmer la soumission définitive de l\'examen ?';
      if (!window.confirm(msg)) { e.preventDefault(); return; }
      submitting = true;
      window.onbeforeunload = null;
    });
    window.onbeforeunload = function () { if (!submitting) return 'Examen en cours'; };
  }

  /* ------------------------------------------------------------------
     Graphiques Canvas (barres, courbes, anneau) — sans bibliothèque
     ------------------------------------------------------------------ */
  function setupCanvas(cv) {
    var dpr = Math.min(window.devicePixelRatio || 1, 2), r = cv.getBoundingClientRect();
    cv.width = r.width * dpr; cv.height = r.height * dpr;
    var ctx = cv.getContext('2d'); ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    return { ctx: ctx, w: r.width, h: r.height };
  }
  function drawChart(box, t) {
    var cv = box.querySelector('canvas'), data;
    try { data = JSON.parse(box.querySelector('script').textContent); } catch (e) { return; }
    var kind = box.dataset.chart, c = setupCanvas(cv), ctx = c.ctx, W = c.w, H = c.h;
    var pad = { l: 34, r: 10, t: 12, b: 26 };
    var series = kind === 'lines' ? data.series : [{ values: data.map(function (d) { return d.value; }), color: '#00C8FF' }];
    var labels = kind === 'lines' ? data.labels : data.map(function (d) { return d.label; });
    var max = 1;
    series.forEach(function (s) { s.values.forEach(function (v) { if (v > max) max = v; }); });
    max = Math.ceil(max * 1.15);
    ctx.clearRect(0, 0, W, H);
    ctx.font = '10px "Share Tech Mono", monospace'; ctx.fillStyle = '#8592A3'; ctx.strokeStyle = 'rgba(174,187,201,.1)'; ctx.lineWidth = 1;
    for (var g = 0; g <= 4; g++) {
      var y = pad.t + (H - pad.t - pad.b) * g / 4;
      ctx.beginPath(); ctx.moveTo(pad.l, y); ctx.lineTo(W - pad.r, y); ctx.stroke();
      ctx.fillText(String(Math.round(max * (1 - g / 4))), 4, y + 3);
    }
    var n = labels.length, step = (W - pad.l - pad.r) / n;
    var every = Math.ceil(n / Math.max(1, Math.floor((W - pad.l) / 46)));
    labels.forEach(function (l, i) { if (i % every === 0) ctx.fillText(l, pad.l + step * i + step / 2 - 12, H - 8); });
    var ih = H - pad.t - pad.b;
    if (kind === 'bars') {
      series[0].values.forEach(function (v, i) {
        var bh = ih * (v / max) * t, x = pad.l + step * i + step * .22, bw = step * .56, y0 = H - pad.b - bh;
        var grad = ctx.createLinearGradient(0, y0, 0, H - pad.b);
        grad.addColorStop(0, '#00F0D0'); grad.addColorStop(1, 'rgba(8,124,255,.3)');
        ctx.fillStyle = grad; ctx.fillRect(x, y0, bw, bh);
        if (v > 0) { ctx.fillStyle = 'rgba(0,240,208,.9)'; ctx.fillRect(x, y0, bw, 2); }
      });
    } else {
      series.forEach(function (s) {
        ctx.strokeStyle = s.color; ctx.lineWidth = 2; ctx.shadowColor = s.color; ctx.shadowBlur = 8;
        ctx.beginPath();
        s.values.forEach(function (v, i) {
          var x = pad.l + step * i + step / 2, y = H - pad.b - ih * (v / max) * t;
          i ? ctx.lineTo(x, y) : ctx.moveTo(x, y);
        });
        ctx.stroke(); ctx.shadowBlur = 0;
        s.values.forEach(function (v, i) {
          var x = pad.l + step * i + step / 2, y = H - pad.b - ih * (v / max) * t;
          ctx.fillStyle = s.color; ctx.beginPath(); ctx.arc(x, y, 2.5, 0, Math.PI * 2); ctx.fill();
        });
      });
    }
  }
  doc.querySelectorAll('[data-chart]').forEach(function (box) {
    var run = function () {
      if (reduced) { drawChart(box, 1); return; }
      var t0 = null;
      (function f(ts) { if (!t0) t0 = ts; var p = Math.min(1, (ts - t0) / 900); drawChart(box, 1 - Math.pow(1 - p, 3)); if (p < 1) requestAnimationFrame(f); })(performance.now());
    };
    if ('IntersectionObserver' in window) {
      var o = new IntersectionObserver(function (en) { if (en[0].isIntersecting) { run(); o.disconnect(); } }); o.observe(box);
    } else run();
    var rt; window.addEventListener('resize', function () { clearTimeout(rt); rt = setTimeout(function () { drawChart(box, 1); }, 150); });
  });
  /* Anneaux de progression */
  doc.querySelectorAll('[data-ring]').forEach(function (r) {
    var c = r.querySelector('.ring__val'), len = 2 * Math.PI * 56;
    c.style.strokeDasharray = len; c.style.strokeDashoffset = len;
    requestAnimationFrame(function () { c.style.strokeDashoffset = len * (1 - (parseFloat(r.dataset.ring) || 0) / 100); });
  });

  /* ------------------------------------------------------------------
     Aides d'administration
     ------------------------------------------------------------------ */
  // Ajout de réponses supplémentaires dans l'éditeur de question
  doc.querySelectorAll('[data-add-answer]').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var list = doc.getElementById(btn.dataset.addAnswer);
      var rows = list.querySelectorAll('.answer-row').length;
      if (rows >= 6) { A.toast('warning', 'Six réponses maximum par question.'); return; }
      var row = doc.createElement('div');
      row.className = 'answer-row';
      row.innerHTML = '<input type="radio" name="correct" value="' + rows + '" aria-label="Bonne réponse"><input class="input" name="answers[]" maxlength="500" placeholder="Réponse ' + (rows + 1) + '"><button class="btn btn--sm btn--danger" type="button" data-remove-row aria-label="Retirer">×</button>';
      list.appendChild(row);
    });
  });
  doc.addEventListener('click', function (e) {
    var rm = e.target.closest('[data-remove-row]');
    if (!rm) return;
    var list = rm.closest('[id]');
    rm.closest('.answer-row').remove();
    if (list) list.querySelectorAll('.answer-row input[type="radio"]').forEach(function (r, i) { r.value = i; });
  });
  // Champs conditionnels selon le type de leçon
  var typeSel = doc.querySelector('[data-lesson-type]');
  if (typeSel) {
    var sync = function () {
      doc.querySelectorAll('[data-show-for]').forEach(function (el) {
        el.hidden = el.dataset.showFor.split(',').indexOf(typeSel.value) === -1;
      });
    };
    typeSel.addEventListener('change', sync); sync();
  }
  // Sélecteur de formation → filtre automatique
  doc.querySelectorAll('select[data-autosubmit]').forEach(function (s) { s.addEventListener('change', function () { s.form.submit(); }); });
})();

/* =====================================================================
   Fiches de révision : transforme un glossaire (dl.glossary) en cartes
   à retourner, avec mélange et suivi « acquis / à revoir ».
   ===================================================================== */
(function () {
  'use strict';
  var doc = document;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  doc.querySelectorAll('.lesson-body dl.glossary').forEach(function (dl, n) {
    var cards = [];
    dl.querySelectorAll('dt').forEach(function (dt) {
      var dd = dt.nextElementSibling;
      if (dd && dd.tagName === 'DD') cards.push({ t: dt.textContent.trim(), d: dd.textContent.trim() });
    });
    if (cards.length < 3) return;
    var key = 'afspy-cards-' + location.search + '-' + n, known = {};
    try { known = JSON.parse(localStorage.getItem(key) || '{}') || {}; } catch (e) { known = {}; }
    var save = function () { try { localStorage.setItem(key, JSON.stringify(known)); } catch (e) {} };

    var wrap = doc.createElement('section');
    wrap.className = 'flash';
    wrap.setAttribute('aria-label', 'Fiches de révision');
    wrap.innerHTML =
      '<div class="flash__head"><h2>Fiches de révision</h2><span class="flash__score" aria-live="polite"></span></div>' +
      '<button class="flash__card" type="button" aria-describedby="flash-hint-' + n + '"><span class="flash__inner"><span class="flash__face flash__front"><small>NOTION</small><b></b></span><span class="flash__face flash__back"><small>DÉFINITION</small><span></span></span></span></button>' +
      '<p class="flash__hint" id="flash-hint-' + n + '">Cliquez (ou Espace) pour retourner la carte · ← → pour naviguer</p>' +
      '<div class="flash__bar"><button class="btn btn--sm" type="button" data-f="prev">←</button><span class="flash__pos mono"></span><button class="btn btn--sm" type="button" data-f="next">→</button></div>' +
      '<div class="flash__actions"><button class="btn btn--sm" type="button" data-f="again">À revoir</button><button class="btn btn--sm btn--primary" type="button" data-f="known">Je sais</button><button class="btn btn--sm btn--ghost" type="button" data-f="shuffle">Mélanger</button><button class="btn btn--sm" type="button" data-f="reset">Réinitialiser</button></div>';
    dl.parentNode.insertBefore(wrap, dl);

    var order = cards.map(function (_, i) { return i; }), pos = 0;
    var card = wrap.querySelector('.flash__card'), front = wrap.querySelector('.flash__front b'), back = wrap.querySelector('.flash__back span');
    var score = wrap.querySelector('.flash__score'), posEl = wrap.querySelector('.flash__pos');
    function render() {
      var c = cards[order[pos]];
      card.classList.remove('is-flipped');
      front.textContent = c.t; back.textContent = c.d;
      card.setAttribute('aria-label', 'Notion : ' + c.t + '. Retourner pour voir la définition.');
      card.classList.toggle('is-known', !!known[c.t]);
      posEl.textContent = (pos + 1) + ' / ' + cards.length;
      var k = cards.filter(function (x) { return known[x.t]; }).length;
      score.textContent = k + ' / ' + cards.length + ' acquises';
      score.classList.toggle('is-done', k === cards.length);
    }
    function go(d) { pos = (pos + d + cards.length) % cards.length; render(); }
    card.addEventListener('click', function () {
      card.classList.toggle('is-flipped');
      card.setAttribute('aria-label', card.classList.contains('is-flipped') ? 'Définition : ' + cards[order[pos]].d : 'Notion : ' + cards[order[pos]].t);
    });
    card.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') { e.preventDefault(); go(1); }
      if (e.key === 'ArrowLeft') { e.preventDefault(); go(-1); }
    });
    wrap.addEventListener('click', function (e) {
      var b = e.target.closest('[data-f]'); if (!b) return;
      var c = cards[order[pos]];
      switch (b.dataset.f) {
        case 'prev': go(-1); break;
        case 'next': go(1); break;
        case 'known': known[c.t] = 1; save(); go(1); break;
        case 'again': delete known[c.t]; save(); go(1); break;
        case 'shuffle':
          for (var i = order.length - 1; i > 0; i--) { var j = Math.floor(Math.random() * (i + 1)), t = order[i]; order[i] = order[j]; order[j] = t; }
          pos = 0; render(); break;
        case 'reset': known = {}; save(); pos = 0; render(); break;
      }
    });
    if (reduced) wrap.classList.add('flash--flat');
    render();
  });
})();

/* =====================================================================
   Notes personnelles (sauvegarde automatique) et compteur du dépôt de travaux
   ===================================================================== */
(function () {
  'use strict';
  var doc = document, A = window.AFSPY || {};
  doc.querySelectorAll('textarea[data-note]').forEach(function (ta) {
    var state = doc.querySelector('[data-note-state]'), timer = null, last = ta.value;
    function save() {
      if (ta.value === last) return;
      var val = ta.value;
      if (state) state.textContent = 'enregistrement…';
      A.request(A.url('ajax/note.php'), { body: { lesson_id: ta.dataset.note, content: val } }).then(function (j) {
        if (j.ok) { last = val; if (state) state.textContent = 'enregistrées · ' + j.saved_at; }
        else if (state) state.textContent = j.message || 'échec de l\'enregistrement';
      });
    }
    ta.addEventListener('input', function () { clearTimeout(timer); if (state) state.textContent = 'modifications…'; timer = setTimeout(save, 900); });
    ta.addEventListener('blur', save);
    window.addEventListener('beforeunload', save);
  });
  doc.querySelectorAll('textarea[data-counter]').forEach(function (ta) {
    var out = ta.form && ta.form.querySelector('[data-count-out]');
    var upd = function () {
      var n = ta.value.trim().length;
      if (out) { out.textContent = n.toLocaleString('fr-FR') + ' caractère' + (n > 1 ? 's' : '') + (n < 200 ? ' · 200 minimum' : ''); out.style.color = n < 200 ? 'var(--gold)' : 'var(--green)'; }
    };
    ta.addEventListener('input', upd); upd();
  });
})();
