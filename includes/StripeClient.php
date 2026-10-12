<?php
namespace KashifBakers\Subscriptions;

class StripeClient {
    private $secret_key;

    public function __construct() {
        $this->secret_key = defined( 'MSS_STRIPE_SECRET_KEY' ) ? MSS_STRIPE_SECRET_KEY : '';
    }

    public function isConfigured() {
        return ! empty( $this->secret_key );
    }

    // Send a request to the Stripe API
    public function post( $endpoint, $params ) {
        $response = wp_remote_post( 'https://api.stripe.com/v1/' . $endpoint, array(
            'headers' => array( 'Authorization' => 'Bearer ' . $this->secret_key ),
            'body'    => $params,
            'timeout' => 30,
        ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $code = wp_remote_retrieve_response_code( $response );
        $body = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( $code >= 400 ) {
            $message = isset( $body['error']['message'] ) ? $body['error']['message'] : 'Stripe error';
            return new \WP_Error( 'stripe_error', $message );
        }

        return $body;
    }
}