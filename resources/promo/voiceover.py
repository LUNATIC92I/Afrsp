"""Voix off française du spot (synthèse Piper, voix « siwis », CC BY 4.0).
Chaque réplique est calée sur sa scène ; si elle dépasse sa fenêtre, le débit est accéléré.
Usage : python3 voiceover.py <modele.onnx> <sortie.wav>"""
import sys, wave, io, numpy as np
from piper import PiperVoice
from piper.config import SynthesisConfig

# (début en secondes, durée maximale, texte) : nombres en toutes lettres et graphies phonétiques
# (« Afrika Spaï », « Mo-ni », « cé effe a ») vérifiées par transcription automatique
LINES = [
    (1.30, 3.4, "Afrika Spaï. Intelligence Academy."),
    (5.25, 1.15, "Comprendre."),
    (6.45, 1.15, "Anticiper."),
    (7.65, 1.25, "Protéger."),
    (9.10, 5.9, "Former les analystes dont l'Afrique a besoin : une académie, en ligne, rigoureuse et éthique."),
    (15.6, 5.0, "Sept domaines d'expertise. Treize formations complètes."),
    (21.1, 5.0, "Des leçons approfondies, des études de cas, des questionnaires corrigés."),
    (26.7, 4.8, "Un examen final chronométré, corrigé immédiatement."),
    (32.2, 4.8, "Et un certificat vérifiable en ligne, par Q R code."),
    (37.6, 4.6, "Payez par Mobile Mo-ni, carte ou virement. Votre compte est protégé."),
    (42.6, 1.6, "Offre de lancement :"),
    (44.25, 3.95, "cent soixante-dix mille francs, cé effe a, au lieu de neuf cent mille."),
    (48.3, 4.2, "Afrika Spaï. Inscrivez-vous dès aujourd'hui."),
]
DURATION = 53.0

def synth(voice, text, scale):
    buf = io.BytesIO()
    with wave.open(buf, 'wb') as w:
        voice.synthesize_wav(text, w, syn_config=SynthesisConfig(length_scale=scale, noise_scale=0.6, noise_w_scale=0.7))
    buf.seek(0)
    with wave.open(buf) as r:
        sr = r.getframerate(); a = np.frombuffer(r.readframes(r.getnframes()), dtype='<i2').astype(np.float32) / 32768
    # Coupe les silences de début et de fin
    nz = np.where(np.abs(a) > 0.01)[0]
    return (a[max(0, nz[0] - int(.02 * sr)):nz[-1] + int(.08 * sr)] if len(nz) else a), sr

def main(model, out):
    voice = PiperVoice.load(model)
    track = None
    for start, maxd, text in LINES:
        scale = 1.0
        clip, sr = synth(voice, text, scale)
        while len(clip) / sr > maxd and scale > 0.72:
            scale -= 0.04; clip, sr = synth(voice, text, scale)
        if track is None: track = np.zeros(int(DURATION * sr), dtype=np.float32)
        s = int(start * sr); track[s:s + len(clip)] += clip[:len(track) - s]
        print(f"{start:6.2f} s  {len(clip)/sr:4.2f} s / {maxd:.2f} s  débit x{1/scale:.2f}  {text}")
    track = track / max(1e-6, np.abs(track).max()) * 0.9
    with wave.open(out, 'wb') as w:
        w.setnchannels(1); w.setsampwidth(2); w.setframerate(sr); w.writeframes((track * 32767).astype('<i2').tobytes())

if __name__ == '__main__':
    main(sys.argv[1], sys.argv[2])
