#!/usr/bin/env bash
# ============================================================================
#  Ember POS - one-command launcher (macOS / Linux)
#
#  Downloads a small self-contained PHP runtime into ./.runtime on first run
#  (no system install needed), then serves the app. Uses SQLite, so there is
#  no database server to set up. The DB is created and seeded automatically.
#
#  Usage:   ./run.sh            (then open the printed URL)
# ============================================================================
set -e
cd "$(dirname "$0")"

PHP_VERSION="8.4.23"
PORT="${PORT:-8000}"
RUNTIME_DIR=".runtime"
PHP_BIN="$RUNTIME_DIR/php"

# ---- Detect platform --------------------------------------------------------
OS="$(uname -s)"
ARCH="$(uname -m)"
case "$OS" in
  Darwin) OS_TAG="macos" ;;
  Linux)  OS_TAG="linux" ;;
  *) echo "Unsupported OS: $OS (use run.ps1 on Windows)"; exit 1 ;;
esac
case "$ARCH" in
  arm64|aarch64) ARCH_TAG="aarch64" ;;
  x86_64|amd64)  ARCH_TAG="x86_64" ;;
  *) echo "Unsupported architecture: $ARCH"; exit 1 ;;
esac

# ---- Download the portable PHP runtime if we don't have it ------------------
if [ ! -x "$PHP_BIN" ]; then
  echo "Downloading portable PHP $PHP_VERSION ($OS_TAG-$ARCH_TAG)…"
  mkdir -p "$RUNTIME_DIR"
  URL="https://dl.static-php.dev/static-php-cli/common/php-${PHP_VERSION}-cli-${OS_TAG}-${ARCH_TAG}.tar.gz"
  if command -v curl >/dev/null 2>&1; then
    curl -fsSL "$URL" -o "$RUNTIME_DIR/php.tar.gz"
  else
    wget -q "$URL" -O "$RUNTIME_DIR/php.tar.gz"
  fi
  tar -xzf "$RUNTIME_DIR/php.tar.gz" -C "$RUNTIME_DIR"
  rm -f "$RUNTIME_DIR/php.tar.gz"
  chmod +x "$PHP_BIN"
  echo "PHP runtime ready."
fi

# ---- Serve ------------------------------------------------------------------
URL="http://localhost:$PORT"
echo ""
echo "  Ember POS is running at  $URL"
echo "  Staff logins:  admin / admin1   ·   staff / johnlogin"
echo "  Press Ctrl+C to stop."
echo ""

# Try to open the browser (non-fatal if it fails)
( sleep 1; (command -v open >/dev/null && open "$URL") || (command -v xdg-open >/dev/null && xdg-open "$URL") ) >/dev/null 2>&1 &

exec "$PHP_BIN" -S "localhost:$PORT" -t .
