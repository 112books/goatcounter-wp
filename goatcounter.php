<?php
/**
 * Plugin Name:       LinuxBCN Analytics for GoatCounter
 * Plugin URI:        https://github.com/112books/goatcounter-wp
 * Description:       Privacy-friendly analytics via GoatCounter. No cookies, no GDPR banner. Stats dashboard in your WordPress admin.
 * Version:           1.2.1
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Author:            LinuxBCN
 * Author URI:        https://linuxbcn.com
 * License:           GPL-2.0-or-later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       linuxbcn-analytics-for-goatcounter
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
    new GCWP_Admin();
}

// Inject tracking script
add_action( 'wp_enqueue_scripts', 'gcwp_enqueue_tracking_script' );
function gcwp_enqueue_tracking_script() {
    if ( is_admin() ) return;
    $site = get_option( 'gcwp_site_code' );
    if ( ! $site ) return;
    $site = sanitize_text_field( $site );

    wp_enqueue_script( 'gcwp-goatcounter', '//gc.zgo.at/count.js', [], GCWP_VERSION, true );
    wp_script_add_data( 'gcwp-goatcounter', 'async', true );
    wp_script_add_data( 'gcwp-goatcounter', 'gcwp_site', $site );
}

add_filter( 'script_loader_tag', 'gcwp_add_tracking_attribute', 10, 2 );
function gcwp_add_tracking_attribute( $tag, $handle ) {
    if ( 'gcwp-goatcounter' !== $handle ) return $tag;
    $site = wp_scripts()->get_data( $handle, 'gcwp_site' );
    if ( ! $site ) return $tag;
    return str_replace(
        ' src=',
        ' data-goatcounter="https://' . esc_attr( $site ) . '.goatcounter.com/count" src=',
        $tag
    );
}
