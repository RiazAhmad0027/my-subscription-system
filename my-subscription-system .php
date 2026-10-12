<?php
/**
 * Plugin Name: My Subscription System
 * Plugin URI: https://kashifbakers.shop
 * Description: Custom WooCommerce subscription system with Stripe integration
 * Version: 3.0.0
 * Author: Riaz Ahmad
 * License: GPL v2 or later
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define( 'MSS_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'MSS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'MSS_PLUGIN_VERSION', '3.0.0' );

// Autoload classes
spl_autoload_register( function( $class ) {
    if ( strpos( $class, 'KashifBakers\\Subscriptions' ) === 0 ) {
        $path = str_replace( '\\', '/', $class );
        $path = str_replace( 'KashifBakers/Subscriptions/', '', $path );
        $file = MSS_PLUGIN_DIR . 'includes/' . $path . '.php';
        
        if ( file_exists( $file ) ) {
            require_once $file;
        }
    }
});

// Initialize plugin on plugins_loaded
add_action( 'plugins_loaded', function() {
    if ( class_exists( 'KashifBakers\\Subscriptions\\Plugin' ) ) {
        \KashifBakers\Subscriptions\Plugin::getInstance();
    }
});

// Activation hook - create or update the database table
register_activation_hook( __FILE__, function() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE {$wpdb->prefix}mss_subscriptions (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  user_id bigint(20) NOT NULL,
  order_id bigint(20) NOT NULL,
  product_id bigint(20) NOT NULL,
  billing_period varchar(50) DEFAULT '' NOT NULL,
  status varchar(50) DEFAULT 'active' NOT NULL,
  created_at datetime DEFAULT CURRENT_TIMESTAMP,
  next_billing_date date DEFAULT NULL,
  stripe_subscription_id varchar(255) DEFAULT '' NOT NULL,
  PRIMARY KEY  (id)
) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
});