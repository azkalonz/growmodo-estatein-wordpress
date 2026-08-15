#!/usr/bin/env bash
set -Eeuo pipefail

ESTATEIN_ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ESTATEIN_DIST_DIR="${ESTATEIN_ROOT_DIR}/dist"
ESTATEIN_THEME_DIR="${ESTATEIN_ROOT_DIR}/wp-content/themes/estatein"
ESTATEIN_PLUGIN_DIR="${ESTATEIN_ROOT_DIR}/wp-content/plugins/estatein-core"
ESTATEIN_WXR_SOURCE="${ESTATEIN_ROOT_DIR}/demo-content/estatein-demo-content.xml"

for source_dir in "${ESTATEIN_THEME_DIR}" "${ESTATEIN_PLUGIN_DIR}"; do
  if [[ ! -d "${source_dir}" ]]; then
    echo "Missing package source: ${source_dir}" >&2
    exit 1
  fi
done

mkdir -p "${ESTATEIN_DIST_DIR}"
rm -f \
  "${ESTATEIN_DIST_DIR}/estatein-theme.zip" \
  "${ESTATEIN_DIST_DIR}/estatein-core.zip" \
  "${ESTATEIN_DIST_DIR}/estatein-demo-content.xml" \
  "${ESTATEIN_DIST_DIR}/SHA256SUMS"

(
  cd "${ESTATEIN_ROOT_DIR}/wp-content/themes"
  zip -q -r -X "${ESTATEIN_DIST_DIR}/estatein-theme.zip" estatein \
    -x \
    '*/.DS_Store' \
    '*/tests/*' \
    '*/node_modules/*' \
    'estatein/assets/icons/figma/*' \
    'estatein/assets/images/figma/*'

  # These two exact Figma exports are runtime CTA assets, so add them back after
  # excluding the rest of the design-reference inventory from the release ZIP.
  zip -q -X "${ESTATEIN_DIST_DIR}/estatein-theme.zip" \
    estatein/assets/icons/figma/about-03.svg \
    estatein/assets/icons/figma/about-11.svg
)

(
  cd "${ESTATEIN_ROOT_DIR}/wp-content/plugins"
  zip -q -r -X "${ESTATEIN_DIST_DIR}/estatein-core.zip" estatein-core \
    -x '*/.DS_Store' '*/tests/*' '*/node_modules/*' '*/vendor/*'
)

if [[ -s "${ESTATEIN_WXR_SOURCE}" ]]; then
  cp "${ESTATEIN_WXR_SOURCE}" "${ESTATEIN_DIST_DIR}/estatein-demo-content.xml"
fi

(
  cd "${ESTATEIN_DIST_DIR}"
  ESTATEIN_CHECKSUM_TARGETS=( estatein-theme.zip estatein-core.zip )
  if [[ -s estatein-demo-content.xml ]]; then
    ESTATEIN_CHECKSUM_TARGETS+=( estatein-demo-content.xml )
  fi
  shasum -a 256 "${ESTATEIN_CHECKSUM_TARGETS[@]}" > SHA256SUMS
  shasum -a 256 -c SHA256SUMS
)

unzip -tq "${ESTATEIN_DIST_DIR}/estatein-theme.zip"
unzip -tq "${ESTATEIN_DIST_DIR}/estatein-core.zip"

echo "Packages created in ${ESTATEIN_DIST_DIR}:"
ESTATEIN_OUTPUT_FILES=(
  "${ESTATEIN_DIST_DIR}/estatein-theme.zip"
  "${ESTATEIN_DIST_DIR}/estatein-core.zip"
  "${ESTATEIN_DIST_DIR}/SHA256SUMS"
)
if [[ -s "${ESTATEIN_DIST_DIR}/estatein-demo-content.xml" ]]; then
  ESTATEIN_OUTPUT_FILES+=( "${ESTATEIN_DIST_DIR}/estatein-demo-content.xml" )
fi
ls -lh "${ESTATEIN_OUTPUT_FILES[@]}"
