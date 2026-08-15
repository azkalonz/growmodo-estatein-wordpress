#!/usr/bin/env bash
set -Eeuo pipefail

ESTATEIN_ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ESTATEIN_EXPORT_FILE="${ESTATEIN_ROOT_DIR}/demo-content/estatein-demo-content.xml"

rm -f "${ESTATEIN_EXPORT_FILE}"
"${ESTATEIN_ROOT_DIR}/scripts/wp.sh" export \
  --dir=/exports \
  --filename_format=estatein-demo-content.xml \
  --with_attachments

if [[ ! -s "${ESTATEIN_EXPORT_FILE}" ]]; then
  echo "The WXR export was not created at ${ESTATEIN_EXPORT_FILE}." >&2
  exit 1
fi

node "${ESTATEIN_ROOT_DIR}/scripts/make-wxr-portable.mjs" "${ESTATEIN_EXPORT_FILE}"

echo "WXR demo content exported to ${ESTATEIN_EXPORT_FILE}"
