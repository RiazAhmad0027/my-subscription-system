<?php
namespace KashifBakers\Subscriptions\Payments;

interface PaymentGateway {
    public function processPayment( $amount );
    public function refundPayment( $transaction_id );
    public function getTransactionStatus( $transaction_id );
}