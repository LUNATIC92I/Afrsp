"""Musique originale du spot TikTok : pulsation énergique (112 BPM), nappe, souffles aux changements
de scène, montée avant l'offre et impacts. Usage : python3 music.py <timeline.json> <sortie.wav>"""
import sys, json, wave, numpy as np
tl = json.load(open(sys.argv[1], encoding='utf-8'))
SR = 48000; DUR = tl['duration']; N = int(SR * DUR); t = np.arange(N) / SR
L = np.zeros(N); R = np.zeros(N); rng = np.random.default_rng(9)
note = lambda m: 440 * 2 ** ((m - 69) / 12)
sc = {s['id']: s for s in tl['scenes']}
def env(start, dur, a, r):
    e = np.zeros(N); s = int(start * SR); d = min(int(dur * SR), N - s)
    if d <= 0: return e
    seg = np.ones(d); at = min(int(a * SR), d); rl = min(int(r * SR), d)
    seg[:at] = np.linspace(0, 1, at); seg[d - rl:] *= np.linspace(1, 0, rl); e[s:s + d] = seg; return e
def tone(f, h=6, ph=0): return sum(np.sin(2 * np.pi * f * k * t + ph * k) / k ** 1.3 for k in range(1, h + 1))
def add(at, sig, gl=1, gr=1):
    s = int(at * SR); n = min(len(sig), N - s)
    if n > 0: L[s:s + n] += sig[:n] * gl; R[s:s + n] += sig[:n] * gr
# Nappe : la mineur, fa, do, sol (4 mesures de 2,14 s par accord)
bpm = 112; beat = 60 / bpm; bar = 4 * beat
chords = [[57, 60, 64], [53, 57, 60], [48, 55, 60], [55, 59, 62]]
x = 0.0; i = 0
while x < DUR:
    e = env(x, 2 * bar + .6, .4, .8)
    for j, m in enumerate(chords[i % 4]):
        v = (tone(note(m)) + tone(note(m) * 1.005, ph=1.1)) * .5; L += v * e * .012 * (1 if j % 2 else .8); R += v * e * .012 * (.8 if j % 2 else 1)
    b = np.sin(2 * np.pi * note(chords[i % 4][0] - 24) * t) * e; L += b * .07; R += b * .07
    x += 2 * bar; i += 1
# Rythme : grosse caisse sur chaque temps, claquement sur 2 et 4, charleston en croches
def kick(g=1):
    n = int(.4 * SR); tt = np.arange(n) / SR
    return np.sin(2 * np.pi * np.cumsum(48 + 110 * np.exp(-tt * 32)) / SR) * np.exp(-tt * 10) * g
def clap(g=1):
    n = int(.18 * SR); tt = np.arange(n) / SR; nz = rng.standard_normal(n)
    return np.diff(np.concatenate([[0], nz])) * np.exp(-tt * 28) * g
def hat(g=1):
    n = int(.05 * SR); tt = np.arange(n) / SR
    return np.diff(np.concatenate([[0], rng.standard_normal(n)])) * np.exp(-tt * 80) * g
start = sc['promesse']['start']; stop = sc['cta']['start'] + 3.2
offer = sc['offre']['items'][0]
riser_from = sc['afrique']['end'] - 2.0
x = start; k = 0
while x < stop:
    quiet = riser_from < x < sc['offre']['start'] + .2       # silence rythmique pendant la montée
    if not quiet:
        add(x, kick(.30))
        if k % 4 in (1, 3): add(x, clap(.05), .9, 1.1)
        add(x + beat / 2, hat(.022), .8, 1.2)
    x += beat; k += 1
# Souffles aux changements de scène
def whoosh(at, d=.6, g=.13):
    n = int(d * SR); nz = rng.standard_normal(n); out = np.zeros(n); acc = 0; kk = np.linspace(.03, .4, n)
    for q in range(n): acc += (nz[q] - acc) * kk[q]; out[q] = acc
    e = np.sin(np.linspace(0, np.pi, n)) ** 2 * g; pan = np.linspace(-1, 1, n)
    add(at, out * e * (1 - pan) / 2 * 2, 1, 0); add(at, out * e * (1 + pan) / 2 * 2, 0, 1)
for s in tl['scenes'][1:]: whoosh(s['start'] - .35)
# Montée puis impact sur le prix ; impact final
n = int(2.0 * SR); tt = np.arange(n) / SR
add(riser_from, np.sin(2 * np.pi * (180 * tt + 260 * tt ** 2)) * (tt / 2.0) ** 2 * .07)
add(riser_from, rng.standard_normal(n) * (tt / 2.0) ** 3 * .05)
def boom(at, g):
    n = int(2.2 * SR); tt = np.arange(n) / SR
    add(at, (np.sin(2 * np.pi * np.cumsum(38 + 70 * np.exp(-tt * 7)) / SR) + .25 * rng.standard_normal(n) * np.exp(-tt * 30)) * np.exp(-tt * 2.4) * g)
boom(.45, .45); boom(offer['t'] + offer['d'] * .38, .6); boom(sc['cta']['start'] + .1, .4)
fade = np.ones(N); fi = int(.3 * SR); fo = int(2.2 * SR); fade[:fi] = np.linspace(0, 1, fi); fade[-fo:] = np.linspace(1, 0, fo)
L *= fade; R *= fade; pk = max(np.abs(L).max(), np.abs(R).max()); L = L / pk * .89; R = R / pk * .89
with wave.open(sys.argv[2], 'wb') as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes((np.stack([L, R], 1) * 32767).astype('<i2').tobytes())
print('musique', DUR, 's')
