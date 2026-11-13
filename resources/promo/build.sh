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
