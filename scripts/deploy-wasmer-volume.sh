#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

ESTATEIN_ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ESTATEIN_DIST_DIR="${ESTATEIN_DIST_DIR:-${ESTATEIN_ROOT_DIR}/dist}"
ESTATEIN_WASMER_APP="${ESTATEIN_WASMER_APP:-azkalonz/estatein-preview}"
ESTATEIN_WASMER_VOLUME="${ESTATEIN_WASMER_VOLUME:-wp-content}"
ESTATEIN_WASMER_MOUNT="${ESTATEIN_WASMER_MOUNT:-/app/wp-content}"
ESTATEIN_BASE_URL="${ESTATEIN_BASE_URL:-https://estatein-preview.wasmer.app}"
ESTATEIN_WASMER_BIN="${ESTATEIN_WASMER_BIN:-wasmer}"
ESTATEIN_RCLONE_BIN="${ESTATEIN_RCLONE_BIN:-rclone}"
ESTATEIN_WASMER_GRAPHQL_ENDPOINT="https://registry.wasmer.io/graphql"

: "${WASMER_TOKEN:?WASMER_TOKEN must contain a Wasmer access token}"

for required_command in "${ESTATEIN_WASMER_BIN}" "${ESTATEIN_RCLONE_BIN}" unzip curl jq shasum; do
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

if [[ ! "${ESTATEIN_WASMER_MOUNT}" =~ ^/app/[a-zA-Z0-9._/-]+$ ]]; then
  echo "Invalid Wasmer volume mount: ${ESTATEIN_WASMER_MOUNT}" >&2
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
ESTATEIN_CREDENTIAL_ERROR="${ESTATEIN_DEPLOY_DIR}/credentials-error.log"
ESTATEIN_GRAPHQL_CURL_CONFIG="${ESTATEIN_DEPLOY_DIR}/graphql-curl.conf"
ESTATEIN_GRAPHQL_REQUEST="${ESTATEIN_DEPLOY_DIR}/graphql-request.json"
ESTATEIN_GRAPHQL_RESPONSE="${ESTATEIN_DEPLOY_DIR}/graphql-response.json"
ESTATEIN_ROTATION_OUTPUT="${ESTATEIN_DEPLOY_DIR}/rotation-output.log"
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
estatein_fetch_volume_credentials() {
  "${ESTATEIN_WASMER_BIN}" app volumes credentials \
    "${ESTATEIN_WASMER_APP}" \
    --format=rclone \
    >"${ESTATEIN_RCLONE_CONFIG}" \
    2>"${ESTATEIN_CREDENTIAL_ERROR}"
}

estatein_post_graphql() {
  curl \
    --config "${ESTATEIN_GRAPHQL_CURL_CONFIG}" \
    --data-binary "@${ESTATEIN_GRAPHQL_REQUEST}" \
    --output "${ESTATEIN_GRAPHQL_RESPONSE}"

  if ! jq -e '(.errors // []) | length == 0' "${ESTATEIN_GRAPHQL_RESPONSE}" >/dev/null; then
    echo "Wasmer rejected the volume configuration request; nothing was changed" >&2
    jq -r '.errors[]?.message // empty' "${ESTATEIN_GRAPHQL_RESPONSE}" | head -n 3 >&2
    return 1
  fi
}

