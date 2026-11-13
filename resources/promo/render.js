const { chromium } = require('playwright'); const path = require('path');
const FPS = 30, OUT = path.join(__dirname, 'build', 'frames'), W = +process.env.WORKERS || 4;
(async () => {
  const b = await chromium.launch();
  const total = Math.round(53 * FPS); const t0 = Date.now();
  await Promise.all([...Array(W)].map(async (_, k) => {
    const p = await b.newPage({ viewport: { width: 1920, height: 1080 } });
    p.on('pageerror', e => console.log('ERR', e.message));
    await p.goto('http://127.0.0.1:8090/build/spot.html'); await p.waitForFunction(() => window.READY);
    await p.evaluate(() => document.fonts.ready);
    for (let i = k; i < total; i += W) {
      await p.evaluate(t => render(t), i / FPS);
      await p.screenshot({ path: path.join(OUT, String(i).padStart(5, '0') + '.jpg'), type: 'jpeg', quality: 93 });
      if (k === 0 && i % 200 === 0) console.log('image', i, '/', total, Math.round((Date.now() - t0) / 1000) + ' s');
    }
  }));
  await b.close(); console.log('fini en', Math.round((Date.now() - t0) / 1000), 's');
})();
