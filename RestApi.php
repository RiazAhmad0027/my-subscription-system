<?php
namespace KashifBakers\Subscriptions;

class RestApi {
    
    public function __construct() {
        add_action( 'rest_api_init', array( $this, 'registerRoutes' ) );
    }
    
    public function registerRoutes() {
        register_rest_route( 'kashif-bakers/v1', '/subscriptions', array(
            'methods' => 'GET',
            'callback' => array( $this, 'getSubscriptions' ),
            'permission_callback' => '__return_true'
        ));
    }
    
    public function getSubscriptions() {
        global $wpdb;
        $table = $wpdb->prefix . 'mss_subscriptions';
        
        $subscriptions = $wpdb->get_results( "SELECT * FROM $table" );
        
        return new \WP_REST_Response( $subscriptions, 200 );
    }
}