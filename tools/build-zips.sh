#!/usr/bin/env bash
# Builds installable ZIPs into dist/. Usage: bash tools/build-zips.sh
set -euo pipefail
cd "$(dirname "$0")/.."; mkdir -p dist; rm -f dist/*.zip
for d in mnm-foundation-theme mnm-foundation-core; do zip -qr "dist/$d.zip" "$d" -x '*.DS_Store' -x '*/.git/*'; done
ls -la dist
