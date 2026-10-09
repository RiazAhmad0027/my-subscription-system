<?php
namespace KashifBakers\Subscriptions;

class Plugin {
    private static $instance = null;
    private $logger;
    
    private function __construct() {
        $this->logger = new Logger();
        $this->initializeHooks();
        $this->logger->log( "Plugin initialized" );
    }
    
    public static function getInstance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    private function initializeHooks() {
    new \KashifBakers\Subscriptions\RestApi();  // ✅ CORRECT - full namespace
        // Hook into WooCommerce order completion
        add_action( 'woocommerce_order_status_completed', array( $this, 'onOrderComplete' ) );
        add_action( 'woocommerce_order_status_processing', array( $this, 'onOrderProcess' ) );
        
        // Admin menu
        add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );
    }
    
    public function onOrderComplete( $order_id ) {
        $this->logger->log( "Order completed: $order_id" );
        // Will be expanded with Subscription creation
    }
    
    public function onOrderProcess( $order_id ) {
        $this->logger->log( "Order processing: $order_id" );
    }
    
    public function addAdminMenu() {
        add_menu_page(
            'Subscriptions Dashboard',
            'Subscriptions',
            'manage_options',
            'mss-dashboard',
            array( $this, 'renderDashboard' ),
            'dashicons-chart-line',
            57
        );
    }
    
    public function renderDashboard() {
        echo '<div class="wrap">';
        echo '<h1>Subscription Dashboard</h1>';
        echo '<p>Subscriptions management coming soon...</p>';
        echo '</div>';
    }
}