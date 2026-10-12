<?php
namespace KashifBakers\Subscriptions;

class SubscriptionCheckout {

    public function __construct() {
        add_action( 'woocommerce_thankyou', array( $this, 'showButton' ), 20 );
        add_action( 'admin_post_mss_start_subscription', array( $this, 'startCheckout' ) );
    }

    // Show a "Start monthly payments" button on the thank-you page
    public function showButton( $order_id ) {
        if ( ! $order_id || ! is_user_logged_in() ) {
            return;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order || (int) $order->get_user_id() !== get_current_user_id() ) {
            return;
        }

        // Make sure a (pending) subscription exists for this order
        $subscription = new Subscription();
        $subscription->createFromOrder( $order_id, 'pending' );

        global $wpdb;
        $table = $wpdb->prefix . 'mss_subscriptions';
        $rows  = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table WHERE order_id = %d AND stripe_subscription_id = ''",
            $order_id
        ) );

        foreach ( $rows as $row ) {
            $product = wc_get_product( $row->product_id );
            if ( ! $product ) {
                continue;
            }
            ?>
            <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:20px 0;">
                <input type="hidden" name="action" value="mss_start_subscription">
                <input type="hidden" name="subscription_id" value="<?php echo esc_attr( $row->id ); ?>">
                <?php wp_nonce_field( 'mss_start_subscription_' . $row->id ); ?>
                <button type="submit" class="button">
                    <?php echo esc_html( 'Start ' . $row->billing_period . 'ly payments for ' . $product->get_name() ); ?>
                </button>
            </form>
            <?php
        }
    }

    // Create a Stripe Checkout session in subscription mode and redirect
    public function startCheckout() {
        $id = isset( $_POST['subscription_id'] ) ? absint( $_POST['subscription_id'] ) : 0;
        check_admin_referer( 'mss_start_subscription_' . $id );

        global $wpdb;
        $table = $wpdb->prefix . 'mss_subscriptions';
        $row   = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table WHERE id = %d", $id ) );

        if ( ! $row || (int) $row->user_id !== get_current_user_id() ) {
            wp_die( 'Subscription not found.' );
        }

        $product = wc_get_product( $row->product_id );
        $order   = wc_get_order( $row->order_id );
        $stripe  = new StripeClient();

        if ( ! $product || ! $order || ! $stripe->isConfigured() ) {
            wp_die( 'Subscription payments are not available right now.' );
        }

        $return_url = $order->get_checkout_order_received_url();
        $user       = wp_get_current_user();

        $session = $stripe->post( 'checkout/sessions', array(
            'mode'                => 'subscription',
            'success_url'         => $return_url,
            'cancel_url'          => $return_url,
            'customer_email'      => $user->user_email,
            'client_reference_id' => $row->id,
            'metadata'            => array( 'mss_subscription_id' => $row->id ),
            'subscription_data'   => array(
                'metadata' => array( 'mss_subscription_id' => $row->id ),
            ),
            'line_items'          => array(
                array(
                    'quantity'   => 1,
                    'price_data' => array(
                        'currency'     => strtolower( get_woocommerce_currency() ),
                        'unit_amount'  => (int) round( (float) $product->get_price() * 100 ),
                        'recurring'    => array( 'interval' => $row->billing_period ),
                        'product_data' => array( 'name' => $product->get_name() ),
                    ),
                ),
            ),
        ) );

        if ( is_wp_error( $session ) ) {
            $logger = new Logger();
            $logger->log( 'Stripe Checkout error: ' . $session->get_error_message(), 'error' );
            wp_die( esc_html( 'Could not start payment: ' . $session->get_error_message() ) );
        }

        wp_redirect( $session['url'] );
        exit;
    }
}