<?php
/**
 * Plugin Name:       Estatein Core
 * Plugin URI:        https://github.com/azkalonz/growmodo-estatein-wordpress
 * Description:       Portable property, team, inquiry, filtering, form, and SEO features for Estatein.
 * Version:           1.0.0
 * Requires at least: 6.6
 * Requires PHP:      8.1
 * Author:            Mark Azkalonz
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       estatein-core
 *
 * @package EstateinCore
 */

defined( 'ABSPATH' ) || exit;

define( 'ESTATEIN_CORE_VERSION', '1.0.0' );
define( 'ESTATEIN_CORE_FILE', __FILE__ );
define( 'ESTATEIN_CORE_PATH', plugin_dir_path( __FILE__ ) );

require_once ESTATEIN_CORE_PATH . 'includes/content-types.php';
require_once ESTATEIN_CORE_PATH . 'includes/fields.php';
require_once ESTATEIN_CORE_PATH . 'includes/filters.php';
require_once ESTATEIN_CORE_PATH . 'includes/forms.php';
require_once ESTATEIN_CORE_PATH . 'includes/seo.php';
require_once ESTATEIN_CORE_PATH . 'includes/admin.php';

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once ESTATEIN_CORE_PATH . 'includes/class-estatein-core-seed-command.php';
}

/**
 * Load translations.
 *
 * @return void
 */
function estatein_core_load_textdomain() {
	load_plugin_textdomain( 'estatein-core', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'estatein_core_load_textdomain' );

/**
 * Register content before flushing rules on activation.
 *
 * @return void
 */
function estatein_core_activate() {
	estatein_core_register_content_types();
	flush_rewrite_rules();
}
register_activation_hook( __FILE__, 'estatein_core_activate' );

/**
 * Flush only this plugin's rewrite rules on deactivation.
 *
 * @return void
 */
function estatein_core_deactivate() {
	flush_rewrite_rules();
}
register_deactivation_hook( __FILE__, 'estatein_core_deactivate' );
