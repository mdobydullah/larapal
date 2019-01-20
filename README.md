# LaraPal

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

<a name="usage"></a>
## Usage

Following are some ways through which you can access the paypal provider:

```php
// Import the class namespaces first, before using it directly
use Obydul\LaraPal\Services\ExpressCheckout;

$paypal = new ExpressCheckout();  // Create object instance.

// Redirect user to PayPal to obtain charging permissions
$paypal->doExpressCheckout(123.45, 'LaraPal Test Checkout', 'invoice1', 'USD');

// Perform payment, token and PayerID are being returned with GET response from PayPal
$paypal->doPayment($_GET['token'], $_GET['PayerID'];

// Perform refund based on transaction ID
$paypal->doRefund($transactionId, 'invoice1', false, 0, 'USD', '')

```

<a name="support"></a>
## Support

In case of any issues, kindly create one on the [Issues](https://github.com/mdobydullah/larapal/issues) section.