<?php
/**
 * PayPal ExpressCheckout Class
 * Author: Md. Obydullah <obydul@makpie.com>.
 * Author URL: https://obydul.me
 */

namespace Obydul\Larapal\Services;

use Obydul\LaraPal\Lib\PaypalBase;

class ExpressCheckout extends PaypalBase
{

    /**
     * Redirect user to PayPal to request payment permissions.  (Single Product)
     *
     * If OK, the customer is redirected to PayPal gateway
     * If error, returns false
     *
     * @param float $amount Amount (2 numbers after decimal point)
     * @param string $desc Item description
     * @param string $invoice (Optional) Your own invoice or tracking number.
     * @param string $currency 3-letter currency code (USD, GBP, CZK etc.)
     * @param array $resultData PayPal response
     *
     * @return array PayPal response
     */
    public function doExpressCheckout($amount, $description, $invoice = '', $currency = 'USD', $shipping = false, &$customFields = array())
    {
        $data = array(
            'PAYMENTACTION' => 'Sale',
            'AMT' => $amount,
            'DESC' => $description,
            'ALLOWNOTE' => "0",
            'CURRENCYCODE' => $currency,
            'METHOD' => 'SetExpressCheckout'
        );

        if ($shipping === false) {
            $data['NOSHIPPING'] = "1";
        } else if ($shipping === true) {
            // let the merchant decide the address
        } else {
            // address specified by the customer
            $data['ADDROVERRIDE'] = 1;
            $data['PAYMENTREQUEST_0_SHIPTONAME'] = $shipping['name'];
            $data['PAYMENTREQUEST_0_SHIPTOSTREET'] = $shipping['street_address_1'];
            $data['PAYMENTREQUEST_0_SHIPTOSTREET2'] = $shipping['street_address_2'];
            $data['PAYMENTREQUEST_0_SHIPTOCITY'] = $shipping['city'];
            $data['PAYMENTREQUEST_0_SHIPTOSTATE'] = $shipping['state_code'];
            $data['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $shipping['country_code'];
            $data['PAYMENTREQUEST_0_SHIPTOZIP'] = $shipping['zip'];
            $data['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = $shipping['phone_number'];
        }

        $data['CUSTOM'] = $amount . '|' . $currency . '|' . $invoice;

        // add custom fields
        if (!empty($customFields)) {
            foreach ($customFields as $customField) {
                $data['CUSTOM'] .= '|' . $customField;
            }
        }

        if ($invoice) $data['INVNUM'] = $invoice;

        return $this->runQueryWithParams($data);
    }

    /**
     * Redirect user to PayPal to request payment permissions. (Multiple Products)
     *
     * @param float $amount Amount (2 numbers after decimal point)
     *
     * @return array PayPal response
     */
    public function doExpressMultipleCheckout($items, $invoice = '', $currency = 'USD', $shipping = false, &$customFields = array())
    {
        $data = array();
        $total_items = count($items);
        $totalPrice = 0;

        foreach ($items as $key => $item) {
            $data["L_PAYMENTREQUEST_0_NAME$key"] = $item['name'];
            $data["L_PAYMENTREQUEST_0_DESC$key"] = $item['product_id'] . ', ';
            $data["L_PAYMENTREQUEST_0_AMT$key"] = $item['price'];
            $data["L_PAYMENTREQUEST_0_QTY$key"] = $item['quantity'];

            $totalPrice = $totalPrice + ($item['price'] * $item['quantity']);
        }

        $data['PAYMENTREQUEST_0_PAYMENTACTION'] = 'Sale';
        $data['PAYMENTREQUEST_0_AMT'] = $totalPrice;
        $data['PAYMENTREQUEST_0_CURRENCYCODE'] = $currency;
        $data['PAYMENTREQUEST_0_INVNUM'] = $invoice;
        $data['METHOD'] = "SetExpressCheckout";

        if ($shipping === false) {
            $data['NOSHIPPING'] = "1";
        } else if ($shipping === true) {
            // let the merchant decide the address
        } else {
            // address specified by the customer
            $data['ADDROVERRIDE'] = 1;
            $data['PAYMENTREQUEST_0_SHIPTONAME'] = $shipping['name'];
            $data['PAYMENTREQUEST_0_SHIPTOSTREET'] = $shipping['street_address_1'];
            $data['PAYMENTREQUEST_0_SHIPTOSTREET2'] = $shipping['street_address_2'];
            $data['PAYMENTREQUEST_0_SHIPTOCITY'] = $shipping['city'];
            $data['PAYMENTREQUEST_0_SHIPTOSTATE'] = $shipping['state_code'];
            $data['PAYMENTREQUEST_0_SHIPTOCOUNTRYCODE'] = $shipping['country_code'];
            $data['PAYMENTREQUEST_0_SHIPTOZIP'] = $shipping['zip'];
            $data['PAYMENTREQUEST_0_SHIPTOPHONENUM'] = $shipping['phone_number'];
        }

        $data['PAYMENTREQUEST_0_CUSTOM'] = $total_items;

        // add custom fields
        if (!empty($customFields)) {
            foreach ($customFields as $customField) {
                $data['PAYMENTREQUEST_0_CUSTOM'] .= '|' . $customField;
            }
        }

        return $this->runQueryWithParams($data);
    }

    /**
     * Gets checkout information from PayPal
     *
     * @param string $token PayPal token
     *
     * @return array PayPal response
     */
    public function getCheckoutDetails($token)
    {
        $data = array('TOKEN' => $token,
            'METHOD' => 'GetExpressCheckoutDetails');

        if (!$resultData = $this->runQueryWithParams($data)) return "No data";
        return $this->runQueryWithParams($data);
    }

    /**
     * Perform payment based on token and Payer ID
     *
     * If OK, returns true
     * If error, returns false
     *
     * @param string $token PayPal token returned with GET response
     * @param string $payerId PayPal Payer ID returned with GET response
     * @param array $resultData PayPal response
     *
     * @return array PayPal response
     */
    public function doSinglePayment($token, $payerId)
    {
        $details = $this->getCheckoutDetails($token);
        if (!$details) return "No data";

        $data = array('PAYMENTACTION' => 'Sale',
            'PAYERID' => $payerId,
            'TOKEN' => $token,
            'AMT' => $details["AMT"],
            'CURRENCYCODE' => $details["CURRENCYCODE"],
            'METHOD' => 'DoExpressCheckoutPayment');

        return $this->runQueryWithParams($data);
    }

    /**
     * Perform multiple payment based on token and Payer ID
     *
     * @param string $token PayPal token returned with GET response
     * @param string $payerId PayPal Payer ID returned with GET response
     *
     * @return array PayPal response
     */
    public function doMultiplePayment($token, $payerId)
    {
        $details = $this->getCheckoutDetails($token);
        if (!$details) return "No data";

        list($total_items) = explode('|', $details['CUSTOM']);

        $data = array();

        for ($i = 0; $i < $total_items; $i++) {
            $data["L_PAYMENTREQUEST_0_NAME$i"] = $details["L_PAYMENTREQUEST_0_NAME$i"];
            $data["L_PAYMENTREQUEST_0_DESC$i"] = $details["L_PAYMENTREQUEST_0_DESC$i"];
            $data["L_PAYMENTREQUEST_0_AMT$i"] = $details["L_PAYMENTREQUEST_0_AMT$i"];
            $data["L_PAYMENTREQUEST_0_QTY$i"] = $details["L_PAYMENTREQUEST_0_QTY$i"];
        }

        $data["PAYMENTREQUEST_0_PAYMENTACTION"] = 'Sale';
        $data["PAYERID"] = $payerId;
        $data["TOKEN"] = $token;
        $data["PAYMENTREQUEST_0_AMT"] = $details["AMT"];
        $data["PAYMENTREQUEST_0_CURRENCYCODE"] = $details["CURRENCYCODE"];
        $data["PAYMENTREQUEST_0_INVNUM"] = $details["INVNUM"];
        $data["METHOD"] = 'DoExpressCheckoutPayment';

        return $this->runQueryWithParams($data);
    }


    /**
     *
     * Get transaction details base on transaction ID
     *
     * @param string $transaction_id Unique identifier of a transaction
     *
     * @return array PayPal response
     */
    public function getTransactionDetails($transaction_id)
    {
        $data = array('TransactionID' => $transaction_id,
            'METHOD' => 'GetTransactionDetails');

        return $this->runQueryWithParams($data);
    }

    /**
     * Perform refund base on transaction ID
     *
     * If OK, returns true
     * If error, returns false
     *
     * @param string $transactionId Unique identifier of a transaction
     * @param string $invoice (Optional) Your own invoice or tracking number.
     * @param bool $isPartial Partial or Full refund
     * @param float $amount PayPal (Optional) Refund amount
     * @param string $currencyCode A three-character currency code. This field is required for partial refunds. Do not use this field for full refunds.
     * @param string $note (Optional) Custom memo about the refund.
     * @param array $resultData PayPal response
     *
     * @return bool
     */
    public function doRefund($transactionId, $invoice = '', $isPartial = false,
                             $amount = 0, $currencyCode = 'USD', $note = '')
    {

        $data = array('METHOD' => 'RefundTransaction',
            'TRANSACTIONID' => $transactionId,
            'INVOICEID' => $invoice,
            'REFUNDTYPE' => $isPartial ? 'Partial' : 'Full',
            'NOTE' => $note);
        if ($isPartial) {
            $data['AMT'] = $amount;
            $data['CURRENCYCODE'] = $currencyCode;
        }

        return $this->runQueryWithParams($data);
    }

}
