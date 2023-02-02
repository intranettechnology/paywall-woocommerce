<?php

    if (!defined('ABSPATH')) {
        exit;
    }
    class paywall
    {
        public $base_url, $base_url_private, $d3url, $query_url, $cancel_url, $refund_url;

        public function __construct()
        {
            $this->base_url = get_option("woocommerce_paywall_settings")['api_type']."/";

            if($this->base_url=="https://dev-payment-api.itspaywall.com/"){
                $this->base_url_private = "https://dev-payment-private-api.itspaywall.com/";
            } else {
                $this->base_url_private = "https://payment-private-api.itspaywall.com/";
            }


            $this->d3url = "api/paywall/payment/start3d";

            $this->query_url = "api/paywall/private/query";
            $this->cancel_url = "api/paywall/private/cancel";
            $this->refund_url = "api/paywall/private/refund";
        }

        /*
         * 3d ödeme
         */
        public function payRequest($vars)
        {
            $options = get_option("woocommerce_paywall_settings");
            $public_client = $options['public_client'];
            $public_key = $options['public_key'];

            $header = array(
                'Content-Type: application/json',
                'apiclientpublic: '.$public_client,
                'apikeypublic: '.$public_key,
            );

            return $this->curlRequest($vars, $this->d3url, $header);
        }


        /*
         * Ödeme Sorgulama
         */

        public function queryRequest($merchant_uniq_code){
            $options = get_option("woocommerce_paywall_settings");
            $private_client = $options['private_client'];
            $private_key = $options['private_key'];
            $header = array(
                'apiclientprivate: '.$private_client,
                'apikeyprivate: '.$private_key,
                'merchantuniquecode: '.$merchant_uniq_code,
            );
            return $this->curlRequest("", $this->query_url, $header);
        }


        /*
         * Ödeme İptali Yapma
         */
        public function cancelRequest($merchant_uniq_code){
            $options = get_option("woocommerce_paywall_settings");
            $private_client = $options['private_client'];
            $private_key = $options['private_key'];
            $header = array(
                'Content-Type: application/json',
                'apiclientprivate: '.$private_client,
                'apikeyprivate: '.$private_key,
            );
            return $this->curlRequest($merchant_uniq_code, $this->cancel_url, $header);
        }

        /*
         * Ödeme İadesi Yapma
         */
        public function refundRequest($merchant_uniq_code){
            $options = get_option("woocommerce_paywall_settings");
            $private_client = $options['private_client'];
            $private_key = $options['private_key'];
            $header = array(
                'Content-Type: application/json',
                'apiclientprivate: '.$private_client,
                'apikeyprivate: '.$private_key,
            );
            return $this->curlRequest($merchant_uniq_code, $this->refund_url, $header);
        }




        public function curlRequest($params, $url, $header)
        {

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, array());
            if(empty($params) || array_key_exists("MerchantUniqueCode", $params)){
                curl_setopt($ch, CURLOPT_URL, $this->base_url_private.$url);
            } else {
                curl_setopt($ch, CURLOPT_URL, $this->base_url.$url);
            }

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $header);


            if(!empty($params)){

                curl_setopt($ch, CURLOPT_POST, json_encode($params, JSON_UNESCAPED_UNICODE));
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params, JSON_UNESCAPED_UNICODE));
            }

            $output = curl_exec($ch);
            curl_close($ch);
            return json_decode($output);
        }
    }

