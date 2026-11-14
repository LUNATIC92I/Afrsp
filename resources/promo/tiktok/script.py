"""Spot TikTok AFRICA'SPY (vertical 1080 x 1920) : contenu, voix off, sous-titres et chronologie.
La voix enchaîne les phrases avec des pauses courtes (débit naturel) ; la chronologie (timeline.json)
découle de la durée réelle de chaque phrase, et les sous-titres sont découpés en segments courts.
Usage : python3 script.py <modele_piper.onnx> <dossier_sortie>"""
import sys, os, json, wave, re
import numpy as np
sys.path.insert(0, os.path.join(os.path.dirname(os.path.abspath(__file__)), '..'))
from voiceover import synth
from piper import PiperVoice

# Chaque réplique : (texte lu, sous-titre). Graphies phonétiques vérifiées par transcription automatique.
SCENES = [
    {'id': 'hook', 'layout': 'hook',
     'lines': [("Et si vous deveniez l'analyste dont l'Afrique a besoin ?", "Et si vous deveniez l'analyste dont l'Afrique a besoin ?")]},
    {'id': 'promesse', 'layout': 'promise',
     'lines': [("Cybermenaces, désinformation, ingérences : le continent a besoin de professionnels formés.", "Cybermenaces, désinformation, ingérences : le continent a besoin de professionnels formés."),
               ("Avec Afrika Spaï, formez-vous en ligne, en français, et à votre rythme.", "Avec AFRICA'SPY, formez-vous en ligne, en français, et à votre rythme."),
               ("Sept modules vous attendent.", "7 modules vous attendent.")]},
    {'id': 'A', 'layout': 'module', 'name': 'Fondements du renseignement', 'n': 2, 'accent': '#00C8FF', 'points': ['Cycle du renseignement', 'Analyse structurée, sans biais'],
     'lines': [("Les fondements : maîtrisez le cycle du renseignement et l'analyse rigoureuse.", "Les fondements : maîtrisez le cycle du renseignement et l'analyse rigoureuse.")]},
    {'id': 'B', 'layout': 'module', 'name': 'Renseignement humain', 'n': 2, 'accent': '#D5B36A', 'points': ['Entretien et écoute active', 'Protection des sources'],
     'lines': [("Le renseignement humain : menez des entretiens et protégez vos sources, dans le respect du droit.", "Le renseignement humain : menez des entretiens et protégez vos sources, dans le respect du droit.")]},
    {'id': 'C', 'layout': 'module', 'name': 'Contre-espionnage', 'n': 2, 'accent': '#FF4057', 'points': ['Menaces internes et ingérences', 'Détection de la manipulation'],
     'lines': [("Le contre-espionnage : protégez votre organisation contre les ingérences et la manipulation.", "Le contre-espionnage : protégez votre organisation contre les ingérences et la manipulation.")]},
    {'id': 'D', 'layout': 'module', 'name': 'Renseignement numérique', 'n': 2, 'accent': '#865DFF', 'points': ['Sources ouvertes (OSINT)', 'Analyse des cybermenaces'],
     'lines': [("Le renseignement numérique : recherche en sources ouvertes et analyse des cybermenaces.", "Le renseignement numérique : recherche en sources ouvertes et analyse des cybermenaces.")]},
    {'id': 'E', 'layout': 'module', 'name': 'Outils du renseignement', 'n': 1, 'accent': '#00F0D0', 'points': ['Logiciels et équipements', 'Usages légitimes, cadre légal'],
     'lines': [("Les outils : prenez en main les outils de l'analyste, et leur cadre légal.", "Les outils : prenez en main les outils de l'analyste, et leur cadre légal.")]},
    {'id': 'F', 'layout': 'module', 'name': 'Intelligence stratégique', 'n': 2, 'accent': '#1AFF9C', 'points': ['Géopolitique africaine', 'Risques pays et veille'],
     'lines': [("L'intelligence stratégique : décryptez la géopolitique africaine et les risques pays.", "L'intelligence stratégique : décryptez la géopolitique africaine et les risques pays.")]},
    {'id': 'G', 'layout': 'module', 'name': 'Renseignement militaire', 'n': 2, 'accent': '#087CFF', 'points': ['Appui au commandement', 'Protection des civils'],
     'lines': [("Le renseignement militaire : éclairez la décision du commandement, et contribuez à protéger les civils.", "Le renseignement militaire : éclairez la décision du commandement, et contribuez à protéger les civils.")]},
    {'id': 'atouts', 'layout': 'features',
     'lines': [("Chaque formation comprend des leçons approfondies, des mises en situation réalistes, et des travaux corrigés par des formateurs.", "Chaque formation comprend des leçons approfondies, des mises en situation réalistes, et des travaux corrigés par des formateurs."),
               ("Puis un examen final, et un certificat vérifiable en ligne par Q R code.", "Puis un examen final, et un certificat vérifiable en ligne par QR code.")]},
    {'id': 'methodes', 'layout': 'agencies',
     'lines': [("Nos méthodes sont étudiées à travers l'histoire des grands services, comme la cé i a, le ka gé bé ou le Mossad,", "Nos méthodes sont étudiées à travers l'histoire des grands services, comme la CIA, le KGB ou le Mossad,"),
               ("puis adaptées aux réalités africaines et à l'État de droit.", "puis adaptées aux réalités africaines et à l'État de droit.")]},
    {'id': 'afrique', 'layout': 'africa',
     'lines': [("Pour nos pays, c'est un enjeu de souveraineté :", "Pour nos pays, c'est un enjeu de souveraineté :"),
               ("des compétences africaines, formées aux réalités du continent, pour des institutions mieux protégées.", "des compétences africaines, formées aux réalités du continent, pour des institutions mieux protégées.")]},
    {'id': 'offre', 'layout': 'offer',
     'lines': [("En ce moment, c'est l'offre de lancement : cent soixante-dix mille francs, cé effe a, au lieu de neuf cent mille.", "En ce moment, c'est l'offre de lancement : 170 000 FCFA au lieu de 900 000."),
               ("Payable par Mobile Mo-ni, carte ou virement.", "Payable par Mobile Money, carte ou virement.")]},
    {'id': 'cta', 'layout': 'cta',
     'lines': [("Inscrivez-vous maintenant, et commencez votre formation dès aujourd'hui.", "Inscrivez-vous maintenant, et commencez votre formation dès aujourd'hui."),
               ("Afrika Spaï. Comprendre, anticiper, protéger.", "AFRICA'SPY. Comprendre, anticiper, protéger.")]},
]

