# LaraPal
[![License](https://poser.pugx.org/obydul/larapal/license)](https://packagist.org/packages/obydul/larapal)
[![Latest Unstable Version](https://poser.pugx.org/obydul/larapal/v/unstable)](https://packagist.org/packages/obydul/larapal)

<a name="introduction"></a>
## Introduction

By using this plugin you can process or refund payments from PayPal in your Laravel application.

<a name="paypal-api-credentials"></a>
## PayPal API Credentials

This package uses the classic paypal express checkout. Refer to this link on how to create API credentials:

https://developer.paypal.com/docs/classic/api/apiCredentials/#create-an-api-signature

<a name="installation"></a>
## Installation

* Use following command to install:

```bash
composer require obydul/larapal
```

* Add the service provider to your `$providers` array in `config/app.php` file like: 

```php
Obydul\LaraPal\LarapalServiceProvider::class
```

* Run the following command to publish configuration:

```bash
php artisan vendor:publish
```
*  Then choose 'Obydul\LaraPal\LarapalServiceProvider':

![larapal-publish](https://user-images.githubusercontent.com/13184472/51436553-8c4e9b00-1cb9-11e9-8a03-ff55841ec3df.png)

Installation completed.

<a name="configuration"></a>
## Configuration

* After installation, you will need to add your paypal settings. Following is the code you will find in **config/larapal.php**, which you should update accordingly.

```php
return [
    'mode' => 'sandbox', // sandbox or live
    'api_username' => 'PAYPAL_API_USERNAME',
    'api_password' => 'PAYPAL_API_PASSWORD',
    'api_signature' => 'PAYPAL_API_SIGNATURE',
    'returnUrl' => 'RETURN_URL',
    'cancelUrl' => 'CANCEL_URL'
];
```
* If there is a problem in your Laravel project, the config `config/larapal.php` may not work. At this situation you can try by entering API Credentials at `YourProject/vendor/obydul/larapal/config/config.php`.

<a name="usage"></a>
## Usage

Following are some ways through which you can access the paypal provider:

```php
// Import the class namespaces first, before using it directly
use Obydul\LaraPal\Services\ExpressCheckout;

$paypal = new ExpressCheckout();  // Create object instance.

// Redirect user to PayPal to obtain charging permissions
$paypal->doExpressCheckout(123.45, 'LaraPal Test Checkout', 'invoice_id', 'USD'); // single payment
$paypal->doExpressMultipleCheckout($items, 'invoice_id', 'USD', false, $customFields); // multiple items

// Perform payment, token and PayerID are being returned with GET response from PayPal
$paypal->doSinglePayment($_GET['token'], $_GET['PayerID']; // single payment
$paypal->doMultiplePayment($_GET['token'], $_GET['PayerID']; // multiple payment

// Perform refund based on transaction ID
$paypal->doRefund($transactionId, 'invoice_id', false, 0, 'USD', ''); // full refund
$paypal->doRefund($transactionId, 'invoice_id', true, 5.15, 'USD', ''); // partial refund
```

#### doExpressCheckout
```php
// Structure - invoice ID must be unique
doExpressCheckout(AMOUNT, 'DESCRIPTION', 'INVOICE', 'CURRENCY', SHIPPING, CUSTOMFIELDS);
doExpressMultipleCheckout(ITEMS, 'INVOICE', 'CURRENCY', SHIPPING, CUSTOMFIELDS);

// Normal call
doExpressCheckout(123.45, 'LaraPal Test Checkout', 'invoice_id', 'USD');

// Pass custom fields to your order
$customFields = array(
    'identifier' => "Example.com/ID",
    'customerEmail' => "customer@email.com",
);

// Now do the express checkout. If you don't like to pass custom fields, then remove $customFields.
$paypal->doExpressCheckout(123.45, 'LaraPal Test Checkout', 'invoice_id', 'USD', false, $customFields);

// multiple payment
$items = array(
    array(
        "name" => "Product 1",
        "price" => "12.25",
        "quantity" => "1",
        "product_id" => 111
    ),
    array(
        "name" => "Product 2",
        "price" => "25.50",
        "quantity" => "1",
        "product_id" => 112
    )
);

$paypal->doExpressMultipleCheckout($items, $invoice_id, 'USD', false, $customFields);

```

#### doRefund
```php
// Structure
doExpressCheckout(TRANSACTION_ID, 'INVOICE_ID', 'IS_PARTIAL', PARTIAL_AMOUNT, CURRENCY, NOTE);

// Full refund
doRefund($transactionId, 'invoice_id', false, 0, 'USD', '')

// Partial refund
doRefund($transactionId, 'invoice_id', true, 12.25, 'USD', '') // you can pass note also
```

<a name="example"></a>
## Example

After installing LaraPal, configure the config file `config/larapal.php` and add returnUrl & cancelUrl like:
```php
'returnUrl' => 'http://example.com/do-the-payment',
'cancelUrl' => 'http://example.com/cancel-payment'
```
 
Now create routes and create a controller named 'PayPalController':

```php
// Routes
Route::get('payment-status', 'PayPalController@paymentStatus')->name('payment-status');
Route::get('single-payment', 'PayPalController@singlePayment');
Route::get('multiple-payment', 'PayPalController@multipleItemsPayment');
Route::get('do-the-payment', 'PayPalController@doThePayment');
Route::get('refund-payment', 'PayPalController@doRefund');
Route::get('cancel-payment', 'PayPalController@cancelPayment');

// Controller
php artisan make:controller PayPalController
```
Now in your PayPalController add this: [PayPalController.php](https://gist.github.com/mdobydullah/44f52dbb1cf9f954d66a15b93c95640d)

Now just visit the URL to make an action:

```php
http://example.com/single-payment
http://example.com/multiple-payment
http://example.com/refund-payment
```

After successful payment, you will be redirected to returnUrl: `http://example.com/payment-status` and will see a message like: `Success! Transaction ID: 9TR987531T2702301`.

## License

The MIT License (MIT). Please see [License File](https://github.com/mdobydullah/larapal/blob/master/LICENSE) for more information.


<a name=""></a>
## Others
Inspired by [paypal-express-checkout](https://github.com/romaonthego/paypal-express-checkout) and thank you, [romaonthego](https://github.com/romaonthego).

In case of any issues, kindly create one on the [Issues](https://github.com/mdobydullah/larapal/issues) section.


Thank you for installing LaraPal.