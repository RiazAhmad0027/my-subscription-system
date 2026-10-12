<?php
namespace KashifBakers\Subscriptions\Payments;

use KashifBakers\Subscriptions\Logger;

class StripeGateway implements PaymentGateway {
    private $api_key;
    private $logger;
    
    public function __construct( $api_key = null ) {
        $this->api_key = $api_key ?: STRIPE_API_KEY;
        $this->logger = new Logger();
    }
    
    public function processPayment( $amount ) {
        try {
            // For now, just log the payment attempt
            // Full Stripe integration will come in Day 25
            $this->logger->log( "Stripe payment attempt: Rs $amount" );
            
            return 'stripe_' . uniqid();
            
        } catch ( Exception $e ) {
            $this->logger->log( "Stripe payment failed: " . $e->getMessage(), 'error' );
            throw new Exception( "Payment failed: " . $e->getMessage() );
        }
    }
    
    public function refundPayment( $transaction_id ) {
        try {
            $this->logger->log( "Stripe refund attempt: $transaction_id" );
            return true;
            
        } catch ( Exception $e ) {
            $this->logger->log( "Refund failed: " . $e->getMessage(), 'error' );
            throw $e;
        }
    }
    
    public function getTransactionStatus( $transaction_id ) {
        try {
            $this->logger->log( "Checking status: $transaction_id" );
            return 'completed';
            
        } catch ( Exception $e ) {
            $this->logger->log( "Error getting status: " . $e->getMessage(), 'error' );
            throw $e;
        }
    }
}