GAP, SCENE_GAP, LEAD, START, SCALE = 0.20, 0.38, 0.32, 0.45, 0.93

def chunks(text, maxc=34):
    """Découpe un sous-titre en segments courts (2 lignes maximum à l'écran)."""
    words, out, cur = text.split(), [], ''
    for w in words:
        if cur and len(cur) + 1 + len(w) > maxc:
            out.append(cur); cur = w
        else:
            cur = (cur + ' ' + w).strip()
        if re.search(r'[.:?;,]$', w) and len(cur) > 18:
            out.append(cur); cur = ''
    if cur: out.append(cur)
    return out

def main(model, out):
    os.makedirs(out, exist_ok=True)
    voice = PiperVoice.load(model)
    clips, t, caps = [], START, []
    for sc in SCENES:
        sc['start'] = round(t - LEAD, 3); items = []
        for spoken, caption in sc['lines']:
            a, sr = synth(voice, spoken, SCALE)
            d = len(a) / sr; clips.append((t, a))
            items.append({'t': round(t, 3), 'd': round(d, 3)})
            # Sous-titres : temps réparti selon la longueur de chaque segment
            parts = chunks(caption); total = sum(len(p) for p in parts); c0 = t
            for p in parts:
                dur = d * len(p) / total
                caps.append({'t': round(c0, 3), 'e': round(c0 + dur, 3), 'text': p}); c0 += dur
            print(f"{t:6.2f} s  {d:5.2f} s  {spoken}")
            t += d + GAP
        sc['items'] = items
        t += SCENE_GAP - GAP
        sc['end'] = round(t, 3)
    total = round(t + 1.4, 2)
    track = np.zeros(int(total * sr), dtype=np.float32)
    for start, a in clips:
        s = int(start * sr); track[s:s + len(a)] += a
    track = track / max(1e-6, np.abs(track).max()) * 0.9
    with wave.open(os.path.join(out, 'voice-raw.wav'), 'wb') as w:
        w.setnchannels(1); w.setsampwidth(2); w.setframerate(sr); w.writeframes((track * 32767).astype('<i2').tobytes())
    tl = {'duration': total, 'captions': caps, 'scenes': [{k: v for k, v in sc.items() if k != 'lines'} for sc in SCENES]}
    json.dump(tl, open(os.path.join(out, 'timeline.json'), 'w', encoding='utf-8'), ensure_ascii=False, indent=1)
    with open(os.path.join(out, 'texte.txt'), 'w', encoding='utf-8') as f:
        f.write("AFRICA'SPY : texte de la voix off du spot TikTok (vertical 9:16)\n\n")
        for sc in SCENES:
            for (_, cap), it in zip(sc['lines'], sc['items']):
                m, s = divmod(int(it['t']), 60); f.write(f"{m:02d}:{s:02d}  {cap}\n")
            f.write('\n')
    print(f"Durée totale : {total} s, {len(caps)} sous-titres")

if __name__ == '__main__':
    main(sys.argv[1], sys.argv[2])
