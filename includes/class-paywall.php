<?php

if (!defined('ABSPATH')) {
    exit;
}
class Paywall
{
    public $base_url, $base_url_private, $_2d_url, $_3d_url, $query_url, $cancel_url, $refund_url;

    public function __construct()
    {
        $this->base_url = get_option("woocommerce_pwo_settings")['api_type'];

        if ($this->base_url == "https://dev-payment-api.itspaywall.com/") {
            $this->base_url_private = "https://dev-payment-private-api.itspaywall.com/";
        } else {
            $this->base_url_private = "https://payment-private-api.itspaywall.com/";
        }

        $this->_2d_url = "api/paywall/payment/startdirect";
        $this->_3d_url = "api/paywall/payment/start3d";

        $this->query_url = "api/paywall/private/query";
        $this->cancel_url = "api/paywall/private/cancel";
        $this->refund_url = "api/paywall/private/refund";
    }

    /**
     * 2d Payment
     */
    public function directPayRequest($vars)
    {
        $options = get_option("woocommerce_pwo_settings");
        $public_client = $options['public_client'];
        $public_key = $options['public_key'];

        $header = array(
            'Content-Type: application/json',
            'apiclientpublic: ' . $public_client,
            'apikeypublic: ' . $public_key,
        );

        return $this->curlRequest($vars, $this->_2d_url, $header);
    }
    /**
     * 3d Payment
     */
    public function payRequest($vars)
    {
        $options = get_option("woocommerce_pwo_settings");
        $public_client = $options['public_client'];
        $public_key = $options['public_key'];

        $header = array(
            'Content-Type: application/json',
            'apiclientpublic: ' . $public_client,
            'apikeypublic: ' . $public_key,
        );

        return $this->curlRequest($vars, $this->_3d_url, $header);
    }


    /**
     * Payment Inquiry
     */

    public function queryRequest($merchant_uniq_code)
    {
        $options = get_option("woocommerce_pwo_settings");
        $private_client = $options['private_client'];
        $private_key = $options['private_key'];
        $header = array(
            'apiclientprivate: ' . $private_client,
            'apikeyprivate: ' . $private_key,
            'merchantuniquecode: ' . $merchant_uniq_code,
        );
        return $this->curlRequest("", $this->query_url, $header);
    }


    /**
     * Payment Cancellation
     */
    public function cancelRequest($merchant_uniq_code)
    {
        $options = get_option("woocommerce_pwo_settings");
        $private_client = $options['private_client'];
        $private_key = $options['private_key'];
        $header = array(
            'Content-Type: application/json',
            'apiclientprivate: ' . $private_client,
            'apikeyprivate: ' . $private_key,
        );
        return $this->curlRequest($merchant_uniq_code, $this->cancel_url, $header);
    }

    /**
     * Refund Request
     */
    public function refundRequest($merchant_uniq_code)
    {
        $options = get_option("woocommerce_pwo_settings");
        $private_client = $options['private_client'];
        $private_key = $options['private_key'];
        $header = array(
            'Content-Type: application/json',
            'apiclientprivate: ' . $private_client,
            'apikeyprivate: ' . $private_key,
        );
        return $this->curlRequest($merchant_uniq_code, $this->refund_url, $header);
    }



    /**
     * Sends a cURL request to the specified URL with the given parameters and headers.
     *
     * @param mixed $params The parameters to be sent with the request.
     * @param string $url The URL to send the request to.
     * @param array $header The headers to be sent with the request.
     * @return mixed The response from the request, decoded from JSON.
     */
    public function curlRequest($params, $url, $header)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HTTPHEADER, array());
        if (empty($params) || array_key_exists("MerchantUniqueCode", $params)) {
            
            curl_setopt($ch, CURLOPT_URL, $this->base_url_private . $url);
        } else {
            curl_setopt($ch, CURLOPT_URL, $this->base_url . $url);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
curl_setopt($ch, CURLINFO_HEADER_OUT, true);
        if (!empty($params)) {

            curl_setopt($ch, CURLOPT_POST, json_encode($params, JSON_UNESCAPED_UNICODE));
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));
        }
        $info = curl_getinfo($ch);
        
		
        $output = curl_exec($ch);
        curl_close($ch);
		error_log(print_r(json_decode($output),1));
        return json_decode($output);
    }
}
