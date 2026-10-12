# My Subscription System

A custom WooCommerce subscription plugin with Stripe, built from scratch in PHP using object-oriented architecture.

**Status: in active development.** Tested on a local WordPress site.

## What works now

- Subscription billing period (monthly / yearly) on WooCommerce products
- Completed orders create subscription records with a next billing date
- Duplicate protection (one subscription per order item)
- Stripe Checkout in subscription mode (recurring card payments)
- Admin dashboard listing all subscriptions
- Secured REST endpoint: `GET /wp-json/kashif-bakers/v1/subscriptions` (shop managers only)
- Logging to a protected uploads folder

## In progress

- Stripe webhooks with signature verification (activation, renewals, failed payments, cancellations)

## Architecture

- `Plugin`: singleton that loads everything
- `ProductFields`: billing period field on products
- `Subscription`: creates subscriptions from orders
- `SubscriptionCheckout`: Stripe Checkout button and session
- `StripeClient`: small Stripe API helper (no external libraries)
- `RestApi`, `Logger`

## Setup

Stripe keys are never stored in the code. Add them to `wp-config.php`:

```php
define( 'MSS_STRIPE_SECRET_KEY', 'sk_test_...' );
```

## Tested with

WordPress 7.1 · WooCommerce 11 · PHP 8.3 · Stripe test mode

## Author

Riaz Ahmad · [Portfolio](https://kashifbakers.shop)

## License

GPL-2.0-or-later
