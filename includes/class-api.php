<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class GCWP_API {

    private $site;
    private $token;
    private $base;

    public function __construct() {
        $this->site  = get_option( 'gcwp_site_code' );
        $this->token = get_option( 'gcwp_api_token' );
        $this->base  = "https://{$this->site}.goatcounter.com/api/v0";
    }

    public function is_configured() {
        return ! empty( $this->site ) && ! empty( $this->token );
    }

    private function get( $endpoint, $params = [] ) {
        if ( ! $this->is_configured() ) return null;
        $url = $this->base . $endpoint;
        if ( $params ) $url .= '?' . http_build_query( $params );
        $response = wp_remote_get( $url, [
            'headers' => [ 'Authorization' => 'Bearer ' . $this->token ],
            'timeout' => 15,
        ] );
        if ( is_wp_error( $response ) ) return null;
        if ( wp_remote_retrieve_response_code( $response ) !== 200 ) return null;
        return json_decode( wp_remote_retrieve_body( $response ), true );
    }

    private function date_range( $period ) {
        $end = gmdate( 'Y-m-d', strtotime( '+1 day' ) );
        switch ( $period ) {
            case 'day':     $start = gmdate( 'Y-m-d' ); break;
            case 'week':    $start = gmdate( 'Y-m-d', strtotime( '-6 days' ) ); break;
            case 'month':   $start = gmdate( 'Y-m-d', strtotime( '-30 days' ) ); break;
            case 'quarter': $start = gmdate( 'Y-m-d', strtotime( '-90 days' ) ); break;
            case 'year':    $start = gmdate( 'Y-m-d', strtotime( '-365 days' ) ); break;
            default:        $start = gmdate( 'Y-m-d', strtotime( '-7 days' ) );
        }
        return [ 'start' => $start, 'end' => $end ];
    }

    public function get_hits( $period = 'week' ) {
        return $this->get( '/stats/hits', array_merge( $this->date_range( $period ), [ 'limit' => 15 ] ) );
    }

    public function get_totals( $period = 'week' ) {
        return $this->get( '/stats/total', $this->date_range( $period ) );
    }

    public function get_browsers( $period = 'week' ) {
        return $this->get( '/stats/browsers', array_merge( $this->date_range( $period ), [ 'limit' => 8 ] ) );
    }

    public function get_systems( $period = 'week' ) {
        return $this->get( '/stats/systems', array_merge( $this->date_range( $period ), [ 'limit' => 8 ] ) );
    }

    public function get_locations( $period = 'week' ) {
        return $this->get( '/stats/locations', array_merge( $this->date_range( $period ), [ 'limit' => 15 ] ) );
    }
}
