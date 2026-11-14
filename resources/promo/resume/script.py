"""Vidéo de synthèse AFRICA'SPY : contenu, voix off et chronologie.
Chaque élément affiché apparaît au moment où la voix le prononce : la chronologie (timeline.json)
est calculée à partir de la durée réelle de chaque réplique synthétisée.
Usage : python3 script.py <modele_piper.onnx> <dossier_sortie>
Règles de contenu : résultats présentés comme attendus (non mesurés), aucun partenariat ni aucune
reconnaissance officielle revendiqués, pratiques exclusivement légales."""
import sys, os, json, wave
import numpy as np
sys.path.insert(0, os.path.join(os.path.dirname(os.path.abspath(__file__)), '..'))
from voiceover import synth  # même moteur et mêmes réglages que le spot
from piper import PiperVoice

# Chaque scène : type de mise en page, titres, puis répliques.
# Une réplique : (texte lu, texte affiché ou None). Graphies phonétiques vérifiées par transcription.
SCENES = [
    {'id': 'titre', 'layout': 'title', 'eyebrow': 'Synthèse du projet', 'title': "AFRICA'SPY",
     'subtitle': 'Objectifs, résultats attendus et avantages pour les États africains et leurs professionnels du renseignement',
     'lines': [("Afrika Spaï, Intelligence Academy.", None),
               ("Synthèse du projet : objectifs, résultats attendus, et avantages pour les États africains et leurs professionnels du renseignement.", None)]},

    {'id': 'constat', 'layout': 'list', 'eyebrow': '01 | Le constat', 'title': 'Des menaces nouvelles, une formation rare', 'image': 'img/04-afrique-holographique.jpg',
     'lines': [("Les États africains font face à des menaces nouvelles : cybermenaces, désinformation, ingérences économiques.", "Cybermenaces, désinformation, ingérences économiques"),
               ("Or la formation spécialisée reste rare, coûteuse, et souvent éloignée des réalités du continent.", "Une formation spécialisée rare, coûteuse, éloignée des réalités africaines")]},

    {'id': 'objectifs', 'layout': 'list', 'eyebrow': '02 | Objectifs visés', 'title': 'Cinq objectifs',
     'lines': [("Premier objectif : former des analystes capables de transformer l'information en aide à la décision.", "Former des analystes qui transforment l'information en aide à la décision"),
               ("Deuxième objectif : diffuser une culture du renseignement fondée sur le droit et l'éthique.", "Diffuser une culture du renseignement fondée sur le droit et l'éthique"),
               ("Troisième objectif : renforcer la protection des institutions, grâce au contre-espionnage défensif et à la sécurité de l'information.", "Renforcer la protection des institutions : contre-espionnage défensif, sécurité de l'information"),
               ("Quatrième objectif : rendre cette formation accessible, en ligne, en français, à un coût maîtrisé.", "Rendre la formation accessible : en ligne, en français, à coût maîtrisé"),
               ("Cinquième objectif : attester les compétences par un certificat vérifiable.", "Attester les compétences par un certificat vérifiable")]},

    {'id': 'dispositif', 'layout': 'stats', 'eyebrow': '03 | Le dispositif', 'title': 'Une académie complète, en ligne', 'image': 'img/11-lecteur-de-cours.jpg',
     'stats': [['7', 'domaines'], ['13', 'formations'], ['217', 'leçons'], ['276', 'questions de QCM']],
     'lines': [("Le dispositif : sept domaines d'expertise, dont le renseignement militaire, treize formations, deux cent dix-sept leçons et deux cent soixante-seize questions d'évaluation.", None),
               ("Chaque formation combine leçons, mises en situation, travaux corrigés par des formateurs, et un examen final chronométré.", "Leçons, mises en situation, travaux corrigés, examen final")]},

    {'id': 'resultats', 'layout': 'list', 'eyebrow': '04 | Résultats attendus', 'title': 'Ce que la formation doit produire', 'note': 'Résultats visés, à mesurer lors du déploiement',
     'lines': [("Les résultats attendus sont les suivants.", None),
               ("Des professionnels formés à une méthode commune : cycle du renseignement, évaluation des sources, analyse structurée.", "Une méthode commune : cycle du renseignement, évaluation des sources, analyse structurée"),
               ("Des notes et des synthèses plus fiables pour les décideurs.", "Des notes et synthèses plus fiables pour les décideurs"),
               ("Une meilleure détection des menaces : ingérences, ingénierie sociale, fuites d'information.", "Une meilleure détection des ingérences, de l'ingénierie sociale et des fuites"),
               ("Des compétences attestées, et vérifiables en ligne.", "Des compétences attestées et vérifiables en ligne")]},

    {'id': 'etats', 'layout': 'list', 'eyebrow': '05 | Avantages pour les États', 'title': 'Pour les États africains', 'accent': 'gold',
     'lines': [("Pour les États, les avantages sont concrets.", None),
               ("La souveraineté : former ses propres analystes, sur des cas africains.", "Souveraineté : former ses propres analystes, sur des cas africains"),
               ("Un coût maîtrisé et un déploiement rapide, sans déplacement, depuis tout le continent.", "Coût maîtrisé, déploiement rapide, sans déplacement"),
               ("Une doctrine commune, respectueuse de l'État de droit.", "Une doctrine commune, respectueuse de l'État de droit"),
               ("Et un suivi précis : progression, résultats, certificats.", "Un suivi précis : progression, résultats, certificats")]},

    {'id': 'agents', 'layout': 'list', 'eyebrow': '06 | Avantages pour les agents', 'title': 'Pour les agents et analystes', 'image': 'img/13-examen-chronometre.jpg', 'accent': 'turq',
     'lines': [("Pour les agents et les analystes :", None),
               ("une formation à leur rythme, compatible avec leurs missions ;", "Une formation à leur rythme, compatible avec leurs missions"),
               ("des méthodes concrètes : recherche en sources ouvertes, rédaction de notes, protection des sources ;", "Des méthodes concrètes : sources ouvertes, notes d'analyse, protection des sources"),
               ("des mises en situation réalistes, sur des scénarios fictifs ;", "Des mises en situation réalistes, sur scénarios fictifs"),
               ("et un certificat qui valorise leurs compétences.", "Un certificat qui valorise leurs compétences")]},

    {'id': 'ethique', 'layout': 'list', 'eyebrow': '07 | Cadre éthique', 'title': 'Un cadre strictement légal', 'accent': 'green',
     'lines': [("Le tout dans un cadre strictement légal :", None),
               ("aucune technique de surveillance illégale ;", "Aucune technique de surveillance illégale"),
               ("des exercices réalisés sur des données fictives ;", "Des exercices sur données fictives"),
               ("et un certificat d'établissement privé, qui n'est pas un diplôme d'État.", "Un certificat d'établissement privé, non assimilé à un diplôme d'État")]},

    {'id': 'fin', 'layout': 'closing',
     'lines': [("Comprendre. Anticiper. Protéger.", None),
               ("Afrika Spaï. Former les analystes dont l'Afrique a besoin.", None)]},
]

