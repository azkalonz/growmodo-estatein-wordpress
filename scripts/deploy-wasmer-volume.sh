#!/usr/bin/env bash
set -Eeuo pipefail
umask 077

ESTATEIN_ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ESTATEIN_DIST_DIR="${ESTATEIN_DIST_DIR:-${ESTATEIN_ROOT_DIR}/dist}"
ESTATEIN_WASMER_APP="${ESTATEIN_WASMER_APP:-azkalonz/estatein-preview}"
ESTATEIN_WASMER_MOUNT="${ESTATEIN_WASMER_MOUNT:-/app/wp-content}"
ESTATEIN_BASE_URL="${ESTATEIN_BASE_URL:-https://estatein-preview.wasmer.app}"
ESTATEIN_RCLONE_BIN="${ESTATEIN_RCLONE_BIN:-rclone}"
ESTATEIN_WASMER_GRAPHQL_ENDPOINT="https://registry.wasmer.io/graphql"

: "${WASMER_TOKEN:?WASMER_TOKEN must contain a Wasmer access token}"

for required_command in "${ESTATEIN_RCLONE_BIN}" unzip curl jq shasum; do
  if ! command -v "${required_command}" >/dev/null 2>&1; then
    echo "Missing required deployment command: ${required_command}" >&2
    exit 1
  fi
done

if [[ ! "${ESTATEIN_WASMER_APP}" =~ ^[a-z0-9-]+/[a-z0-9-]+$ ]]; then
  echo "Invalid Wasmer app identifier: ${ESTATEIN_WASMER_APP}" >&2
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
ESTATEIN_GRAPHQL_CURL_CONFIG="${ESTATEIN_DEPLOY_DIR}/graphql-curl.conf"
ESTATEIN_GRAPHQL_REQUEST="${ESTATEIN_DEPLOY_DIR}/graphql-request.json"
ESTATEIN_GRAPHQL_RESPONSE="${ESTATEIN_DEPLOY_DIR}/graphql-response.json"
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

estatein_query_volume() {
  local estatein_owner="${ESTATEIN_WASMER_APP%%/*}"
  local estatein_app_name="${ESTATEIN_WASMER_APP#*/}"

  jq -n \
    --arg owner "${estatein_owner}" \
    --arg name "${estatein_app_name}" \
    '{
      query: "query EstateinDeployVolumes($owner: String!, $name: String!) { getDeployApp(owner: $owner, name: $name) { volumes(first: 100) { edges { node { id mountPath s3Enabled s3 { accessKey secretKey endpoint } } } } } }",
      variables: {owner: $owner, name: $name}
    }' >"${ESTATEIN_GRAPHQL_REQUEST}"
  estatein_post_graphql
}

estatein_select_volume() {
  jq -ec \
    --arg mount "${ESTATEIN_WASMER_MOUNT}" \
    '[.data.getDeployApp.volumes.edges[]?.node | select(.mountPath == $mount)] | if length == 1 then .[0] else empty end' \
    "${ESTATEIN_GRAPHQL_RESPONSE}"
}

estatein_enable_volume_s3() {
  local estatein_volume_id="$1"

  # Wasmer CLI 7.2.1 predates `app volume enable-s3`, so use the same
  # updateVolume mutation as the current CLI when bootstrapping a volume once.
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

estatein_rotate_volume_credentials() {
  local estatein_volume_id="$1"

  jq -n \
    --arg id "${estatein_volume_id}" \
    '{
      query: "mutation EstateinRotateVolumeS3($id: ID!) { rotateS3Credentials(input: {id: $id}) { accessKey secretKey endpoint success } }",
      variables: {id: $id}
    }' >"${ESTATEIN_GRAPHQL_REQUEST}"
  estatein_post_graphql

  if ! jq -e '.data.rotateS3Credentials.success == true' "${ESTATEIN_GRAPHQL_RESPONSE}" >/dev/null; then
    echo "Wasmer could not initialize per-volume credentials; nothing was changed" >&2
    return 1
  fi

  jq -ec '.data.rotateS3Credentials | {accessKey, secretKey, endpoint}' "${ESTATEIN_GRAPHQL_RESPONSE}"
}

