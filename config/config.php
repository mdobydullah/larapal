<?php
/**
 * PayPal Setting & API Credentials
 * Author: Md. Obydullah <obydul@makpie.com>.
 * Author URL: https://obydul.me
 */

return [
    'mode' => env('LARAPAL_MODE', 'sandbox'), // sandbox or live
    'api_username' => env('LARAPAL_API_USERNAME'), // paypal api username
    'api_password' => env('LARAPAL_API_PASSWORD'), // paypal api password
    'api_signature' => env('LARAPAL_API_SIGNATURE'), // paypal api signature
];
