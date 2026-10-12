<?php
namespace KashifBakers\Subscriptions;

class Subscription {
    private $logger;

    public function __construct() {
        $this->logger = new Logger();
    }

    // Create subscriptions for all subscription products in an order
    public function createFromOrder( $order_id, $status = 'active' ) {        global $wpdb;
        $table = $wpdb->prefix . 'mss_subscriptions';

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        foreach ( $order->get_items() as $item ) {
            $product_id = $item->get_product_id();
            $product    = wc_get_product( $product_id );

            if ( ! $product ) {
                continue;
            }

            $period = $product->get_meta( '_mss_billing_period' );
            if ( ! in_array( $period, array( 'month', 'year' ), true ) ) {
                continue; // Not a subscription product
            }

            // Don't create the same subscription twice
            $exists = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM $table WHERE order_id = %d AND product_id = %d",
                $order_id,
                $product_id
            ) );
            if ( $exists ) {
                continue;
            }

            $next = ( 'year' === $period ) ? '+1 year' : '+1 month';

            $result = $wpdb->insert(
                $table,
                array(
                    'user_id'           => $order->get_user_id(),
                    'order_id'          => $order_id,
                    'product_id'        => $product_id,
                    'billing_period'    => $period,
                    'status'            => $status,                    'created_at'        => current_time( 'mysql' ),
                    'next_billing_date' => wp_date( 'Y-m-d', strtotime( $next ) ),
                ),
                array( '%d', '%d', '%d', '%s', '%s', '%s', '%s' )
            );

            if ( $result ) {
                $this->logger->log( "Subscription created: ID {$wpdb->insert_id} for order $order_id" );
            } else {
                $this->logger->log( "Could not create subscription for order $order_id: {$wpdb->last_error}", 'error' );
            }
        }
    }

    // Cancel a subscription by its ID
    public function cancel( $subscription_id ) {
        global $wpdb;
        $table = $wpdb->prefix . 'mss_subscriptions';

        $wpdb->update(
            $table,
            array( 'status' => 'cancelled' ),
            array( 'id' => (int) $subscription_id ),
            array( '%s' ),
            array( '%d' )
        );

        $this->logger->log( "Subscription cancelled: ID $subscription_id" );
    }
}