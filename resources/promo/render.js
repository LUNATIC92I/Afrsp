const { chromium } = require('playwright'); const path = require('path');
// Paramètres : PAGE (adresse de la composition), DURATION (s), OUT (dossier des images), VW et VH (taille, 1920 x 1080 par défaut)
const FPS = 30, OUT = process.env.OUT || path.join(__dirname, 'build', 'frames'), W = +process.env.WORKERS || 4;
const PAGE = process.env.PAGE || 'http://127.0.0.1:8090/build/spot.html';
(async () => {
  const b = await chromium.launch();
  const total = Math.round((+process.env.DURATION || 53) * FPS); const t0 = Date.now();
  await Promise.all([...Array(W)].map(async (_, k) => {
    const p = await b.newPage({ viewport: { width: +process.env.VW || 1920, height: +process.env.VH || 1080 } });
    p.on('pageerror', e => console.log('ERR', e.message));
    await p.goto(PAGE); await p.waitForFunction(() => window.READY);
    await p.evaluate(() => document.fonts.ready);
    for (let i = k; i < total; i += W) {
      await p.evaluate(t => render(t), i / FPS);
      await p.screenshot({ path: path.join(OUT, String(i).padStart(5, '0') + '.jpg'), type: 'jpeg', quality: 93 });
      if (k === 0 && i % 200 === 0) console.log('image', i, '/', total, Math.round((Date.now() - t0) / 1000) + ' s');
    }
  }));
  await b.close(); console.log('fini en', Math.round((Date.now() - t0) / 1000), 's');
})();
