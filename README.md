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
$paypal->doExpressCheckout(123.45, 'LaraPal Test Checkout', 'invoice1', 'USD');

// Perform payment, token and PayerID are being returned with GET response from PayPal
$paypal->doPayment($_GET['token'], $_GET['PayerID'];

// Perform refund based on transaction ID
$paypal->doRefund($transactionId, 'invoice1', false, 0, 'USD', '');
```

#### doExpressCheckout
```php
// Structure
doExpressCheckout(AMOUNT, 'NOTE', 'INVOICE', 'CURRENCY', SHIPPING, CUSTOMFIELDS); // invoice ID must be unique

// Normal call
doExpressCheckout(123.45, 'LaraPal Test Checkout', 'invoice1', 'USD');

// Pass custom fields to your order
$customFields = array(
    'identifier' => "Example.com/ID",
    'customerEmail' => "customer@email.com",
);

// Now do the express checkout
$paypal->doExpressCheckout(123.45, 'LaraPal Test Checkout', 'invoice1', 'USD', false, $customFields);
```

#### doRefund
```php
// Structure
doExpressCheckout(TRANSACTION_ID, 'INVOICE_ID', 'IS_PARTIAL', PARTIAL_AMOUNT, CURRENCY, NOTE);

// Full refund
doRefund($transactionId, 'invoice1', false, 0, 'USD', '')

// Partial refund
doRefund($transactionId, 'invoice1', true, 12.25, 'USD', '') // you can pass note also
```

<a name="example"></a>
## Example

After installing LaraPal, configure the config file `config/larapal.php` and add returnUrl & cancelUrl like:
```php
'returnUrl' => 'http://example.com/paypal?action=success',
'cancelUrl' => 'http://example.com/paypal?action=cancel'
```
 
Now create a route and create a controller named 'PayPalController':

```php
// Route
Route::get('paypal', 'PayPalController@index');

// Controller
php artisan make:controller PayPalController

```

Now in your PayPalController add this:

```php
namespace App\Http\Controllers;

use Obydul\LaraPal\Services\ExpressCheckout;

class PayPalController extends Controller
{

    /**
     * payment process
     */
    public function index()
    {
        // If you face this type of message "Cannot modify header information", then add this: ob_start();

        $paypal = new ExpressCheckout();

        if (isset($_GET['action'])) {
            $action = $_GET['action'];

            switch ($action) {
                case "pay": // Index page, here you should be redirected to Paypal
                    $paypal->doExpressCheckout(123.45, 'LaraPal Test Checkout', 'invoice1', 'USD');
                    break;

                case "success": // Paypal says everything's fine, do the charge (user redirected to $gateway->returnUrl)
                    if ($transaction = $paypal->doPayment($_GET['token'], $_GET['PayerID'])) {
                        // In the $transaction array there are many information. You can var_dump($transaction) and display message as you want. You can also store data to database from here.
                        echo "Success! Transaction ID: " . $transaction['TRANSACTIONID'];
                    } else {
                        echo "Debugging what went wrong: ";
                    }
                    break;

                case "refund":
                    // Enter your transaction ID
                    $transactionId = '9TR987531T2702301';
                    if ($refund = $paypal->doRefund($transactionId, 'invoice9', false, 0, 'USD', '')) {
                        // Like $transaction array there are many information in $refund array.
                        echo 'Refunded: ' . $refund['GROSSREFUNDAMT'];
                    } else {
                        echo "Debugging what went wrong: ";
                    }
                    break;

                case "cancel": // User canceled and returned to your store (to $gateway->cancelUrl)
                    echo "User canceled";
                    break;
            }
        } else {
            echo "Please pass an action. Actions: pay, success or refund";
        }

    }

}
```

Now just visit the URL to make a transaction:

```php
http://example.com/paypal?action=pay
```

After successful payment, you will be redirected to returnUrl: `http://example.com/paypal?action=success` and see a message like: `Success! Transaction ID: 9TR987531T2702301`.

## License

The MIT License (MIT). Please see [License File](https://github.com/mdobydullah/larapal/blob/master/LICENSE) for more information.


<a name=""></a>
## Others
Inspired by [paypal-express-checkout](https://github.com/romaonthego/paypal-express-checkout) and thank you, [romaonthego](https://github.com/romaonthego).

In case of any issues, kindly create one on the [Issues](https://github.com/mdobydullah/larapal/issues) section.


Thank you for installing LaraPal.