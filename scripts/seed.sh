#!/usr/bin/env bash
set -Eeuo pipefail

ESTATEIN_ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

"${ESTATEIN_ROOT_DIR}/scripts/wp.sh" plugin activate advanced-custom-fields estatein-core --quiet
"${ESTATEIN_ROOT_DIR}/scripts/wp.sh" estatein seed
"${ESTATEIN_ROOT_DIR}/scripts/wp.sh" rewrite flush
