"""Musique d'accompagnement de la vidéo de synthèse : nappe posée, pulsation douce,
souffles aux changements de chapitre (lus dans timeline.json). Usage : python3 music.py <timeline.json> <sortie.wav>"""
import sys, json, wave, numpy as np
tl = json.load(open(sys.argv[1], encoding='utf-8'))
SR = 48000; DUR = tl['duration']; N = int(SR * DUR); t = np.arange(N) / SR
L = np.zeros(N); R = np.zeros(N); rng = np.random.default_rng(5)
note = lambda m: 440 * 2 ** ((m - 69) / 12)
def env(start, dur, a, r):
    e = np.zeros(N); s = int(start * SR); d = min(int(dur * SR), N - s)
    if d <= 0: return e
    seg = np.ones(d); at = min(int(a * SR), d); rl = min(int(r * SR), d)
    seg[:at] = np.linspace(0, 1, at); seg[d - rl:] *= np.linspace(1, 0, rl); e[s:s + d] = seg; return e
def tone(f, h=5, ph=0): return sum(np.sin(2 * np.pi * f * k * t + ph * k) / k ** 1.4 for k in range(1, h + 1))
# Progression lente : Ré mineur, Si bémol, Fa, Do (8 s par accord)
chords = [[50, 57, 62, 65], [46, 53, 58, 62], [41, 53, 57, 60], [48, 55, 60, 64]]
seg = 8.0; i = 0; x = 0.0
while x < DUR:
    e = env(x, seg + 2, 2.5, 2.5)
    for j, m in enumerate(chords[i % 4]):
        v = (tone(note(m)) + tone(note(m) * 1.004, ph=.7)) * .5
        L += v * e * .014 * (1 if j % 2 else .75); R += v * e * .014 * (.75 if j % 2 else 1)
    b = np.sin(2 * np.pi * note(chords[i % 4][0] - 12) * t) * e; L += b * .05; R += b * .05
    x += seg; i += 1
# Pulsation douce (battement grave et tic léger), 84 BPM, hors titre et conclusion
beat = 60 / 84; s0 = tl['scenes'][1]['start']; s1 = tl['scenes'][-1]['start']
x = s0
while x < s1:
    n = int(.35 * SR); s = int(x * SR); tt = np.arange(n) / SR
    k = np.sin(2 * np.pi * np.cumsum(45 + 70 * np.exp(-tt * 30)) / SR) * np.exp(-tt * 11) * .16
    e2 = min(n, N - s); L[s:s + e2] += k[:e2]; R[s:s + e2] += k[:e2]
    m = int(.05 * SR); h = np.diff(np.concatenate([[0], rng.standard_normal(m)])) * np.exp(-np.arange(m) / SR * 70) * .012
    s2 = int((x + beat / 2) * SR); e3 = min(m, N - s2); L[s2:s2 + e3] += h[:e3]; R[s2:s2 + e3] += h[:e3] * 1.2
    x += beat
# Souffle à chaque chapitre, impact doux à l'ouverture et à la conclusion
def whoosh(at, d=.8):
    n = int(d * SR); s = int(at * SR); nz = rng.standard_normal(n); out = np.zeros(n); acc = 0; k = np.linspace(.02, .3, n)
    for q in range(n): acc += (nz[q] - acc) * k[q]; out[q] = acc
    e = np.sin(np.linspace(0, np.pi, n)) ** 2 * .16; pan = np.linspace(-1, 1, n); e2 = min(n, N - s)
    L[s:s + e2] += (out * e * (1 - pan) / 2)[:e2]; R[s:s + e2] += (out * e * (1 + pan) / 2)[:e2]
def boom(at, g):
    n = int(2.5 * SR); s = int(at * SR); tt = np.arange(n) / SR
    v = np.sin(2 * np.pi * np.cumsum(36 + 50 * np.exp(-tt * 6)) / SR) * np.exp(-tt * 2) * g; e2 = min(n, N - s)
    L[s:s + e2] += v[:e2]; R[s:s + e2] += v[:e2]
for sc in tl['scenes'][1:]: whoosh(sc['start'] - .5)
boom(1.0, .35); boom(tl['scenes'][-1]['items'][1]['t'] - .2, .35)
fade = np.ones(N); fi = int(.5 * SR); fo = int(3 * SR); fade[:fi] = np.linspace(0, 1, fi); fade[-fo:] = np.linspace(1, 0, fo)
L *= fade; R *= fade; pk = max(np.abs(L).max(), np.abs(R).max()); L = L / pk * .89; R = R / pk * .89
with wave.open(sys.argv[2], 'wb') as w:
    w.setnchannels(2); w.setsampwidth(2); w.setframerate(SR); w.writeframes((np.stack([L, R], 1) * 32767).astype('<i2').tobytes())
print('musique', DUR, 's')