GAP, SCENE_GAP, LEAD, START = 0.42, 1.0, 0.35, 0.9

def main(model, out):
    os.makedirs(out, exist_ok=True)
    voice = PiperVoice.load(model)
    clips, t = [], START
    for sc in SCENES:
        sc['start'] = round(t - LEAD, 3)
        items = []
        for spoken, shown in sc['lines']:
            a, sr = synth(voice, spoken, 1.0)
            clips.append((t, a)); d = len(a) / sr
            items.append({'t': round(t, 3), 'd': round(d, 3), 'text': shown})
            print(f"{t:7.2f} s  {d:5.2f} s  {spoken}")
            t += d + GAP
        sc['items'] = items
        t += SCENE_GAP
        sc['end'] = round(t - 0.25, 3)
    total = round(t + 1.6, 2)
    track = np.zeros(int(total * sr), dtype=np.float32)
    for start, a in clips:
        s = int(start * sr); track[s:s + len(a)] += a
    track = track / max(1e-6, np.abs(track).max()) * 0.9
    with wave.open(os.path.join(out, 'voice-raw.wav'), 'wb') as w:
        w.setnchannels(1); w.setsampwidth(2); w.setframerate(sr); w.writeframes((track * 32767).astype('<i2').tobytes())
    tl = {'duration': total, 'scenes': [{k: v for k, v in sc.items() if k != 'lines'} for sc in SCENES]}
    with open(os.path.join(out, 'timeline.json'), 'w', encoding='utf-8') as f:
        json.dump(tl, f, ensure_ascii=False, indent=1)
    with open(os.path.join(out, 'texte.txt'), 'w', encoding='utf-8') as f:
        for sc, (start, _) in zip([s for s in SCENES for _ in s['lines']], clips):
            pass
        for sc in SCENES:
            for (spoken, _), it in zip(sc['lines'], sc['items']):
                m, s = divmod(int(it['t']), 60)
                f.write(f"{m:02d}:{s:02d}  {spoken.replace('Afrika Spaï', 'AFRICA' + chr(39) + 'SPY').replace('Q R code', 'QR code')}\n")
            f.write('\n')
    print(f"Durée totale : {total} s")

if __name__ == '__main__':
    main(sys.argv[1], sys.argv[2])
