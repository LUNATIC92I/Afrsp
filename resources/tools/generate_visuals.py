#!/usr/bin/env python3
"""
AFRICA'SPY — générateur des visuels cinématographiques originaux.

Produit des images raster (WebP) entièrement originales, rendues de manière
procédurale à partir des tracés cartographiques de l'Afrique (Natural Earth via
johan/world.geo.json, domaine public) : aucun visuel tiers, aucune licence à
attribuer. Les administrateurs peuvent remplacer chaque visuel par une
photographie sous licence depuis l'espace d'administration.

Usage : python3 resources/tools/generate_visuals.py  (requiert Pillow + numpy)
"""
import json, math, os, random
import numpy as np
from PIL import Image, ImageDraw, ImageFilter, ImageFont

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
OUT = os.path.join(ROOT, 'assets', 'images')
MAP = json.load(open(os.path.join(ROOT, 'includes', 'data', 'africa-map.json')))
FONT_MONO = os.path.join(ROOT, 'resources', 'pdf-fonts', 'ShareTechMono-400.ttf')
FONT_HEAD = os.path.join(ROOT, 'resources', 'pdf-fonts', 'Orbitron-700.ttf')

CYAN = (0, 200, 255); BLUE = (8, 124, 255); TURQ = (0, 240, 208); GREEN = (26, 255, 156)
GOLD = (213, 179, 106); VIOLET = (134, 93, 255); RED = (255, 64, 87); WHITE = (232, 242, 250)

random.seed(7)


def parse_paths():
    """Convertit les chemins SVG 'M..L..Z' (repère 740x730) en listes de polygones."""
    polys = []
    for c in MAP:
        for seg in c['d'].split('M')[1:]:
            seg = seg.rstrip('Z')
            pts = [tuple(map(float, p.split(','))) for p in seg.split('L')]
            polys.append(pts)
    return polys


POLYS = parse_paths()


