<?php
/**
 * Plugin Name:       GoatCounter Analytics
 * Plugin URI:        https://github.com/linuxbcn/goatcounter-wp
 * Description:       Privacy-friendly analytics via GoatCounter. No cookies, no GDPR banner. Stats dashboard in your WordPress admin.
 * Version:           1.2.0
 * Author:            LinuxBCN
 * Author URI:        https://linuxbcn.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       goatcounter-wp
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) exit;

define( 'GCWP_VERSION', '1.2.0' );
define( 'GCWP_DIR', plugin_dir_path( __FILE__ ) );
define( 'GCWP_URL', plugin_dir_url( __FILE__ ) );

require_once GCWP_DIR . 'includes/class-api.php';
require_once GCWP_DIR . 'includes/class-admin.php';

add_action( 'plugins_loaded', 'gcwp_init' );
function gcwp_init() {
    load_plugin_textdomain( 'goatcounter-wp', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    new GCWP_Admin();
}

// Inject tracking script
add_action( 'wp_footer', 'gcwp_tracking_script' );
function gcwp_tracking_script() {
    if ( is_admin() ) return;
    $site = get_option( 'gcwp_site_code' );
    if ( ! $site ) return;
    $site = esc_attr( sanitize_text_field( $site ) );
    echo "\n<script data-goatcounter=\"https://{$site}.goatcounter.com/count\" async src=\"//gc.zgo.at/count.js\"></script>\n";
}
