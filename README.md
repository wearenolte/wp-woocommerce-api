# Lean Woocommerce Api
Description: Woocommerce API endpoints for Lean.

Version: 0.1.0


### Getting Started
TODO

### Endpoints
TODO

### Error codes
The API can return some custom errors. You can find a explanation of
these errors here.

* `method_not_registered` - This means you are trying to use a non allowed method. For example, if the endpoint
only accepts `POST` and you are sending a `GET`.

* `request_error` - This means your request could not be completed. This usually happens when you miss a parameter, or
the requirements for this request were not met. More information can be found in the response message.

### Filters
There are some actions you can use to add functionality to this plugin.

##### ln_wc_pre_order
Called before the order is created.

Parameters
```php
$request;   // WP_REST_Request.
$cart;      // WC_Cart Cart instance.
```

##### ln_wc_pre_order
Called after the order is created.
```php
$request;   // WP_REST_Request.
$cart;      // WC_Cart Cart instance.
```

#### ln_wc_pre_update_guest_order
Called before the order shipping/billing address are updated (For Guest purchases only).
```php
$request;   // WP_REST_Request.
$order;     // WC_Order Order instance.
```

#### ln_wc_after_update_guest_order
Called after the order shipping/billing address are updated (For Guest purchases only).
```php
$request;   // WP_REST_Request.
$order;     // WC_Order Order instance.
```