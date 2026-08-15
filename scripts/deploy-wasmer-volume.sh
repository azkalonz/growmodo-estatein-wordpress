#!/usr/bin/env bash
set -Eeuo pipefail

ESTATEIN_ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ESTATEIN_DIST_DIR="${ESTATEIN_DIST_DIR:-${ESTATEIN_ROOT_DIR}/dist}"
ESTATEIN_WASMER_APP="${ESTATEIN_WASMER_APP:-azkalonz/estatein-preview}"
ESTATEIN_WASMER_VOLUME="${ESTATEIN_WASMER_VOLUME:-wp-content}"
ESTATEIN_BASE_URL="${ESTATEIN_BASE_URL:-https://estatein-preview.wasmer.app}"
ESTATEIN_WASMER_BIN="${ESTATEIN_WASMER_BIN:-wasmer}"
ESTATEIN_RCLONE_BIN="${ESTATEIN_RCLONE_BIN:-rclone}"

: "${WASMER_TOKEN:?WASMER_TOKEN must contain a Wasmer access token}"

for required_command in "${ESTATEIN_WASMER_BIN}" "${ESTATEIN_RCLONE_BIN}" unzip curl shasum; do
  if ! command -v "${required_command}" >/dev/null 2>&1; then
    echo "Missing required deployment command: ${required_command}" >&2
    exit 1
  fi
done

if [[ ! "${ESTATEIN_WASMER_APP}" =~ ^[a-z0-9-]+/[a-z0-9-]+$ ]]; then
  echo "Invalid Wasmer app identifier: ${ESTATEIN_WASMER_APP}" >&2
  exit 1
fi

if [[ ! "${ESTATEIN_WASMER_VOLUME}" =~ ^[a-z0-9-]+$ ]]; then
  echo "Invalid Wasmer volume name: ${ESTATEIN_WASMER_VOLUME}" >&2
  exit 1
fi

if [[ ! "${ESTATEIN_BASE_URL}" =~ ^https://[^/]+/?$ ]]; then
  echo "ESTATEIN_BASE_URL must be an HTTPS origin without a path" >&2
  exit 1
fi
ESTATEIN_BASE_URL="${ESTATEIN_BASE_URL%/}"

ESTATEIN_REQUIRED_ARTIFACTS=(
  estatein-theme.zip
  estatein-core.zip
  SHA256SUMS
)
for artifact in "${ESTATEIN_REQUIRED_ARTIFACTS[@]}"; do
  if [[ ! -s "${ESTATEIN_DIST_DIR}/${artifact}" ]]; then
    echo "Missing deployment artifact: ${ESTATEIN_DIST_DIR}/${artifact}" >&2
    exit 1
  fi
done

(
  cd "${ESTATEIN_DIST_DIR}"
  shasum -a 256 -c SHA256SUMS
)

ESTATEIN_DEPLOY_DIR="$(mktemp -d "${TMPDIR:-/tmp}/estatein-wasmer-deploy.XXXXXX")"
ESTATEIN_RCLONE_CONFIG="${ESTATEIN_DEPLOY_DIR}/rclone.conf"
cleanup() {
  rm -rf "${ESTATEIN_DEPLOY_DIR}"
}
trap cleanup EXIT

unzip -q "${ESTATEIN_DIST_DIR}/estatein-theme.zip" -d "${ESTATEIN_DEPLOY_DIR}/release"
unzip -q "${ESTATEIN_DIST_DIR}/estatein-core.zip" -d "${ESTATEIN_DEPLOY_DIR}/release"

for release_dir in \
  "${ESTATEIN_DEPLOY_DIR}/release/estatein" \
  "${ESTATEIN_DEPLOY_DIR}/release/estatein-core"; do
  if [[ ! -d "${release_dir}" ]]; then
    echo "Unexpected package layout: ${release_dir} is missing" >&2
    exit 1
  fi
done

echo "Requesting temporary volume credentials for ${ESTATEIN_WASMER_APP}..."
"${ESTATEIN_WASMER_BIN}" app volumes credentials \
  "${ESTATEIN_WASMER_APP}" \
  --format=rclone >"${ESTATEIN_RCLONE_CONFIG}"
chmod 600 "${ESTATEIN_RCLONE_CONFIG}"

ESTATEIN_RCLONE_REMOTE="$(
  sed -n 's/^\[\([^]]*\)\]$/\1/p' "${ESTATEIN_RCLONE_CONFIG}" | head -n 1
)"
if [[ -z "${ESTATEIN_RCLONE_REMOTE}" ]]; then
  echo "Wasmer did not return a valid rclone configuration" >&2
  exit 1
fi

if ! "${ESTATEIN_RCLONE_BIN}" lsf \
  --config "${ESTATEIN_RCLONE_CONFIG}" \
  --dirs-only \
  "${ESTATEIN_RCLONE_REMOTE}:" | grep -Fxq "${ESTATEIN_WASMER_VOLUME}/"; then
  echo "Expected Wasmer volume '${ESTATEIN_WASMER_VOLUME}' was not found; nothing was changed" >&2
  exit 1
fi

ESTATEIN_REMOTE_ROOT="${ESTATEIN_RCLONE_REMOTE}:${ESTATEIN_WASMER_VOLUME}"
ESTATEIN_RCLONE_SYNC_FLAGS=(
  --config "${ESTATEIN_RCLONE_CONFIG}"
  --checksum
  --delete-after
  --fast-list
  --transfers 8
  --checkers 16
)

echo "Syncing Estatein Core to ${ESTATEIN_WASMER_APP}..."
"${ESTATEIN_RCLONE_BIN}" sync \
  "${ESTATEIN_DEPLOY_DIR}/release/estatein-core" \
  "${ESTATEIN_REMOTE_ROOT}/plugins/estatein-core" \
  "${ESTATEIN_RCLONE_SYNC_FLAGS[@]}"

echo "Syncing the Estatein theme to ${ESTATEIN_WASMER_APP}..."
"${ESTATEIN_RCLONE_BIN}" sync \
  "${ESTATEIN_DEPLOY_DIR}/release/estatein" \
  "${ESTATEIN_REMOTE_ROOT}/themes/estatein" \
  "${ESTATEIN_RCLONE_SYNC_FLAGS[@]}"

echo "Waiting for the public preview to serve the deployed theme..."
curl \
  --fail \
  --silent \
  --show-error \
  --location \
  --retry 8 \
  --retry-delay 5 \
  --retry-all-errors \
  --connect-timeout 15 \
  --max-time 45 \
  --output /dev/null \
  "${ESTATEIN_BASE_URL}/wp-content/themes/estatein/style.css"

curl \
  --fail \
  --silent \
  --show-error \
  --location \
  --retry 5 \
  --retry-delay 5 \
  --retry-all-errors \
  --connect-timeout 15 \
  --max-time 45 \
  --output /dev/null \
  "${ESTATEIN_BASE_URL}/"

echo "Wasmer preview deployment completed: ${ESTATEIN_BASE_URL}/"
