#!/usr/bin/env bash
# Rebuilds demo-content.xml and every ZIP in dist/.   Usage: bash tools/build-package.sh
set -euo pipefail
cd "$(dirname "$0")/.."
ROOT=$(pwd)
php tools/build-wxr.php > demo-content/demo-content.xml
rm -rf dist && mkdir -p dist build
rm -rf build/* 
( cd theme  && zip -qr -X "$ROOT/dist/ahcene-babori-academic-theme.zip" ahcene-babori-academic -x '*.DS_Store' )
( cd plugin && zip -qr -X "$ROOT/dist/ahcene-babori-core-plugin.zip" ahcene-babori-core -x '*.DS_Store' )
S=build/ahcene-babori-researcher
mkdir -p "$S/source"
cp dist/*.zip "$S/"
cp README.md INSTALLATION.md CHANGELOG.md "$S/"
cp documentation/installation-guide.md documentation/language-setup.md documentation/QA-REPORT.md "$S/"
cp demo-content/demo-content.xml "$S/"
cp -r theme plugin documentation demo-content tools "$S/source/"
( cd build && zip -qr -X "$ROOT/dist/ahcene-babori-researcher-complete.zip" ahcene-babori-researcher )
cp dist/ahcene-babori-researcher-complete.zip dist/ahcene-babori-researcher.zip
rm -rf build
ls -la dist
