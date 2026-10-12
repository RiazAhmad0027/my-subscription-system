<?php
namespace KashifBakers\Subscriptions;

class ProductFields {

    public function __construct() {
        add_action( 'woocommerce_product_options_general_product_data', array( $this, 'renderField' ) );
        add_action( 'woocommerce_admin_process_product_object', array( $this, 'saveField' ) );
    }

    // Show a "Billing period" dropdown on the product edit screen
    public function renderField() {
        woocommerce_wp_select( array(
            'id'          => '_mss_billing_period',
            'label'       => 'Subscription billing period',
            'options'     => array(
                ''      => 'Not a subscription',
                'month' => 'Monthly',
                'year'  => 'Yearly',
            ),
            'desc_tip'    => true,
            'description' => 'Choose a period to make this product a subscription.',
        ) );
    }

    // Save the value safely when the product is saved
    public function saveField( $product ) {
        $allowed = array( '', 'month', 'year' );
        $value   = isset( $_POST['_mss_billing_period'] )
            ? sanitize_text_field( wp_unslash( $_POST['_mss_billing_period'] ) )
            : '';

        if ( ! in_array( $value, $allowed, true ) ) {
            $value = '';
        }

        $product->update_meta_data( '_mss_billing_period', $value );
    }
}