class Canvas:
    def __init__(self, w, h, top=(5, 7, 11), bottom=(11, 16, 24)):
        self.w, self.h = w, h
        y = np.linspace(0, 1, h)[:, None, None]
        t = np.array(top, float)[None, None, :]; b = np.array(bottom, float)[None, None, :]
        self.base = (t * (1 - y) + b * y) * np.ones((h, w, 3))

    def layer(self):
        return Image.new('RGB', (self.w, self.h), (0, 0, 0))

    def add(self, img, blur=0, gain=1.0):
        if blur:
            img = img.filter(ImageFilter.GaussianBlur(blur))
        self.base += np.asarray(img, float) * gain

    def over(self, rgba):
        a = np.asarray(rgba, float)
        alpha = a[..., 3:4] / 255.0
        self.base = self.base * (1 - alpha) + a[..., :3] * alpha

    def vignette(self, strength=0.75):
        yy, xx = np.mgrid[0:self.h, 0:self.w]
        d = np.sqrt(((xx - self.w / 2) / (self.w / 2)) ** 2 + ((yy - self.h / 2) / (self.h / 2)) ** 2)
        self.base *= np.clip(1 - strength * (d / 1.42) ** 2.2, 0, 1)[..., None]

    def grain(self, amount=5):
        self.base += np.random.default_rng(3).normal(0, amount, self.base.shape)

    def scanlines(self, every=3, k=0.92):
        self.base[::every] *= k

    def save(self, name, quality=80):
        img = Image.fromarray(np.clip(self.base, 0, 255).astype('uint8'))
        img.save(os.path.join(OUT, name), 'WEBP', quality=quality, method=6)
        print('→', name, os.path.getsize(os.path.join(OUT, name)) // 1024, 'Ko')


def map_lines(draw, x0, y0, scale, color, width=1, fill=None, skew=None):
    for pts in POLYS:
        q = [(x0 + x * scale, y0 + y * scale) for x, y in pts]
        if skew:
            q = [skew(px, py) for px, py in q]
        if fill:
            draw.polygon(q, fill=fill)
        draw.line(q + [q[0]], fill=color, width=width, joint='curve')


def font(path, size):
    try:
        return ImageFont.truetype(path, size)
    except Exception:
        return ImageFont.load_default()


def text_rows(draw, x, y, rows, color, size=14, gap=20):
    f = font(FONT_MONO, size)
    for i, r in enumerate(rows):
        draw.text((x, y + i * gap), r, fill=color, font=f)


def silhouette(draw, cx, base_y, s, color=(3, 5, 8)):
    """Analyste vu de dos : tête, nuque, épaules, buste."""
    draw.ellipse((cx - 34 * s, base_y - 250 * s, cx + 34 * s, base_y - 172 * s), fill=color)
    draw.rectangle((cx - 16 * s, base_y - 185 * s, cx + 16 * s, base_y - 150 * s), fill=color)
    draw.ellipse((cx - 112 * s, base_y - 168 * s, cx + 112 * s, base_y + 140 * s), fill=color)
    draw.polygon([(cx - 26 * s, base_y - 178 * s), (cx + 26 * s, base_y - 178 * s), (cx + 60 * s, base_y - 150 * s), (cx - 60 * s, base_y - 150 * s)], fill=color)


def rim(draw, cx, base_y, s, color):
    draw.arc((cx - 34 * s, base_y - 250 * s, cx + 34 * s, base_y - 172 * s), 200, 340, fill=color, width=max(1, int(3 * s)))
    draw.arc((cx - 112 * s, base_y - 168 * s, cx + 112 * s, base_y + 140 * s), 215, 325, fill=color, width=max(1, int(2 * s)))


def floor_grid(draw, w, h, horizon, vp_x, color, n=40, rows=14):
    for i in range(-n, n + 1):
        x_bottom = vp_x + i * (w / n) * 2.2
        draw.line([(vp_x, horizon), (x_bottom, h)], fill=color, width=1)
    for j in range(1, rows):
        t = (j / rows) ** 2.1
        y = horizon + (h - horizon) * t
        draw.line([(0, y), (w, y)], fill=color, width=1)


def arcs_between(draw, pts, color, width=2, lift=0.35):
    for (x1, y1), (x2, y2) in pts:
        mx, my = (x1 + x2) / 2, (y1 + y2) / 2 - math.hypot(x2 - x1, y2 - y1) * lift
        prev = (x1, y1)
        for k in range(1, 41):
            t = k / 40
            x = (1 - t) ** 2 * x1 + 2 * (1 - t) * t * mx + t * t * x2
            y = (1 - t) ** 2 * y1 + 2 * (1 - t) * t * my + t * t * y2
            draw.line([prev, (x, y)], fill=color, width=width)
            prev = (x, y)


def lonlat(lon, lat):
    return ((lon + 20) * 10, (38 - lat) * 10)


CITIES = [(-17.4, 14.7), (3.4, 6.5), (36.8, -1.3), (31.2, 30.0), (28.0, -26.2), (15.3, -4.3), (38.7, 9.0), (-7.6, 33.6), (10.2, 36.8), (32.6, 0.3), (-0.2, 5.6), (47.5, -18.9)]


# ---------------------------------------------------------------------------
#  1. HERO — salle de commandement face au mur d'écran holographique
# ---------------------------------------------------------------------------
def hero():
    W, H = 1920, 1080
    c = Canvas(W, H, (4, 6, 10), (8, 12, 19))
    # Halo du mur d'écran
    L = c.layer(); d = ImageDraw.Draw(L)
    d.ellipse((260, 40, 1660, 820), fill=(0, 60, 95))
    c.add(L, blur=140, gain=0.9)
    # Panneau principal (écran courbe simulé)
    S = Image.new('RGBA', (W, H), (0, 0, 0, 0)); ds = ImageDraw.Draw(S)
    ds.polygon([(300, 110), (1620, 110), (1580, 640), (340, 640)], fill=(4, 18, 30, 235))
    c.over(S)
    G = c.layer(); dg = ImageDraw.Draw(G)
    for x in range(320, 1610, 44):
        dg.line([(x, 115), (x + (960 - x) * 0.03, 636)], fill=(0, 40, 60), width=1)
    for y in range(130, 640, 44):
        dg.line([(305 + (y - 110) * 0.075, y), (1615 - (y - 110) * 0.075, y)], fill=(0, 40, 60), width=1)
    c.add(G, gain=0.7)
    # Carte de l'Afrique sur l'écran
    M = c.layer(); dm = ImageDraw.Draw(M)
    map_lines(dm, 700, 135, 0.66, (0, 150, 210), 1, fill=(0, 26, 40))
    c.add(M, blur=0, gain=1.0)
    c.add(M, blur=6, gain=0.9)
    # Arcs de connexion entre capitales
    A = c.layer(); da = ImageDraw.Draw(A)
    pts = [((700 + lonlat(*a)[0] * 0.66, 135 + lonlat(*a)[1] * 0.66), (700 + lonlat(*b)[0] * 0.66, 135 + lonlat(*b)[1] * 0.66))
           for a, b in [(CITIES[0], CITIES[2]), (CITIES[1], CITIES[3]), (CITIES[4], CITIES[6]), (CITIES[5], CITIES[7]), (CITIES[9], CITIES[10]), (CITIES[8], CITIES[11])]]
    arcs_between(da, pts, (0, 200, 170), 2)
    for lon, lat in CITIES:
        x, y = lonlat(lon, lat); x = 700 + x * 0.66; y = 135 + y * 0.66
        da.ellipse((x - 4, y - 4, x + 4, y + 4), fill=WHITE)
    c.add(A, gain=0.85); c.add(A, blur=8, gain=1.2)
    # Panneaux latéraux de données
    P = c.layer(); dp = ImageDraw.Draw(P)
    for bx, by in [(360, 150), (360, 330), (1300, 150), (1300, 400)]:
        dp.rectangle((bx, by, bx + 240, by + 150), outline=(0, 110, 150), width=1)
        for k in range(7):
            ln = random.randint(60, 220)
            dp.rectangle((bx + 12, by + 16 + k * 18, bx + 12 + ln, by + 22 + k * 18), fill=(0, 70 + k * 6, 100))
    text_rows(dp, 368, 488, ['SECTOR 07 // SAHEL', 'LAT 13.5127  LON 2.1128', 'FEED: OPEN SOURCE', 'STATUS: ANALYSIS'], (0, 170, 210), 15)
    text_rows(dp, 1306, 168 + 140, [], (0, 0, 0))
    c.add(P, gain=0.8)
    # Sol en perspective
    F = c.layer(); df = ImageDraw.Draw(F)
    floor_grid(df, W, H, 700, 960, (0, 70, 95))
    c.add(F, gain=0.45)
    # Consoles + silhouettes au premier plan
    D = Image.new('RGBA', (W, H), (0, 0, 0, 0)); dd = ImageDraw.Draw(D)
    dd.polygon([(0, 905), (W, 905), (W, H), (0, H)], fill=(5, 8, 12, 255))
    dd.polygon([(0, 905), (W, 905), (W, 915), (0, 915)], fill=(18, 30, 40, 255))
    for mx in [130, 560, 1080, 1520]:
        dd.polygon([(mx, 790), (mx + 250, 790), (mx + 250, 900), (mx, 900)], fill=(6, 12, 18, 255))
    for cx, s in [(470, 1.45), (960, 1.62), (1430, 1.4)]:
        silhouette(dd, cx, 1080, s, (2, 3, 6, 255))
    c.over(D)
    E = c.layer(); de = ImageDraw.Draw(E)
    for mx in [130, 560, 1080, 1520]:
        de.rectangle((mx + 8, 798, mx + 242, 892), outline=(0, 140, 190), width=2)
        de.line([(mx + 20, 870), (mx + 230, 840)], fill=(26, 255, 156), width=2)
    for cx, s in [(470, 1.45), (960, 1.62), (1430, 1.4)]:
        rim(de, cx, 1080, s, (0, 150, 200))
    de.line([(0, 905), (W, 905)], fill=(0, 140, 190), width=2)
    c.add(E, gain=0.8); c.add(E, blur=10, gain=0.6)
    # Poussière lumineuse
    Pz = c.layer(); dz = ImageDraw.Draw(Pz)
    for _ in range(260):
        x, y = random.randint(0, W), random.randint(0, 900); r = random.random() * 1.6
        dz.ellipse((x - r, y - r, x + r, y + r), fill=(0, 120 + random.randint(0, 80), 160))
    c.add(Pz, blur=1, gain=0.6)
    c.scanlines(3, 0.94); c.vignette(0.85); c.grain(4)
    c.save('hero-command-center.webp', 78)


# ---------------------------------------------------------------------------
#  2. Salle d'analyse (page institution)
# ---------------------------------------------------------------------------
def ops_room():
    W, H = 1600, 1000
    c = Canvas(W, H, (5, 7, 11), (10, 14, 21))
    L = c.layer(); d = ImageDraw.Draw(L)
    for i in range(5):
        x = 120 + i * 290
        d.rectangle((x, 160, x + 250, 430), fill=(0, 45 + i * 6, 70))
    c.add(L, blur=60, gain=0.8)
    S = Image.new('RGBA', (W, H), (0, 0, 0, 0)); ds = ImageDraw.Draw(S)
    for i in range(5):
        x = 120 + i * 290
        ds.rectangle((x, 160, x + 250, 430), fill=(3, 14, 22, 240))
    c.over(S)
    M = c.layer(); dm = ImageDraw.Draw(M)
    # écran 1 : carte ; 2 : graphes ; 3 : réseau ; 4 : courbes ; 5 : texte
    map_lines(dm, 120 + 290 * 0 + 30, 170, 0.33, (0, 170, 220), 1)
    for k in range(10):
        h = random.randint(30, 200); dm.rectangle((440 + k * 22, 420 - h, 452 + k * 22, 420), fill=(0, 120, 180) if k % 3 else (213, 179, 106))
    nodes = [(700 + random.randint(0, 210), 180 + random.randint(0, 230)) for _ in range(14)]
    for a in nodes:
        for b in nodes:
            if a < b and math.dist(a, b) < 90:
                dm.line([a, b], fill=(0, 200, 170), width=1)
    for x, y in nodes:
        dm.ellipse((x - 3, y - 3, x + 3, y + 3), fill=WHITE)
    prev = None
    for k in range(60):
        x = 990 + k * 4; y = 300 + math.sin(k / 6) * 60 + random.randint(-12, 12)
        if prev: dm.line([prev, (x, y)], fill=(26, 255, 156), width=2)
        prev = (x, y)
    text_rows(dm, 1290, 180, ['> BRIEFING 0417', '> SRC: OSINT/ROI', '> CONF: B2', '> ANALYST: T-09', '> REVIEW: PENDING', '> LEGAL: CLEARED', '> ARCHIVE: 2026'], (0, 190, 230), 16, 32)
    c.add(M, gain=0.9); c.add(M, blur=5, gain=0.7)
    F = c.layer(); df = ImageDraw.Draw(F)
    floor_grid(df, W, H, 560, 800, (0, 55, 80), 30, 10)
    c.add(F, gain=0.35)
    D = Image.new('RGBA', (W, H), (0, 0, 0, 0)); dd = ImageDraw.Draw(D)
    dd.polygon([(0, 760), (W, 700), (W, H), (0, H)], fill=(5, 8, 12, 255))
    for cx, s, by in [(300, 1.1, 1000), (760, 1.25, 1000), (1250, 1.05, 1000)]:
        silhouette(dd, cx, by, s, (2, 3, 6, 255))
    c.over(D)
    E = c.layer(); de = ImageDraw.Draw(E)
    de.line([(0, 760), (W, 700)], fill=(0, 130, 180), width=2)
    for cx, s, by in [(300, 1.1, 1000), (760, 1.25, 1000), (1250, 1.05, 1000)]:
        rim(de, cx, by, s, (0, 150, 200))
    c.add(E, gain=0.7); c.add(E, blur=8, gain=0.5)
    c.scanlines(3, 0.95); c.vignette(0.8); c.grain(4)
    c.save('ops-room.webp', 78)


# ---------------------------------------------------------------------------
#  3. Table tactique géopolitique (vue plongeante)
# ---------------------------------------------------------------------------
def geo_table():
    W, H = 1600, 1000
    c = Canvas(W, H, (6, 8, 12), (6, 9, 14))
    def skew(x, y):
        # perspective légère : table vue en plongée
        t = y / H
        k = 0.78 + 0.22 * t
        return (W / 2 + (x - W / 2) * k, 80 + y * 0.86)
    T = c.layer(); dt = ImageDraw.Draw(T)
    dt.polygon([skew(80, 40), skew(1520, 40), skew(1520, 980), skew(80, 980)], fill=(10, 30, 40))
    c.add(T, blur=30, gain=0.8)
    G = c.layer(); dg = ImageDraw.Draw(G)
    for x in range(80, 1521, 60):
        dg.line([skew(x, 40), skew(x, 980)], fill=(0, 45, 60))
    for y in range(40, 981, 60):
        dg.line([skew(80, y), skew(1520, y)], fill=(0, 45, 60))
    c.add(G, gain=0.8)
    M = c.layer(); dm = ImageDraw.Draw(M)
    map_lines(dm, 470, 60, 1.18, (213, 179, 106), 2, fill=(28, 26, 18), skew=skew)
    c.add(M, gain=0.85); c.add(M, blur=7, gain=0.6)
    # Lignes de contour (topographie)
    C = c.layer(); dc = ImageDraw.Draw(C)
    for r in range(40, 400, 26):
        cx, cy = 1210, 700
        dc.ellipse(skew(cx - r * 1.3, cy - r)[0:1] + skew(cx - r * 1.3, cy - r)[1:2] + skew(cx + r * 1.3, cy + r), outline=(0, 90, 110))
    # Annotations
    A = c.layer(); da = ImageDraw.Draw(A)
    for lon, lat, lab in [(-1.5, 12.4, 'SAHEL · ZONE ÉTUDE 01'), (43, 8, 'CORNE · ZONE ÉTUDE 02'), (5, 3, 'GOLFE DE GUINÉE · 03'), (29, -2, 'GRANDS LACS · 04')]:
        x, y = lonlat(lon, lat); x, y = skew(470 + x * 1.18, 60 + y * 1.18)
        da.ellipse((x - 26, y - 26, x + 26, y + 26), outline=(0, 220, 255), width=2)
        da.line([(x + 26, y - 10), (x + 90, y - 60), (x + 300, y - 60)], fill=(0, 220, 255), width=2)
        da.text((x + 96, y - 86), lab, fill=WHITE, font=font(FONT_MONO, 20))
    c.add(A, gain=0.9); c.add(A, blur=6, gain=0.6)
    c.vignette(0.9); c.grain(4)
    c.save('geo-table.webp', 80)


# ---------------------------------------------------------------------------
#  4. Laboratoire d'investigation numérique
# ---------------------------------------------------------------------------
def lab():
    W, H = 1600, 1000
    c = Canvas(W, H, (4, 6, 10), (8, 11, 17))
    L = c.layer(); d = ImageDraw.Draw(L)
    d.rectangle((240, 180, 1360, 700), fill=(10, 30, 70))
    c.add(L, blur=110, gain=0.8)
    S = Image.new('RGBA', (W, H), (0, 0, 0, 0)); ds = ImageDraw.Draw(S)
    screens = [(220, 210, 720, 560), (760, 170, 1380, 560), (420, 590, 980, 820), (1020, 590, 1400, 820)]
    for b in screens:
        ds.rectangle(b, fill=(5, 12, 22, 250), outline=(20, 40, 60, 255), width=4)
    c.over(S)
    M = c.layer(); dm = ImageDraw.Draw(M)
    f = font(FONT_MONO, 15)
    hexchars = '0123456789ABCDEF'
    for row in range(17):
        line = '%08X  ' % (row * 16) + ' '.join(random.choice(hexchars) + random.choice(hexchars) for _ in range(12))
        dm.text((240, 228 + row * 19), line, fill=(0, 170, 220) if row % 5 else (213, 179, 106), font=f)
    nodes = [(800 + random.randint(0, 540), 200 + random.randint(0, 330)) for _ in range(22)]
    for a in nodes:
        for b in nodes:
            if a < b and math.dist(a, b) < 120:
                dm.line([a, b], fill=(134, 93, 255), width=1)
    for i, (x, y) in enumerate(nodes):
        col = (0, 240, 208) if i % 4 else (255, 64, 87)
        dm.ellipse((x - 5, y - 5, x + 5, y + 5), fill=col)
    prev = None
    for k in range(130):
        x = 440 + k * 4; y = 720 + math.sin(k / 5) * 40 * math.exp(-((k - 65) / 40) ** 2) + random.randint(-4, 4)
        if prev: dm.line([prev, (x, y)], fill=(26, 255, 156), width=2)
        prev = (x, y)
    for k in range(8):
        dm.rectangle((1040, 610 + k * 24, 1040 + random.randint(80, 330), 622 + k * 24), fill=(0, 90, 140))
    c.add(M, gain=0.9); c.add(M, blur=6, gain=0.7)
    D = Image.new('RGBA', (W, H), (0, 0, 0, 0)); dd = ImageDraw.Draw(D)
    dd.polygon([(0, 860), (W, 860), (W, H), (0, H)], fill=(6, 9, 13, 255))
    silhouette(dd, 1460, 1000, 1.2, (2, 3, 6, 255))
    c.over(D)
    E = c.layer(); de = ImageDraw.Draw(E)
    de.line([(0, 860), (W, 860)], fill=(0, 120, 170), width=2)
    rim(de, 1460, 1000, 1.2, (134, 93, 255))
    c.add(E, gain=0.8); c.add(E, blur=8, gain=0.5)
    c.scanlines(3, 0.94); c.vignette(0.8); c.grain(4)
    c.save('cyber-lab.webp', 78)


# ---------------------------------------------------------------------------
#  5. Couvertures de domaines (A à F)
# ---------------------------------------------------------------------------
def cover(code, accent, motif):
    W, H = 960, 600
    c = Canvas(W, H, (5, 7, 11), (11, 16, 24))
    L = c.layer(); d = ImageDraw.Draw(L)
    d.ellipse((W * 0.45, -150, W * 1.25, H * 0.95), fill=tuple(int(v * 0.35) for v in accent))
    c.add(L, blur=110, gain=1.0)
    M = c.layer(); dm = ImageDraw.Draw(M)
    motif(dm, W, H, accent)
    c.add(M, gain=0.9); c.add(M, blur=5, gain=0.6)
    T = c.layer(); dt = ImageDraw.Draw(T)
    for x in range(0, W, 48):
        dt.line([(x, 0), (x, H)], fill=(10, 18, 26))
    for y in range(0, H, 48):
        dt.line([(0, y), (W, y)], fill=(10, 18, 26))
    c.add(T, gain=0.6)
    O = c.layer(); do = ImageDraw.Draw(O)
    do.text((36, H - 70), f'AFSPY // MODULE {code}', fill=(150, 165, 180), font=font(FONT_MONO, 22))
    do.line([(36, H - 40), (260, H - 40)], fill=accent, width=3)
    c.add(O, gain=0.8)
    c.scanlines(3, 0.95); c.vignette(0.7); c.grain(3)
    c.save(os.path.join('covers', f'{code}.webp'), 78)


def m_archive(d, W, H, a):
    map_lines(d, 470, 40, 0.68, a, 2)
    for i in range(6):
        x = 70 + i * 26; y = 120 + i * 20
        d.rectangle((x, y, x + 210, y + 270), outline=(110, 125, 140), width=2)
        for k in range(8):
            d.line([(x + 18, y + 30 + k * 26), (x + 30 + random.randint(80, 170), y + 30 + k * 26)], fill=(70, 85, 100), width=3)


def m_human(d, W, H, a):
    # entretien : deux interlocuteurs de part et d'autre d'une table, dossier au centre
    d.polygon([(150, 470), (810, 470), (900, 600), (60, 600)], fill=(14, 18, 24))
    d.line([(150, 470), (810, 470)], fill=a, width=2)
    d.rectangle((410, 440, 550, 470), outline=(150, 165, 180), width=2)
    for k in range(3):
        d.line([(425, 450 + k * 7), (520, 450 + k * 7)], fill=(110, 125, 140), width=2)
    for cx, s in [(230, 1.0), (730, 1.0)]:
        silhouette(d, cx, 560, s, (3, 4, 7))
        rim(d, cx, 560, s, a)
    d.arc((300, 150, 660, 420), 205, 335, fill=(90, 80, 60), width=1)
    for k in range(5):
        d.ellipse((452 + k * 14 - 3, 200 - 3, 452 + k * 14 + 3, 200 + 3), fill=(232, 242, 250))


def m_counter(d, W, H, a):
    cx, cy = 620, 280
    for r in range(30, 300, 34):
        d.ellipse((cx - r, cy - r, cx + r, cy + r), outline=a if r % 68 == 30 else (40, 60, 80), width=2)
    for k in range(12):
        ang = k * math.pi / 6
        d.line([(cx, cy), (cx + 300 * math.cos(ang), cy + 300 * math.sin(ang))], fill=(30, 45, 60), width=1)
    d.pieslice((cx - 280, cy - 280, cx + 280, cy + 280), -60, -20, fill=tuple(int(v * 0.5) for v in a))
    for k in range(7):
        r = random.randint(60, 260); ang = random.random() * 6.28
        x, y = cx + r * math.cos(ang), cy + r * math.sin(ang)
        d.ellipse((x - 5, y - 5, x + 5, y + 5), fill=(255, 64, 87) if k == 2 else (232, 242, 250))


def m_cyber(d, W, H, a):
    nodes = [(random.randint(80, W - 60), random.randint(40, H - 120)) for _ in range(46)]
    for p in nodes:
        for q in nodes:
            if p < q and math.dist(p, q) < 150:
                d.line([p, q], fill=tuple(int(v * 0.55) for v in a), width=1)
    for i, (x, y) in enumerate(nodes):
        r = 6 if i % 7 == 0 else 3
        d.ellipse((x - r, y - r, x + r, y + r), fill=(232, 242, 250) if i % 7 else a)


def m_tools(d, W, H, a):
    # champ de vision binoculaire avec échelle de mesure (observation de paysage)
    cy = 280
    for cx in (480, 720):
        for r, w in [(190, 3), (150, 1)]:
            d.ellipse((cx - r, cy - r, cx + r, cy + r), outline=a if w > 1 else (60, 80, 100), width=w)
    pts = [(300 + k * 12, 360 - 40 * math.sin(k / 6) - 18 * math.sin(k / 2.3)) for k in range(51)]
    d.line(pts, fill=(120, 140, 155), width=2)
    for k in range(-6, 7):
        d.line([(600 + k * 18, cy - (10 if k % 3 else 18)), (600 + k * 18, cy)], fill=(170, 190, 205), width=1)
    d.line([(490, cy), (710, cy)], fill=(170, 190, 205), width=1)
    text_rows(d, 70, 90, ['RNG 0420 m', 'AZM 117.4°', 'ELV +02.1°', 'MAG x12'], (150, 170, 190), 22, 34)


def m_geo(d, W, H, a):
    cx, cy = 560, 300
    for k in range(14):
        pts = []
        for t in range(0, 361, 6):
            ang = math.radians(t)
            r = 40 + k * 22 + 18 * math.sin(3 * ang + k * 0.6) + 10 * math.cos(5 * ang)
            pts.append((cx + r * 1.4 * math.cos(ang), cy + r * math.sin(ang)))
        d.line(pts, fill=a if k % 4 == 0 else (50, 70, 80), width=2 if k % 4 == 0 else 1)
    map_lines(d, 60, 60, 0.42, (120, 135, 150), 1)


if __name__ == '__main__':
    os.makedirs(os.path.join(OUT, 'covers'), exist_ok=True)
    hero(); ops_room(); geo_table(); lab()
    cover('A', CYAN, m_archive)
    cover('B', GOLD, m_human)
    cover('C', RED, m_counter)
    cover('D', VIOLET, m_cyber)
    cover('E', TURQ, m_tools)
    cover('F', GREEN, m_geo)
