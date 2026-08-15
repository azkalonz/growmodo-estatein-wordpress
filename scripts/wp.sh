#!/usr/bin/env bash
set -Eeuo pipefail

ESTATEIN_ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ESTATEIN_ENV_FILE="${ESTATEIN_ENV_FILE:-${ESTATEIN_ROOT_DIR}/.env}"

if [[ ! -f "${ESTATEIN_ENV_FILE}" ]]; then
  echo "Missing ${ESTATEIN_ENV_FILE}. Copy .env.example to .env and replace every placeholder." >&2
  exit 1
fi

exec docker compose \
  --project-directory "${ESTATEIN_ROOT_DIR}" \
  --env-file "${ESTATEIN_ENV_FILE}" \
  --profile tools \
  run --rm wpcli "$@"