estatein_enable_volume_s3() {
  local estatein_owner="${ESTATEIN_WASMER_APP%%/*}"
  local estatein_app_name="${ESTATEIN_WASMER_APP#*/}"
  local estatein_volume_id
  local estatein_s3_enabled

  # Wasmer CLI 7.2.1 predates `app volume enable-s3`, so use the same
  # updateVolume mutation as the newer CLI when bootstrapping a volume once.
  printf '%s\n' \
    'fail' \
    'silent' \
    'show-error' \
    'retry = 3' \
    'retry-all-errors' \
    'connect-timeout = 15' \
    'max-time = 60' \
    "url = \"${ESTATEIN_WASMER_GRAPHQL_ENDPOINT}\"" \
    'header = "Content-Type: application/json"' \
    "header = \"Authorization: Bearer ${WASMER_TOKEN}\"" \
    >"${ESTATEIN_GRAPHQL_CURL_CONFIG}"

  jq -n \
    --arg owner "${estatein_owner}" \
    --arg name "${estatein_app_name}" \
    '{
      query: "query EstateinDeployVolumes($owner: String!, $name: String!) { getDeployApp(owner: $owner, name: $name) { volumes(first: 100) { edges { node { id mountPath s3Enabled } } } } }",
      variables: {owner: $owner, name: $name}
    }' >"${ESTATEIN_GRAPHQL_REQUEST}"
  estatein_post_graphql

  estatein_volume_id="$(
    jq -er \
      --arg mount "${ESTATEIN_WASMER_MOUNT}" \
      '[.data.getDeployApp.volumes.edges[]?.node | select(.mountPath == $mount)] | if length == 1 then .[0].id else empty end' \
      "${ESTATEIN_GRAPHQL_RESPONSE}"
  )"
  estatein_s3_enabled="$(
    jq -r \
      --arg mount "${ESTATEIN_WASMER_MOUNT}" \
      '.data.getDeployApp.volumes.edges[]?.node | select(.mountPath == $mount) | .s3Enabled' \
      "${ESTATEIN_GRAPHQL_RESPONSE}"
  )"

  if [[ -z "${estatein_volume_id}" ]]; then
    echo "Wasmer did not return exactly one volume mounted at ${ESTATEIN_WASMER_MOUNT}; nothing was changed" >&2
    return 1
  fi

  if [[ "${estatein_s3_enabled}" == 'true' ]]; then
    echo "S3 is already enabled; rotating missing credentials once..."
    if ! "${ESTATEIN_WASMER_BIN}" app volumes rotate-secrets \
      "${ESTATEIN_WASMER_APP}" \
      --format=json \
      --quiet \
      >"${ESTATEIN_ROTATION_OUTPUT}" \
      2>&1; then
      echo "Wasmer could not recover the missing volume credentials; nothing was changed" >&2
      grep -Eiv '(access[_ -]?key|secret|token|password)' "${ESTATEIN_ROTATION_OUTPUT}" | head -n 10 >&2 || true
      return 1
    fi
    return 0
  fi

  jq -n \
    --arg id "${estatein_volume_id}" \
    '{
      query: "mutation EstateinEnableVolumeS3($id: ID!, $s3Enabled: Boolean) { updateVolume(input: {id: $id, s3Enabled: $s3Enabled}) { success } }",
      variables: {id: $id, s3Enabled: true}
    }' >"${ESTATEIN_GRAPHQL_REQUEST}"
  estatein_post_graphql

  if ! jq -e '.data.updateVolume.success == true' "${ESTATEIN_GRAPHQL_RESPONSE}" >/dev/null; then
    echo "Wasmer did not confirm S3 access for ${ESTATEIN_WASMER_MOUNT}; nothing was changed" >&2
    return 1
  fi

  echo "Enabled S3 access for ${ESTATEIN_WASMER_MOUNT}."
}

if ! estatein_fetch_volume_credentials; then
  if ! grep -Fq 'app does not have S3 credentials' "${ESTATEIN_CREDENTIAL_ERROR}"; then
    echo "Wasmer could not provide volume credentials; nothing was changed" >&2
    exit 1
  fi

  echo "Initializing S3 access for the deployment volume..."
  estatein_enable_volume_s3

  ESTATEIN_CREDENTIALS_READY=false
  for estatein_attempt in 1 2 3 4 5; do
    if estatein_fetch_volume_credentials; then
      ESTATEIN_CREDENTIALS_READY=true
      break
    fi
    sleep 2
  done
  if [[ "${ESTATEIN_CREDENTIALS_READY}" != 'true' ]]; then
    echo "Wasmer did not return volume credentials after initialization; nothing was changed" >&2
    exit 1
  fi
fi
chmod 600 "${ESTATEIN_RCLONE_CONFIG}"

ESTATEIN_RCLONE_REMOTE="$(
  sed -n 's/^\[\([^]]*\)\]$/\1/p' "${ESTATEIN_RCLONE_CONFIG}" | head -n 1
)"
if [[ -z "${ESTATEIN_RCLONE_REMOTE}" ]]; then
  echo "Wasmer did not return a valid rclone configuration" >&2
  exit 1
fi

ESTATEIN_REMOTE_DIRS="$("${ESTATEIN_RCLONE_BIN}" lsf \
  --config "${ESTATEIN_RCLONE_CONFIG}" \
  --dirs-only \
  "${ESTATEIN_RCLONE_REMOTE}:")"

if grep -Fxq "${ESTATEIN_WASMER_VOLUME}/" <<<"${ESTATEIN_REMOTE_DIRS}"; then
  ESTATEIN_REMOTE_ROOT="${ESTATEIN_RCLONE_REMOTE}:${ESTATEIN_WASMER_VOLUME}/"
elif grep -Fxq 'plugins/' <<<"${ESTATEIN_REMOTE_DIRS}" && grep -Fxq 'themes/' <<<"${ESTATEIN_REMOTE_DIRS}"; then
  ESTATEIN_REMOTE_ROOT="${ESTATEIN_RCLONE_REMOTE}:"
else
  echo "Expected Wasmer volume '${ESTATEIN_WASMER_VOLUME}' was not found; nothing was changed" >&2
  exit 1
fi

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
  "${ESTATEIN_REMOTE_ROOT}plugins/estatein-core" \
  "${ESTATEIN_RCLONE_SYNC_FLAGS[@]}"

echo "Syncing the Estatein theme to ${ESTATEIN_WASMER_APP}..."
"${ESTATEIN_RCLONE_BIN}" sync \
  "${ESTATEIN_DEPLOY_DIR}/release/estatein" \
  "${ESTATEIN_REMOTE_ROOT}themes/estatein" \
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
