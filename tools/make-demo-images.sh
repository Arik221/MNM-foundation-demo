#!/usr/bin/env bash
# Generates soft, out-of-focus *photographic-style* placeholders (duotone atmosphere + grain + vignette).
# They are stand-ins only: replace with real photographs via the Media Library or assets/demo/photos/.
set -euo pipefail
OUT="$(cd "$(dirname "$0")/.." && pwd)/mnm-foundation-core/assets/demo"
FONT=/usr/share/fonts/truetype/dejavu/DejaVuSans-Bold.ttf
mk() { # name W H seed dark light
  local n=$1 w=$2 h=$3 s=$4 d=$5 l=$6
  convert -seed "$s" -size "${w}x${h}" plasma:fractal -blur 0x$((w/60)) -colorspace Gray -auto-level -gamma 0.85 -colorspace sRGB -type TrueColor \
    +level-colors "$d,$l" \
    \( -size "${w}x${h}" radial-gradient:"rgba(255,255,255,0.18)-rgba(0,0,0,0.55)" \) -compose overlay -composite \
    -attenuate 0.22 +noise Gaussian -quality 80 \
    -gravity southwest -font "$FONT" -pointsize $((h/46+8)) -fill 'rgba(255,255,255,0.75)' -annotate +24+22 'PHOTO PLACEHOLDER' \
    "$OUT/$n"
  echo "$n"
}
T=('#04202a #7fd0c8' '#3a1410 #ffb199' '#3b2a07 #ffd98a' '#0b2a3d #9bd3ee' '#08241b #8fd6a9' '#0f2f3a #bfe7dd')
mk marathon-banner-demo.jpg 1800 800 11 '#3a1410' '#ffb199'
mk campaign-demo.jpg 1200 800 12 '#08241b' '#8fd6a9'
mk programme-education-demo.jpg 1200 800 13 '#0b2a3d' '#9bd3ee'
mk programme-digital-demo.jpg 1200 800 14 '#04202a' '#7fd0c8'
mk programme-community-demo.jpg 1200 800 15 '#3b2a07' '#ffd98a'
i=0; for spec in "900 1200" "1200 800" "1000 1000" "1200 800" "900 1200" "1200 900" "1200 800" "1000 1250" "1200 800" "1000 1000" "1200 800" "900 1200"; do
  i=$((i+1)); set -- $spec; p=(${T[$((i%6))]}); mk "gallery-$(printf %02d $i)-demo.jpg" "$1" "$2" $((20+i)) "${p[0]}" "${p[1]}"
done
