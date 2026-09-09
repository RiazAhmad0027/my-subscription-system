# My Subscription System

Custom WooCommerce subscription plugin with Stripe integration.

## Features
- ✅ OOP Architecture (Classes, Interfaces, Traits, Namespacing)
- ✅ REST API Endpoints (GET, POST, PUT, DELETE)
- ✅ Stripe Payment Gateway Integration
- ✅ Webhook Handling
- ✅ Security Hardened (Nonces, Sanitization, Escaping)
- ✅ Professional Logging System
- ✅ Production-Ready Code

## Architecture
- Plugin class with singleton pattern
- Subscription management class
- PaymentGateway interface (extensible)
- StripeGateway implementation
- Logger for debugging

## Installation
1. Download the plugin
2. Upload to `/wp-content/plugins/`
3. Activate in WordPress Admin
4. Configure Stripe API keys in settings

## API Endpoints

GET /wp-json/kashif-bakers/v1/subscriptions
POST /wp-json/kashif-bakers/v1/subscriptions
GET /wp-json/kashif-bakers/v1/subscriptions/{id}
PUT /wp-json/kashif-bakers/v1/subscriptions/{id}
DELETE /wp-json/kashif-bakers/v1/subscriptions/{id}
POST /wp-json/kashif-bakers/v1/webhooks/stripe


## Author
**Riaz Ahmad**
- Fiverr: https://fiverr.com/users/riaz0027
- Portfolio: https://kashifbakers.shop
- Email: nangri2211@gmail.com

## License
