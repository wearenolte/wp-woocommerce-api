# Lean Woocommerce Api
Description: Woocommerce API endpoints for Lean.
Status: In development.

Version: 0.1.0


### Getting Started
TODO

---
### Endpoints
##### `/ecommerce/cart`
`GET` - Get the current user's cart.
`POST` - Add a product to the user's cart. `product_id` needs to be specified as an url parameter.

##### `/ecommerce/order`
`GET` - Get all logged users orders. If the user is not logged in, it returns an empty array.
`POST` - Create an order using the current user's cart. It returns the created order or an error.

##### `/ecommerce/checkout`
`POST` - It requires `order_id` parameter in URL. This endpoint tries to make the payment of the
order passed as parameters. 

**Note:** Please read the following considerations.

1. The user has to be the owner of the order, or it should be a guest order.
2. The plugin will try the **first active** payment gateway configured in your WP dashboard.
3. More information can be required depending on the Gateway used. More information should be available
 in the order panel of the dashboard if the payment fails.

### Error codes
The API can return some custom errors. You can find a explanation of
these errors here. Also, all error responses should show an extra message to have a better
understanding of the problem.

* `method_not_registered` - This means you are trying to use a non allowed method. For example, if the endpoint
only accepts `POST` and you are sending a `GET`.

* `request_error` - This means your request could not be completed. This usually happens when you miss a parameter, or
the requirements for this request were not met. More information can be found in the response message.

* `bad_configured` - This means, there is some missing configuration to do in the Wordpress admin panel.

* `bad_permissions` - This means, the user does not have permissions to perform the desired action.

* `internal_error` - This means, something went wrong in the Server but we don't have more information. Server logs
and/or Woocommerce log should have more information about this.

### Hooks
There are some actions you can use to add functionality to this plugin.

#### Actions:
##### ln_wc_pre_order
Called before the order is created.

Parameters
```php
$request;   // WP_REST_Request.
$cart;      // WC_Cart Cart instance.
```

##### ln_wc_after_order
Called after the order is created.
```php
$request;   // WP_REST_Request.
$order;     // WC_Order Order instance after it has been created.
```

##### ln_wc_pre_update_guest_order
Called before the order shipping/billing address are updated (For Guest purchases only).
```php
$request;   // WP_REST_Request.
$order;     // WC_Order Order instance.
```

##### ln_wc_after_update_guest_order
Called after the order shipping/billing address are updated (For Guest purchases only).
```php
$request;   // WP_REST_Request.
$order;     // WC_Order Order instance.
```

##### ln_wc_pre_checkout
Called before the payment is processed. You can use this hook to, for example, load some information on the $_POST object.
```php
$order_id;     // int Order id.
```

##### ln_wc_after_checkout
Called after the payment is processed.
```php
$order_id;     // int Order id.
```

#### Filters: