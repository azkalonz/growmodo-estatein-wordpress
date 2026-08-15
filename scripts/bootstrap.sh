#!/usr/bin/env bash
set -Eeuo pipefail

ESTATEIN_ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ESTATEIN_ENV_FILE="${ESTATEIN_ENV_FILE:-${ESTATEIN_ROOT_DIR}/.env}"
ESTATEIN_WP="${ESTATEIN_ROOT_DIR}/scripts/wp.sh"

if [[ ! -f "${ESTATEIN_ENV_FILE}" ]]; then
  echo "Missing .env. Run: cp .env.example .env, then replace every placeholder." >&2
  exit 1
fi

estatein_env_value() {
  local key="$1"
  awk -F= -v key="${key}" '$1 == key { sub(/^[^=]*=/, ""); print; exit }' "${ESTATEIN_ENV_FILE}"
}

ESTATEIN_SITE_URL="$(estatein_env_value ESTATEIN_SITE_URL)"
ESTATEIN_SITE_TITLE="$(estatein_env_value ESTATEIN_SITE_TITLE)"
ESTATEIN_ADMIN_USER="$(estatein_env_value ESTATEIN_ADMIN_USER)"
ESTATEIN_ADMIN_PASSWORD="$(estatein_env_value ESTATEIN_ADMIN_PASSWORD)"
ESTATEIN_ADMIN_EMAIL="$(estatein_env_value ESTATEIN_ADMIN_EMAIL)"

for ESTATEIN_REQUIRED_NAME in \
  ESTATEIN_SITE_URL \
  ESTATEIN_SITE_TITLE \
  ESTATEIN_ADMIN_USER \
  ESTATEIN_ADMIN_PASSWORD \
  ESTATEIN_ADMIN_EMAIL; do
  if [[ -z "${!ESTATEIN_REQUIRED_NAME}" ]]; then
    echo "${ESTATEIN_REQUIRED_NAME} must not be empty in .env." >&2
    exit 1
  fi
done

if [[ "$(awk 'index($0, "replace-with-") { print "yes"; exit }' "${ESTATEIN_ENV_FILE}")" == "yes" ]]; then
  echo "Replace every replace-with-* value in .env before bootstrapping." >&2
  exit 1
fi

docker compose \
  --project-directory "${ESTATEIN_ROOT_DIR}" \
  --env-file "${ESTATEIN_ENV_FILE}" \
  up --detach --build db mailpit wordpress

echo "Waiting for the WordPress container to become healthy..."
for attempt in {1..60}; do
  ESTATEIN_WORDPRESS_STATE="$(docker compose \
    --project-directory "${ESTATEIN_ROOT_DIR}" \
    --env-file "${ESTATEIN_ENV_FILE}" \
    ps --format json wordpress 2>/dev/null)"

  if [[ "${ESTATEIN_WORDPRESS_STATE}" == *'"Health"'*'"healthy"'* ]]; then
    break
  fi

  if [[ "${attempt}" -eq 60 ]]; then
    echo "WordPress did not become healthy. Run: make logs" >&2
    exit 1
  fi

  sleep 2
done

if ! "${ESTATEIN_WP}" core is-installed --quiet --url="${ESTATEIN_SITE_URL}"; then
  "${ESTATEIN_WP}" core install \
    --url="${ESTATEIN_SITE_URL}" \
    --title="${ESTATEIN_SITE_TITLE}" \
    --admin_user="${ESTATEIN_ADMIN_USER}" \
    --admin_password="${ESTATEIN_ADMIN_PASSWORD}" \
    --admin_email="${ESTATEIN_ADMIN_EMAIL}" \
    --skip-email
fi

if ! "${ESTATEIN_WP}" plugin is-installed advanced-custom-fields; then
  "${ESTATEIN_WP}" plugin install advanced-custom-fields --version=6.8.7
fi

"${ESTATEIN_WP}" plugin activate advanced-custom-fields estatein-core --quiet
"${ESTATEIN_WP}" theme activate estatein
"${ESTATEIN_WP}" option update blogdescription "Find Your Dream Property"
"${ESTATEIN_WP}" rewrite structure '/%postname%/' --hard
"${ESTATEIN_WP}" estatein seed
"${ESTATEIN_WP}" rewrite flush

echo
echo "Estatein is ready: ${ESTATEIN_SITE_URL}"
echo "WordPress admin: ${ESTATEIN_SITE_URL}/wp-admin/"
echo "Mailpit: http://127.0.0.1:8026"
