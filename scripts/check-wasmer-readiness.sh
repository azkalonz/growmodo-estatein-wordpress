#!/usr/bin/env bash
set -Eeuo pipefail

ESTATEIN_ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ESTATEIN_DIST_DIR="${ESTATEIN_ROOT_DIR}/dist"
ESTATEIN_THEME_ZIP="${ESTATEIN_DIST_DIR}/estatein-theme.zip"
ESTATEIN_PLUGIN_ZIP="${ESTATEIN_DIST_DIR}/estatein-core.zip"
ESTATEIN_WXR="${ESTATEIN_DIST_DIR}/estatein-demo-content.xml"
ESTATEIN_CHECKSUMS="${ESTATEIN_DIST_DIR}/SHA256SUMS"

ESTATEIN_REQUIRED_FILES=(
  "${ESTATEIN_THEME_ZIP}"
  "${ESTATEIN_PLUGIN_ZIP}"
  "${ESTATEIN_WXR}"
  "${ESTATEIN_CHECKSUMS}"
)

for required_file in "${ESTATEIN_REQUIRED_FILES[@]}"; do
  if [[ ! -s "${required_file}" ]]; then
    echo "Missing or empty Wasmer handoff file: ${required_file}" >&2
    exit 1
  fi
done

(
  cd "${ESTATEIN_DIST_DIR}"
  shasum -a 256 -c SHA256SUMS
)

unzip -tq "${ESTATEIN_THEME_ZIP}"
unzip -tq "${ESTATEIN_PLUGIN_ZIP}"

if ! unzip -Z1 "${ESTATEIN_THEME_ZIP}" | grep -Fx 'estatein/style.css' >/dev/null; then
  echo "Theme archive does not contain estatein/style.css at the expected path." >&2
  exit 1
fi

if ! unzip -Z1 "${ESTATEIN_PLUGIN_ZIP}" | grep -Fx 'estatein-core/estatein-core.php' >/dev/null; then
  echo "Plugin archive does not contain estatein-core/estatein-core.php at the expected path." >&2
  exit 1
fi

for archive in "${ESTATEIN_THEME_ZIP}" "${ESTATEIN_PLUGIN_ZIP}"; do
  if unzip -Z1 "${archive}" | grep -E '(^|/)(\.env($|\.)|wp-config\.php$|node_modules/|vendor/|tests/)' >/dev/null; then
    echo "Archive contains a forbidden development or secret-bearing path: ${archive}" >&2
    exit 1
  fi
done

if grep -Eqi '<wp:attachment_url>https?://(localhost|127\.0\.0\.1)(:|/|<)' "${ESTATEIN_WXR}"; then
  echo "WXR still contains a localhost attachment URL and is not portable." >&2
  exit 1
fi

ESTATEIN_HANDOFF_BYTES=0
for handoff_file in "${ESTATEIN_REQUIRED_FILES[@]}"; do
  ESTATEIN_FILE_BYTES="$(wc -c < "${handoff_file}")"
  ESTATEIN_HANDOFF_BYTES=$((ESTATEIN_HANDOFF_BYTES + ESTATEIN_FILE_BYTES))
done

if (( ESTATEIN_HANDOFF_BYTES >= 1000000000 )); then
  echo "Handoff exceeds Wasmer Hobby's 1 GB volume allowance." >&2
  exit 1
fi

echo
echo "Wasmer handoff is ready."
echo "  Directory: ${ESTATEIN_DIST_DIR}"
echo "  Transfer size: $((ESTATEIN_HANDOFF_BYTES / 1024 / 1024)) MiB"
echo "  Database content: portable WXR (database quota must be monitored after import)"
echo "  Deployment guide: ${ESTATEIN_ROOT_DIR}/docs/WASMER_DEPLOYMENT.md"
