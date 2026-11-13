/* AFRICA'SPY : amorçage synchrone minimal (évite le flash avant les révélations). */
(function (d) {
  var h = d.documentElement;
  h.className = h.className.replace('no-js', 'js');
  try {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) h.className += ' reduced-motion';
  } catch (e) {}
})(document);
