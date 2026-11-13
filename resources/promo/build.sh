#!/usr/bin/env bash
# Régénère le spot publicitaire : docs/promo/africaspy-spot-16x9.mp4
# Prérequis : node + playwright (Chromium), python3 + numpy, ffmpeg.
set -euo pipefail
cd "$(dirname "$0")"
ROOT=../..
rm -rf build && mkdir -p build/img build/frames
cp spot.html build/
cp $ROOT/assets/fonts/*.woff2 build/img/
for f in 01-accueil-hero 11-lecteur-de-cours 12-qcm-corrige 13-examen-chronometre 14-resultat-examen 17-certificat; do cp $ROOT/docs/screenshots/$f.jpg build/img/; done
python3 - <<'PY'
from PIL import Image
D='../../docs/screenshots/'
a=Image.open(D+'23-mobile-accueil.jpg'); b=Image.open(D+'24-mobile-offre.jpg')
c=Image.new('RGB',(1170,a.height+b.height),(5,7,11)); c.paste(a,(0,0)); c.paste(b,(0,a.height)); c.save('build/img/mobile-long.jpg',quality=90)
PY
php -r 'require "'$ROOT'/includes/bootstrap.php"; require_once "'$ROOT'/includes/logo.php"; echo logo_svg("holo","logo-mark",true);' > build/img/logo.svg
python3 music.py
python3 -m http.server 8090 --bind 127.0.0.1 >/dev/null 2>&1 & SRV=$!
trap 'kill $SRV' EXIT
sleep 1
node render.js
ffmpeg -v error -y -framerate 30 -i build/frames/%05d.jpg -i build/music.wav \
  -filter_complex "[1:a]loudnorm=I=-15:TP=-1.5:LRA=11[a]" -map 0:v -map "[a]" \
  -c:v libx264 -preset slow -crf 19 -pix_fmt yuv420p -tune animation -c:a aac -b:a 192k -movflags +faststart -shortest \
  $ROOT/docs/promo/africaspy-spot-16x9.mp4
echo "Vidéo : docs/promo/africaspy-spot-16x9.mp4"

# ---- Voix off française (Piper, voix « siwis », CC BY 4.0) : pip install piper-tts
if python3 -c "import piper" 2>/dev/null; then
  VOICE=build/voices/fr_FR-siwis-medium.onnx
  URL=https://huggingface.co/rhasspy/piper-voices/resolve/main/fr/fr_FR/siwis/medium/fr_FR-siwis-medium.onnx
  mkdir -p build/voices
  [ -f "$VOICE" ] || { curl -fsSL -o "$VOICE" "$URL"; curl -fsSL -o "$VOICE.json" "$URL.json"; }
  python3 voiceover.py "$VOICE" build/voice-raw.wav
  ffmpeg -v error -y -i build/voice-raw.wav -af "highpass=f=90,equalizer=f=3200:t=q:w=1.2:g=2.5,equalizer=f=250:t=q:w=1:g=-1.5,acompressor=threshold=-20dB:ratio=3:attack=5:release=120:makeup=3,aecho=0.8:0.5:28:0.12,aresample=48000,pan=stereo|c0=c0|c1=c0" build/voice.wav
  ffmpeg -v error -y -i build/voice.wav -af loudnorm=I=-16:TP=-1.5:LRA=9 -ar 48000 $ROOT/docs/promo/africaspy-voix-off.wav
  ffmpeg -v error -y -i $ROOT/docs/promo/africaspy-voix-off.wav -c:a libmp3lame -b:a 192k $ROOT/docs/promo/africaspy-voix-off.mp3
  # Musique abaissée automatiquement sous la voix (compression par la voix)
  ffmpeg -v error -y -i build/music.wav -i build/voice.wav -filter_complex \
    "[1:a]asplit=2[vk][vm];[0:a]volume=0.9[mu];[mu][vk]sidechaincompress=threshold=0.03:ratio=6:attack=30:release=450:makeup=1[duck];[duck][vm]amix=inputs=2:weights=0.75 1.25:normalize=0,loudnorm=I=-15:TP=-1.5:LRA=11[a]" \
    -map "[a]" -ar 48000 build/mix.wav
  ffmpeg -v error -y -i build/mix.wav -c:a libmp3lame -b:a 192k $ROOT/docs/promo/africaspy-spot-audio.mp3
  ffmpeg -v error -y -i $ROOT/docs/promo/africaspy-spot-16x9.mp4 -i build/mix.wav -map 0:v -map 1:a -c:v copy -c:a aac -b:a 192k -movflags +faststart -shortest $ROOT/docs/promo/africaspy-spot-16x9-voix.mp4
  echo "Avec voix off : docs/promo/africaspy-spot-16x9-voix.mp4 (+ voix seule et bande-son mixée en MP3)"
else
  echo "Voix off ignorée : installez piper-tts (pip install piper-tts) pour la générer."
fi
