#!/usr/bin/env bash
set -Eeuo pipefail

ESTATEIN_ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"

"${ESTATEIN_ROOT_DIR}/scripts/wp.sh" plugin install theme-check --activate
"${ESTATEIN_ROOT_DIR}/scripts/wp.sh" plugin install plugin-check --activate

echo "Theme Check: open /wp-admin/themes.php?page=themecheck"
echo "Plugin Check CLI: ./scripts/wp.sh plugin check estatein-core --require=./wp-content/plugins/plugin-check/cli.php"
