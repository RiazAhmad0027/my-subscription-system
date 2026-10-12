<?php
namespace KashifBakers\Subscriptions;

class WebhookHandler {
    private $logger;

    public function __construct() {
        $this->logger = new Logger();
        add_action( 'rest_api_init', array( $this, 'registerRoute' ) );
    }

    public function registerRoute() {
        register_rest_route( 'kashif-bakers/v1', '/webhooks/stripe', array(
            'methods'             => 'POST',
            'callback'            => array( $this, 'handle' ),
            // Public on purpose: Stripe must reach it. The signature check is the security.
            'permission_callback' => '__return_true',
        ) );
    }

    public function handle( \WP_REST_Request $request ) {
        $payload = $request->get_body();
        $header  = $request->get_header( 'stripe_signature' );

        if ( ! $this->verifySignature( $payload, $header ) ) {
            $this->logger->log( 'Webhook rejected: invalid signature', 'error' );
            return new \WP_REST_Response( array( 'error' => 'Invalid signature' ), 400 );
        }

        $event = json_decode( $payload, true );
        $type  = isset( $event['type'] ) ? $event['type'] : '';
        $obj   = isset( $event['data']['object'] ) ? $event['data']['object'] : array();

        switch ( $type ) {
            case 'checkout.session.completed':
                $this->onCheckoutCompleted( $obj );
                break;
            case 'invoice.paid':
                $this->onInvoicePaid( $obj );
                break;
            case 'invoice.payment_failed':
                $this->updateByStripeId( $this->getInvoiceSubscriptionId( $obj ), array( 'status' => 'past_due' ) );
                break;
            case 'customer.subscription.deleted':
                $this->updateByStripeId( isset( $obj['id'] ) ? $obj['id'] : '', array( 'status' => 'cancelled' ) );
                break;
        }

        return new \WP_REST_Response( array( 'received' => true ), 200 );
    }

    private function verifySignature( $payload, $header ) {
        $secret = defined( 'MSS_STRIPE_WEBHOOK_SECRET' ) ? MSS_STRIPE_WEBHOOK_SECRET : '';
        if ( empty( $secret ) || empty( $header ) ) {
            return false;
        }

        $timestamp  = '';
        $signatures = array();
        foreach ( explode( ',', $header ) as $part ) {
            $pair = explode( '=', trim( $part ), 2 );
            if ( count( $pair ) !== 2 ) {
                continue;
            }
            if ( 't' === $pair[0] ) {
                $timestamp = $pair[1];
            }
            if ( 'v1' === $pair[0] ) {
                $signatures[] = $pair[1];
            }
        }

        if ( ! $timestamp || abs( time() - (int) $timestamp ) > 300 ) {
            return false;
        }

        $expected = hash_hmac( 'sha256', $timestamp . '.' . $payload, $secret );
        foreach ( $signatures as $sig ) {
            if ( hash_equals( $expected, $sig ) ) {
                return true;
            }
        }
        return false;
    }

    private function onCheckoutCompleted( $session ) {
        $id         = isset( $session['metadata']['mss_subscription_id'] ) ? absint( $session['metadata']['mss_subscription_id'] ) : 0;
        $stripe_sub = isset( $session['subscription'] ) ? sanitize_text_field( $session['subscription'] ) : '';

        if ( ! $id || ! $stripe_sub ) {
            return;
        }

        global $wpdb;
        $wpdb->update(
            $wpdb->prefix . 'mss_subscriptions',
            array( 'status' => 'active', 'stripe_subscription_id' => $stripe_sub ),
            array( 'id' => $id ),
            array( '%s', '%s' ),
            array( '%d' )
        );

        $this->logger->log( "Subscription $id activated (Stripe $stripe_sub)" );
    }

    private function onInvoicePaid( $invoice ) {
        $stripe_sub = $this->getInvoiceSubscriptionId( $invoice );
        $period_end = isset( $invoice['lines']['data'][0]['period']['end'] )
            ? (int) $invoice['lines']['data'][0]['period']['end']
            : 0;

        $data = array( 'status' => 'active' );
        if ( $period_end ) {
            $data['next_billing_date'] = wp_date( 'Y-m-d', $period_end );
        }

        $this->updateByStripeId( $stripe_sub, $data );
    }

    private function getInvoiceSubscriptionId( $invoice ) {
        if ( ! empty( $invoice['subscription'] ) ) {
            return sanitize_text_field( $invoice['subscription'] );
        }
        if ( ! empty( $invoice['parent']['subscription_details']['subscription'] ) ) {
            return sanitize_text_field( $invoice['parent']['subscription_details']['subscription'] );
        }
        return '';
    }

    private function updateByStripeId( $stripe_sub, $data ) {
        if ( empty( $stripe_sub ) ) {
            return;
        }

        global $wpdb;
        $updated = $wpdb->update(
            $wpdb->prefix . 'mss_subscriptions',
            $data,
            array( 'stripe_subscription_id' => $stripe_sub )
        );

        $this->logger->log( "Stripe $stripe_sub updated (" . implode( ', ', array_keys( $data ) ) . "), rows: " . (int) $updated );
    }
}