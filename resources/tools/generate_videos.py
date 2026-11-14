#!/usr/bin/env python3
"""
AFRICA'SPY — génération des « briefings vidéo » pédagogiques (MP4 H.264, sans narration).

Pour chaque formation : écran-titre, un écran par chapitre (titre + résumé),
un rappel éthique et un écran de clôture, enchaînés par fondus enchaînés.
Entrée : JSON du catalogue (php -r 'echo json_encode(require "database/seed/catalog.php");').
Usage : python3 resources/tools/generate_videos.py /chemin/catalog.json
"""
import json, os, subprocess, sys, tempfile, textwrap
from PIL import Image, ImageDraw, ImageFont, ImageFilter

ROOT = os.path.dirname(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
FONTS = os.path.join(ROOT, 'resources', 'pdf-fonts')
OUT = os.path.join(ROOT, 'uploads', 'videos')
W, H = 1280, 720
SLIDE = 5.0
FADE = 0.8
ACC = {'A': (0, 200, 255), 'B': (213, 179, 106), 'C': (255, 64, 87), 'D': (134, 93, 255), 'E': (0, 240, 208), 'F': (26, 255, 156), 'G': (8, 124, 255)}


def f(name, size):
    return ImageFont.truetype(os.path.join(FONTS, name), size)


def base(cat):
    cover = Image.open(os.path.join(ROOT, 'assets', 'images', 'covers', cat + '.webp')).convert('RGB').resize((W, int(W * 600 / 960)))
    img = Image.new('RGB', (W, H), (5, 7, 11))
    img.paste(cover.filter(ImageFilter.GaussianBlur(2)), (0, (H - cover.height) // 2))
    shade = Image.new('RGBA', (W, H), (5, 7, 11, 200))
    img = Image.alpha_composite(img.convert('RGBA'), shade)
    d = ImageDraw.Draw(img)
    a = ACC.get(cat, (0, 200, 255))
    d.rectangle((60, 60, W - 60, H - 60), outline=(60, 75, 90), width=1)
    for (x, y, dx, dy) in [(60, 60, 1, 1), (W - 60, 60, -1, 1), (60, H - 60, 1, -1), (W - 60, H - 60, -1, -1)]:
        d.line([(x, y), (x + 30 * dx, y)], fill=a, width=3)
        d.line([(x, y), (x, y + 30 * dy)], fill=a, width=3)
    d.text((90, 84), "AFRICA'SPY // INTELLIGENCE ACADEMY", font=f('ShareTechMono-400.ttf', 18), fill=(150, 165, 180))
    return img, d, a


def wrap(d, text, font, maxw):
    words, lines, cur = text.split(), [], ''
    for w in words:
        t = (cur + ' ' + w).strip()
        if d.textlength(t, font=font) > maxw and cur:
            lines.append(cur); cur = w
        else:
            cur = t
    if cur: lines.append(cur)
    return lines


def slide(cat, kicker, title, body, footer):
    img, d, a = base(cat)
    d.text((90, 200), kicker, font=f('ShareTechMono-400.ttf', 22), fill=a)
    d.line([(90, 238), (250, 238)], fill=a, width=2)
    y = 262
    tf = f('Orbitron-700.ttf', 44 if len(title) < 40 else 36)
    for line in wrap(d, title.upper(), tf, W - 200)[:3]:
        d.text((90, y), line, font=tf, fill=(232, 242, 250)); y += tf.size + 14
    y += 16
    bf = f('Inter-400.ttf', 26)
    for line in wrap(d, body, bf, W - 260)[:5]:
        d.text((90, y), line, font=bf, fill=(201, 213, 225)); y += 38
    d.text((90, H - 100), footer, font=f('ShareTechMono-400.ttf', 18), fill=(130, 145, 160))
    return img.convert('RGB')


def build(course, tmp):
    cat = course['category']
    n = len(course['chapters'])
    slides = [slide(cat, course['code'] + ' // BRIEFING', course['title'], course['subtitle'], 'Briefing visuel sans narration · lisez chaque écran')]
    for i, ch in enumerate(course['chapters']):
        if ch['title'].lower().startswith('atelier'):
            continue
        slides.append(slide(cat, f'CHAPITRE {i + 1:02d} / {n:02d}', ch['title'], ch['summary'], course['code']))
    slides.append(slide(cat, 'CADRE ÉTHIQUE', 'Légalité · Proportionnalité · Respect des personnes',
                        "Toutes les pratiques enseignées sont légales et défensives. Les exercices utilisent des données synthétiques et des scénarios fictifs.", course['code']))
    slides.append(slide(cat, 'FIN DU BRIEFING', 'Votre parcours commence', "Poursuivez avec la première leçon, puis validez chaque chapitre par son évaluation QCM.", 'AFRICA\'SPY · Intelligence Academy'))
    paths = []
    for i, s in enumerate(slides):
        p = os.path.join(tmp, f's{i:02d}.png'); s.save(p); paths.append(p)
    # Un clip par écran avec lent zoom (zoompan), puis enchaînement par fondus (xfade)
    inputs, filters = [], []
    frames = int(SLIDE * 25)
    for i, p in enumerate(paths):
        inputs += ['-loop', '1', '-t', str(SLIDE), '-i', p]
        filters.append(f"[{i}:v]scale=960:540,fps=25,format=yuv420p,setsar=1[v{i}]")
    last = 'v0'
    offset = SLIDE - FADE
    for i in range(1, len(paths)):
        out = f'x{i}'
        filters.append(f'[{last}][v{i}]xfade=transition=fade:duration={FADE}:offset={offset:.2f}[{out}]')
        last = out
        offset += SLIDE - FADE
    dest = os.path.join(OUT, f"seed-{course['code'].lower()}.mp4")
    cmd = ['ffmpeg', '-y', '-loglevel', 'error'] + inputs + ['-filter_complex', ';'.join(filters), '-map', f'[{last}]',
           '-c:v', 'libx264', '-preset', 'medium', '-crf', '28', '-tune', 'stillimage', '-pix_fmt', 'yuv420p', '-movflags', '+faststart', dest]
    subprocess.run(cmd, check=True)
    print('→', os.path.basename(dest), os.path.getsize(dest) // 1024, 'Ko')


if __name__ == '__main__':
    data = json.load(open(sys.argv[1]))
    os.makedirs(OUT, exist_ok=True)
    only = sys.argv[2] if len(sys.argv) > 2 else None
    for c in data['courses']:
        if only and c['code'] != only:
            continue
        with tempfile.TemporaryDirectory() as tmp:
            build(c, tmp)
