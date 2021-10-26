<?php

namespace Obydul\LaraPal\Lib;

use Obydul\LaraPal\Lib\HTTPRequest;
use Obydul\LaraPal\Lib\PaypalGateway;

class PaypalBase
{
    public $gateway;
    public $endpoint = '/nvp';

    public function __construct($return_url = null, $cancel_url = null)
    {
        // configure larapal
        $gateway = new PaypalGateway();

        $gateway->apiUsername = config('larapal.api_username');
        $gateway->apiPassword = config('larapal.api_password');
        $gateway->apiSignature = config('larapal.api_signature');

        // set mode : sandbox or live
        if (config('larapal.mode') == "sandbox")
            $gateway->testMode = true;
        else
            $gateway->testMode = false;

        // return (ack success) and cancel url
        $gateway->returnUrl = $return_url;
        $gateway->cancelUrl = $cancel_url;

        $this->gateway = $gateway;
    }

    protected function response($data)
    {
        $request = new HTTPRequest($this->gateway->getHost(), $this->endpoint, 'POST', true);
        $result = $request->connect($data);
        if ($result < 400) return $request;
        return false;
    }

    protected function responseParse($resp)
    {
        $a = explode("&", $resp);
        $out = array();
        foreach ($a as $v) {
            $k = strpos($v, '=');
            if ($k) {
                $key = trim(substr($v, 0, $k));
                $value = trim(substr($v, $k + 1));
                if (!$key) continue;
                $out[$key] = urldecode($value);
            } else {
                $out[] = $v;
            }
        }
        return $out;
    }

    protected function buildQuery($data = array())
    {
        $data['USER'] = $this->gateway->apiUsername;
        $data['PWD'] = $this->gateway->apiPassword;
        $data['SIGNATURE'] = $this->gateway->apiSignature;
        $data['VERSION'] = '204.0';
        $data['RETURNURL'] = $this->gateway->returnUrl;
        $data['CANCELURL'] = $this->gateway->cancelUrl;
        $query = http_build_query($data);
        return $query;
    }


    protected function runQueryWithParams($data)
    {
        $query = $this->buildQuery($data);
        $result = $this->response($query);
        if (!$result) return false;

        $response = $result->getContent();
        $return = $this->responseParse($response);
        $return['ACK'] = strtoupper($return['ACK']);
        return $return;
    }
}