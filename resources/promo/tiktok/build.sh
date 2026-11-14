#!/usr/bin/env bash
# Régénère le spot TikTok vertical : docs/promo/africaspy-tiktok-9x16.mp4 (1080 x 1920, 30 i/s)
# Prérequis : node + playwright, python3 + numpy + piper-tts, ffmpeg, php.
set -euo pipefail
cd "$(dirname "$0")"
ROOT=../../..; B=../build/tiktok; mkdir -p $B/img $B/frames ../build/voices
VOICE=../build/voices/fr_FR-siwis-medium.onnx
URL=https://huggingface.co/rhasspy/piper-voices/resolve/main/fr/fr_FR/siwis/medium/fr_FR-siwis-medium.onnx
[ -f "$VOICE" ] || { curl -fsSL -o "$VOICE" "$URL"; curl -fsSL -o "$VOICE.json" "$URL.json"; }
cp tiktok.html $B/; cp $ROOT/assets/fonts/*.woff2 $B/img/
for c in A B C D E F G; do cp $ROOT/assets/images/covers/$c.webp $B/img/cover-$c.webp; done
cp $ROOT/docs/screenshots/23-mobile-accueil.jpg $B/img/
php -r 'require "'$ROOT'/includes/bootstrap.php"; require_once "'$ROOT'/includes/logo.php"; echo logo_svg("holo","logo-mark",true);' > $B/img/logo.svg
php -r 'require "'$ROOT'/includes/bootstrap.php"; require_once "'$ROOT'/includes/content.php"; echo africa_map_svg(false);' > $B/img/africa.svg
python3 script.py "$VOICE" $B             # voix off, sous-titres, chronologie
python3 music.py $B/timeline.json $B/music.wav
DUR=$(python3 -c "import json;print(json.load(open('$B/timeline.json'))['duration'])")
(cd ../build && python3 -m http.server 8092 --bind 127.0.0.1 >/dev/null 2>&1) & SRV=$!; trap 'kill $SRV' EXIT; sleep 1
find $B/frames -name '*.jpg' -delete
PAGE=http://127.0.0.1:8092/tiktok/tiktok.html DURATION=$DUR OUT=$B/frames VW=1080 VH=1920 node ../render.js
ffmpeg -v error -y -i $B/voice-raw.wav -af "highpass=f=90,equalizer=f=3200:t=q:w=1.2:g=3,equalizer=f=250:t=q:w=1:g=-1.5,acompressor=threshold=-20dB:ratio=3.5:attack=5:release=110:makeup=3,aecho=0.8:0.5:24:0.08,aresample=48000,pan=stereo|c0=c0|c1=c0" $B/voice.wav
ffmpeg -v error -y -i $B/music.wav -i $B/voice.wav -filter_complex "[1:a]asplit=2[vk][vm];[0:a]volume=0.85[mu];[mu][vk]sidechaincompress=threshold=0.03:ratio=6:attack=25:release=350:makeup=1[duck];[duck][vm]amix=inputs=2:weights=0.7 1.3:normalize=0,loudnorm=I=-14:TP=-1.5:LRA=9[a]" -map "[a]" -ar 48000 $B/mix.wav
ffmpeg -v error -y -framerate 30 -i $B/frames/%05d.jpg -i $B/mix.wav -map 0:v -map 1:a -c:v libx264 -preset slow -crf 20 -pix_fmt yuv420p -profile:v high -c:a aac -b:a 192k -ar 48000 -movflags +faststart -shortest $ROOT/docs/promo/africaspy-tiktok-9x16.mp4
ffmpeg -v error -y -ss 2.6 -i $ROOT/docs/promo/africaspy-tiktok-9x16.mp4 -frames:v 1 -q:v 2 $ROOT/docs/promo/africaspy-tiktok-couverture.jpg
cp $B/texte.txt $ROOT/docs/promo/africaspy-tiktok-texte.txt
echo "Vidéo : docs/promo/africaspy-tiktok-9x16.mp4"
