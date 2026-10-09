<?php
namespace KashifBakers\Subscriptions;

class Subscription {
    private $id;
    private $order_id;
    private $user_id;
    private $product_id;
    private $billing_period;
    private $status = 'active';
    private $logger;
    
    public function __construct() {
        $this->logger = new Logger();
    }
    
    public function create( $order_id ) {
        try {
            global $wpdb;
            $table = $wpdb->prefix . 'mss_subscriptions';
            
            $order = wc_get_order( $order_id );
            $user_id = $order->get_user_id();
            $items = $order->get_items();
            
            foreach ( $items as $item ) {
                $product_id = $item->get_product_id();
                $product = wc_get_product( $product_id );
                
                // Check if product is subscription type
                if ( $product->get_meta( 'billing_period' ) ) {
                    $result = $wpdb->insert( $table, array(
                        'user_id' => $user_id,
                        'order_id' => $order_id,
                        'product_id' => $product_id,
                        'billing_period' => $product->get_meta( 'billing_period' ),
                        'status' => 'active',
                        'created_at' => current_time( 'mysql' )
                    ));
                    
                    if ( $result ) {
                        $this->id = $wpdb->insert_id;
                        $this->logger->log( "Subscription created: ID $this->id for Order $order_id" );
                    }
                }
            }
            
        } catch ( Exception $e ) {
            $this->logger->log( "Error creating subscription: " . $e->getMessage(), 'error' );
            throw $e;
        }
    }
    
    public function getStatus() {
        return $this->status;
    }
    
    public function cancel() {
        global $wpdb;
        $table = $wpdb->prefix . 'mss_subscriptions';
        
        $wpdb->update( $table,
            array( 'status' => 'cancelled' ),
            array( 'id' => $this->id )
        );
        
        $this->logger->log( "Subscription cancelled: ID $this->id" );
    }
}