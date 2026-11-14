#!/usr/bin/env bash
# Régénère la vidéo de synthèse : docs/promo/africaspy-synthese-16x9.mp4
# Prérequis : node + playwright, python3 + numpy + piper-tts, ffmpeg, php.
set -euo pipefail
cd "$(dirname "$0")"
ROOT=../../..; B=../build/resume; mkdir -p $B/img $B/frames ../build/voices
VOICE=../build/voices/fr_FR-siwis-medium.onnx
URL=https://huggingface.co/rhasspy/piper-voices/resolve/main/fr/fr_FR/siwis/medium/fr_FR-siwis-medium.onnx
[ -f "$VOICE" ] || { curl -fsSL -o "$VOICE" "$URL"; curl -fsSL -o "$VOICE.json" "$URL.json"; }
cp resume.html $B/; cp $ROOT/assets/fonts/*.woff2 $B/img/
for f in 04-afrique-holographique 11-lecteur-de-cours 13-examen-chronometre; do cp $ROOT/docs/screenshots/$f.jpg $B/img/; done
php -r 'require "'$ROOT'/includes/bootstrap.php"; require_once "'$ROOT'/includes/logo.php"; echo logo_svg("holo","logo-mark",true);' > $B/img/logo.svg
python3 script.py "$VOICE" $B          # voix off + chronologie (timeline.json)
python3 music.py $B/timeline.json $B/music.wav
DUR=$(python3 -c "import json;print(json.load(open('$B/timeline.json'))['duration'])")
(cd ../build && python3 -m http.server 8091 --bind 127.0.0.1 >/dev/null 2>&1) & SRV=$!; trap 'kill $SRV' EXIT; sleep 1
rm -f $B/frames/*.jpg
PAGE=http://127.0.0.1:8091/resume/resume.html DURATION=$DUR OUT=$B/frames node ../render.js
ffmpeg -v error -y -i $B/voice-raw.wav -af "highpass=f=90,equalizer=f=3200:t=q:w=1.2:g=2.5,equalizer=f=250:t=q:w=1:g=-1.5,acompressor=threshold=-20dB:ratio=3:attack=5:release=120:makeup=3,aecho=0.8:0.5:28:0.12,aresample=48000,pan=stereo|c0=c0|c1=c0" $B/voice.wav
ffmpeg -v error -y -i $B/music.wav -i $B/voice.wav -filter_complex "[1:a]asplit=2[vk][vm];[0:a]volume=0.8[mu];[mu][vk]sidechaincompress=threshold=0.03:ratio=7:attack=30:release=500:makeup=1[duck];[duck][vm]amix=inputs=2:weights=0.6 1.3:normalize=0,loudnorm=I=-16:TP=-1.5:LRA=11[a]" -map "[a]" -ar 48000 $B/mix.wav
ffmpeg -v error -y -framerate 30 -i $B/frames/%05d.jpg -i $B/mix.wav -map 0:v -map 1:a -c:v libx264 -preset slow -crf 20 -pix_fmt yuv420p -tune animation -c:a aac -b:a 160k -movflags +faststart -shortest $ROOT/docs/promo/africaspy-synthese-16x9.mp4
ffmpeg -v error -y -i $B/voice.wav -af loudnorm=I=-16:TP=-1.5 -c:a libmp3lame -b:a 160k $ROOT/docs/promo/africaspy-synthese-voix-off.mp3
cp $B/texte.txt $ROOT/docs/promo/africaspy-synthese-texte.txt
echo "Vidéo : docs/promo/africaspy-synthese-16x9.mp4"
