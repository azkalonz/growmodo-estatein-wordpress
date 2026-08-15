#!/usr/bin/env bash
set -Eeuo pipefail

ESTATEIN_ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ESTATEIN_FAILURES=0

while IFS= read -r php_file; do
  if ! php -l "${ESTATEIN_ROOT_DIR}/${php_file}"; then
    ESTATEIN_FAILURES=1
  fi
done < <(
  cd "${ESTATEIN_ROOT_DIR}"
  find wp-content/themes/estatein wp-content/plugins/estatein-core \
    -type f \
    -name '*.php' \
    -not -path '*/vendor/*' \
    -print
)

exit "${ESTATEIN_FAILURES}"
