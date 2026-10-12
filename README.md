# My Subscription System

A custom WooCommerce plugin that adds recurring subscriptions with Stripe. It's built from scratch, with no third-party subscription plugin.

**Status:** working in test mode. Being prepared for live use.

## What works now

- "Billing period" option on any WooCommerce product: none, monthly or yearly
- A subscription is created automatically when an order is paid or completed
- Customers start monthly or yearly payments through Stripe Checkout, from the order thank-you page
- Stripe webhooks with signature verification. Activation, renewals, failed payments and cancellations update subscriptions automatically
- Next billing date updated from Stripe on every renewal
- Admin page (WP Admin → Subscriptions) listing all subscriptions with their status
- REST API endpoint `kashif-bakers/v1/subscriptions`, restricted to shop managers
- Private debug log, protected from public access

## Architecture

- `my-subscription-system.php`: main file, autoloader, database table on activation
- `includes/Plugin.php`: loads all parts, admin menu and order hooks
- `includes/ProductFields.php`: billing period field on products
- `includes/Subscription.php`: creates and cancels subscriptions
- `includes/SubscriptionCheckout.php`: "Start payments" button and Stripe Checkout session
- `includes/StripeClient.php`: small client for the Stripe API
- `includes/WebhookHandler.php`: verifies Stripe signatures and updates subscriptions
- `includes/RestApi.php`: REST endpoint
- `includes/Logger.php`: debug logging

## Setup

1. Upload the plugin folder to `wp-content/plugins/` and activate it (WooCommerce required).
2. Add your Stripe keys to `wp-config.php` (never put keys in the plugin code):

   define('MSS_STRIPE_SECRET_KEY', 'sk_test_...');
   define('MSS_STRIPE_WEBHOOK_SECRET', 'whsec_...');

3. In Stripe, add a webhook pointing to:
   `https://yoursite.com/wp-json/kashif-bakers/v1/webhooks/stripe`
   with the events `checkout.session.completed`, `invoice.paid`, `invoice.payment_failed` and `customer.subscription.deleted`.
4. Edit a product, choose a billing period, and save.

## Tested with

WordPress 7.1, WooCommerce 11, PHP 8.3, Stripe test mode.

## Author

Riaz Ahmad, WordPress and WooCommerce developer
Riaz Ahmad · [Portfolio](https://kashifbakers.shop)

## License

GPL-2.0-or-later
