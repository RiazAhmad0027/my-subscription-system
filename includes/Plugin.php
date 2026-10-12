<?php
namespace KashifBakers\Subscriptions;

class Plugin {
    private static $instance = null;
    private $logger;

    private function __construct() {
        $this->logger = new Logger();
        $this->initializeHooks();
    }

    public static function getInstance() {
        if ( self::$instance === null ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function initializeHooks() {
        new RestApi();
        new ProductFields();
        new SubscriptionCheckout();
        new WebhookHandler();
        // Paid online (e.g. Stripe) or marked Completed by the shop
        add_action( 'woocommerce_payment_complete', array( $this, 'createSubscriptions' ) );
        add_action( 'woocommerce_order_status_completed', array( $this, 'createSubscriptions' ) );

        add_action( 'admin_menu', array( $this, 'addAdminMenu' ) );
    }

    public function createSubscriptions( $order_id ) {
        $this->logger->log( "Checking order $order_id for subscriptions" );
        $subscription = new Subscription();
        $subscription->createFromOrder( $order_id );
    }

    public function addAdminMenu() {
        add_menu_page(
            'Subscriptions',
            'Subscriptions',
            'manage_options',
            'mss-dashboard',
            array( $this, 'renderDashboard' ),
            'dashicons-chart-line',
            57
        );
    }

    public function renderDashboard() {
        if ( ! current_user_can( 'manage_options' ) ) {
            return;
        }

        global $wpdb;
        $table = $wpdb->prefix . 'mss_subscriptions';
        $rows  = $wpdb->get_results( "SELECT * FROM $table ORDER BY id DESC LIMIT 100" );

        echo '<div class="wrap"><h1>Subscriptions</h1>';

        if ( empty( $rows ) ) {
            echo '<p>No subscriptions yet.</p></div>';
            return;
        }

        echo '<table class="widefat striped"><thead><tr>';
        echo '<th>ID</th><th>Customer</th><th>Order</th><th>Product</th>';
        echo '<th>Period</th><th>Status</th><th>Next billing</th>';
        echo '</tr></thead><tbody>';

        foreach ( $rows as $row ) {
            $user    = get_userdata( $row->user_id );
            $product = wc_get_product( $row->product_id );
            $order   = wc_get_order( $row->order_id );

            printf(
                '<tr><td>%d</td><td>%s</td><td><a href="%s">#%d</a></td><td>%s</td><td>%s</td><td>%s</td><td>%s</td></tr>',
                (int) $row->id,
                esc_html( $user ? $user->display_name : 'Guest' ),
                esc_url( $order ? $order->get_edit_order_url() : '#' ),
                (int) $row->order_id,
                esc_html( $product ? $product->get_name() : '(deleted product)' ),
                esc_html( $row->billing_period ),
                esc_html( $row->status ),
                esc_html( $row->next_billing_date )
            );
        }

        echo '</tbody></table></div>';
    }
}