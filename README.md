# Lean Woocommerce Api
Description: Woocommerce API endpoints for Lean.

Version: 0.1.0


### Getting Started
### Endpoints
### Hooks
There are some actions you can use to add functionality to this plugin.

##### ln_wc_pre_order
Called before the order is created.

Parameters
```php
$request;   // WP_REST_Request.
$cart;      // WC_Cart Cart instance.
```
