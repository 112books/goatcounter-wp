<?php
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) exit;

delete_option( 'gcwp_site_code' );
delete_option( 'gcwp_api_token' );
delete_option( 'gcwp_selfhosted_url' );