estatein_write_rclone_config() {
  local estatein_credentials="$1"
  local estatein_app_name="${ESTATEIN_WASMER_APP#*/}"
  local estatein_volume_slug="${ESTATEIN_WASMER_MOUNT#/}"
  local estatein_access_key
  local estatein_secret_key
  local estatein_endpoint

  estatein_volume_slug="${estatein_volume_slug//\//-}"
  ESTATEIN_RCLONE_REMOTE="edge-${estatein_app_name}-${estatein_volume_slug}"
  estatein_access_key="$(jq -er '.accessKey' <<<"${estatein_credentials}")"
  estatein_secret_key="$(jq -er '.secretKey' <<<"${estatein_credentials}")"
  estatein_endpoint="$(jq -er '.endpoint' <<<"${estatein_credentials}")"

  if [[ -z "${estatein_access_key}" || -z "${estatein_secret_key}" || ! "${estatein_endpoint}" =~ ^https://[^[:space:]]+$ ]]; then
    echo "Wasmer returned invalid per-volume credentials; nothing was changed" >&2
    return 1
  fi
  if [[ "${estatein_access_key}" == *$'\n'* || "${estatein_secret_key}" == *$'\n'* || "${estatein_endpoint}" == *$'\n'* ]]; then
    echo "Wasmer returned unsafe credential formatting; nothing was changed" >&2
    return 1
  fi

  printf '%s\n' \
    "[${ESTATEIN_RCLONE_REMOTE}]" \
    'type = s3' \
    'provider = Other' \
    'acl = private' \
    "access_key_id = ${estatein_access_key}" \
    "secret_access_key = ${estatein_secret_key}" \
    "endpoint = ${estatein_endpoint}" \
    >"${ESTATEIN_RCLONE_CONFIG}"
}

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

echo "Requesting per-volume credentials for ${ESTATEIN_WASMER_APP}..."
estatein_query_volume
ESTATEIN_VOLUME_JSON="$(estatein_select_volume)"
if [[ -z "${ESTATEIN_VOLUME_JSON}" ]]; then
  echo "Wasmer did not return exactly one volume mounted at ${ESTATEIN_WASMER_MOUNT}; nothing was changed" >&2
  exit 1
fi

ESTATEIN_VOLUME_ID="$(jq -er '.id' <<<"${ESTATEIN_VOLUME_JSON}")"
ESTATEIN_S3_ENABLED="$(jq -r '.s3Enabled' <<<"${ESTATEIN_VOLUME_JSON}")"
if [[ "${ESTATEIN_S3_ENABLED}" != 'true' ]]; then
  echo "Initializing S3 access for the deployment volume..."
  estatein_enable_volume_s3 "${ESTATEIN_VOLUME_ID}"
fi

ESTATEIN_CREDENTIALS_JSON="$(jq -ec '.s3 // empty' <<<"${ESTATEIN_VOLUME_JSON}" || true)"
if [[ -z "${ESTATEIN_CREDENTIALS_JSON}" ]]; then
  for estatein_attempt in 1 2 3 4 5; do
    estatein_query_volume
    ESTATEIN_VOLUME_JSON="$(estatein_select_volume)"
    ESTATEIN_CREDENTIALS_JSON="$(jq -ec '.s3 // empty' <<<"${ESTATEIN_VOLUME_JSON}" || true)"
    if [[ -n "${ESTATEIN_CREDENTIALS_JSON}" ]]; then
      break
    fi
    sleep 2
  done
fi

if [[ -z "${ESTATEIN_CREDENTIALS_JSON}" && "${ESTATEIN_S3_ENABLED}" == 'true' ]]; then
  echo "S3 is enabled but credentials are missing; rotating this volume once..."
  ESTATEIN_CREDENTIALS_JSON="$(estatein_rotate_volume_credentials "${ESTATEIN_VOLUME_ID}")"
fi
if [[ -z "${ESTATEIN_CREDENTIALS_JSON}" ]]; then
  echo "Wasmer did not return per-volume credentials after initialization; nothing was changed" >&2
  exit 1
fi

estatein_write_rclone_config "${ESTATEIN_CREDENTIALS_JSON}"
chmod 600 "${ESTATEIN_RCLONE_CONFIG}"

ESTATEIN_REMOTE_DIRS="$("${ESTATEIN_RCLONE_BIN}" lsf \
  --config "${ESTATEIN_RCLONE_CONFIG}" \
  --dirs-only \
  --recursive \
  --max-depth 4 \
  "${ESTATEIN_RCLONE_REMOTE}:")"

ESTATEIN_REMOTE_ROOT_CANDIDATES="$(awk '
  { directories[$0] = 1 }
  END {
    for (directory in directories) {
      if (directory ~ /(^|\/)plugins\/$/) {
        prefix = directory
        sub(/plugins\/$/, "", prefix)
        if ((prefix "themes/") in directories) {
          if (prefix == "") {
            print "."
          } else {
            print prefix
          }
        }
      }
    }
  }
' <<<"${ESTATEIN_REMOTE_DIRS}")"
ESTATEIN_REMOTE_ROOT_COUNT="$(sed '/^$/d' <<<"${ESTATEIN_REMOTE_ROOT_CANDIDATES}" | wc -l | tr -d ' ')"
ESTATEIN_REMOTE_PATH_PREFIX="$(sed -n '1p' <<<"${ESTATEIN_REMOTE_ROOT_CANDIDATES}")"

if [[ "${ESTATEIN_REMOTE_ROOT_COUNT}" != '1' ]]; then
  echo "Wasmer did not expose exactly one WordPress volume root; nothing was changed" >&2
  exit 1
fi

if [[ "${ESTATEIN_REMOTE_PATH_PREFIX}" == '.' ]]; then
  ESTATEIN_REMOTE_ROOT="${ESTATEIN_RCLONE_REMOTE}:"
else
  if [[ ! "${ESTATEIN_REMOTE_PATH_PREFIX}" =~ ^([a-zA-Z0-9._-]+/)+$ || "${ESTATEIN_REMOTE_PATH_PREFIX}" == *'../'* ]]; then
    echo "Wasmer returned an unsafe WordPress volume path; nothing was changed" >&2
    exit 1
  fi
  ESTATEIN_REMOTE_ROOT="${ESTATEIN_RCLONE_REMOTE}:${ESTATEIN_REMOTE_PATH_PREFIX}"
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
