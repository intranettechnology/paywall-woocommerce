<?php
    /*
        Plugin Name: Paywall Sanal Pos
        Plugin URI:
        Description: Paywall sanal pos eklentisi
        Version: 1.0
        Author: Paywall
        Author URI:www.itspaywall.com
        License: GNU
        Text Domain: paywall
        Domain Path: /languages
        */

    if (!defined('ABSPATH')) {
        exit;
    }
    // Çoklu Dil Desteği Ekler
    add_action('init', 'wpdocs_load_paywall');
    function wpdocs_load_paywall()
    {
        load_plugin_textdomain('paywall', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }


    add_filter('woocommerce_payment_gateways', 'paywall_add_gateway_class');
    function paywall_add_gateway_class($gateways)
    {
        $gateways[] = 'WC_Paywall_Gateway';
        return $gateways;
    }

    /*
     * The class itself, please note that it is inside plugins_loaded action hook
     */
    add_action('plugins_loaded', 'paywall_init_gateway_class');
    function paywall_init_gateway_class()
    {

        class WC_Paywall_Gateway extends WC_Payment_Gateway
        {

            /**
             * Class constructor, more about it in Step 3
             */
            public function __construct()
            {

                $this->id = 'paywall';
                $this->has_fields = true;
                $this->method_title = 'Paywall';
                $this->method_description = esc_html__('İşletmeler için en iyi ödeme çözümü', 'paywall');
                $this->supports = array('products');
                $this->init_form_fields();
                $this->init_settings();
                $this->title = $this->get_option('title');
                $this->description = "" . $this->get_option('description') . "";
                $this->enabled = $this->get_option('enabled');
                add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
                add_action('wp_enqueue_scripts', array($this, 'payment_scripts'));
                add_action('woocommerce_receipt_paywall', array($this, 'paywall_payment_redirect'));
                add_action('woocommerce_api_paywall', array($this, 'webhook'));
            }

            public function init_form_fields()
            {

                $this->form_fields = array(
                    'enabled' => array(
                        'title' => 'Enable/Disable',
                        'label' => 'Enable Paywall',
                        'type' => 'checkbox',
                        'description' => '',
                        'default' => 'no'
                    ),
                    'title' => array(
                        'title' => esc_html__('Başlık', 'paywall'),
                        'type' => 'text',
                        'description' => esc_html__('Sitenizde müşterilerinizin göreceği ödeme yöntemi başlığı', 'paywall'),
                        'default' => 'Paywall Sanal Pos',
                        'desc_tip' => true,
                    ),
                    'description' => array(
                        'title' => esc_html__('Açıklama', 'paywall'),
                        'type' => 'text',
                        'description' => esc_html__('Sitenizde müşterilerinizin göreceği ödeme yöntemi açıklaması', 'paywall'),
                        'default' => 'Kredi kartınızla ödeme yapın.',
                        'desc_tip' => true,
                    ),
                    'api_type' => array(
                        'title' => esc_html__('Api Türü', 'paywall'),
                        'type' => 'select',
                        'options' =>
                            array(
                                'https://payment-api.itspaywall.com/' => esc_html__('Gerçek Ortam', 'paywall'),
                                'https://dev-payment-api.itspaywall.com' => esc_html__('Test Ortamı', 'paywall')
                            ),
                    ),
                    'public_client' => array(
                        'title' => esc_html__('Public Client', 'paywall'),
                        'type' => 'text'
                    ),

                    'public_key' => array(
                        'title' => esc_html__('Public Key', 'paywall'),
                        'type' => 'text'
                    ),
                    'private_client' => array(
                        'title' => esc_html__('Private Client', 'paywall'),
                        'type' => 'text'
                    ),
                    'private_key' => array(
                        'title' => esc_html__('Private Key', 'paywall'),
                        'type' => 'text'
                    ),

                );

            }

            /**
             * You will need it if you want your custom credit card form, Step 4 is about it
             */
            public function payment_fields()
            {


            }

            /*
             * Custom CSS and JS, in most cases required only when you decided to go with a custom credit card form
             */
            public function payment_scripts()
            {


            }


            public function process_payment($order_id)
            {
                $order = wc_get_order($order_id);
                return array(
                    'result' => 'success',
                    'redirect' => $order->get_checkout_payment_url(true)
                );
            }


            public function paywall_payment_redirect($order_id)
            {
                require_once "class/class-paywall.php";

                $options = get_option("woocommerce_paywall_settings");
                $public_client = $options['public_client'];
                $public_key = $options['public_key'];

                $order = wc_get_order($order_id);
                $purchaseAmount = $order->get_total();
                $okUrl = get_site_url() . "/wc-api/paywall";
                $failUrl = $order->get_checkout_payment_url(true);

                $rnd = time();
                $uniq_code = md5($rnd);
                update_post_meta($order_id, "MerchantUniqueCode", $uniq_code);


                $items = $order->get_items();

                foreach ($items as $item) {

                    $product_cat = "";
                    foreach (get_the_terms($item['product_id'], "product_cat") as $tax) {
                        $product_cat .= $tax->name . ", ";
                    }
                    $product_cat = rtrim($product_cat, ", ");

                    $carts[] = [
                        "ProductId" => '' . $item['product_id'] . '',
                        "ProductName" => '' . $item->get_name() . '',
                        "ProductCategory" => '' . $product_cat . '',
                        "ProductDescription" => '' . get_post($item['product_id'])->post_excerpt . '',
                        "ProductAmount" => '' . $item['line_total'] . ''
                    ];
                }


                if (isset($_REQUEST['ay'])) {
                    $paywall = new paywall();

                    if (empty($order->get_billing_phone())) {
                        $billing_phone = 5554443322;
                    } else {
                        $billing_phone = $order->get_billing_phone();
                    }

                    if (empty($order->get_billing_email())) {
                        $billing_email = "paywall@itspaywall.com";
                    } else {
                        $billing_email = $order->get_billing_email();
                    }

                    $vars = [
                        "PaymentDetail" => [
                            "Amount" => $order->get_total(),
                            "MerchantUniqueCode" => $uniq_code,
                            "CurrencyId" => 1,
                            "MerchantSuccessBackUrl" => $okUrl,
                            "MerchantFailBackUrl" => $failUrl,
                            "Installement" => $_REQUEST['InstallmentCount'],
                            "ChannelId" => 0,
                            "TagId" => 0,
                            "Half3D" => false
                        ],
                        "Card" => [
                            "OwnerName" => $_REQUEST['card_name'],
                            "Number" => $_REQUEST['Pan'],
                            "ExpireMonth" => $_REQUEST['ay'],
                            "ExpireYear" => $_REQUEST['yil'],
                            "Cvv" => $_REQUEST['Cvv2'],
                            "UniqueCode" => ""
                        ],
                        "Customer" => [
                            "FullName" => $order->get_billing_first_name() . " " . $order->get_billing_last_name(),
                            "Phone" => $billing_phone,
                            "Email" => $billing_email,
                            "Country" => WC()->countries->countries[$order->get_billing_country()],
                            "City" => WC()->countries->get_states($order->get_billing_country())[$order->get_billing_state()],
                            "Address" => $order->get_billing_address_1() . " " . $order->get_billing_address_2(),
                            "IdentityNumber" => "11111111111",
                            "TaxNumber" => $order->get_meta('vat_number')
                        ],
                        "Products" => $carts
                    ];


                    $result = $paywall->payRequest($vars);

                    if (empty($result)) {
                        wc_print_notice(esc_html__('Canlı ortam aktif değildir.', 'paywall'), 'error');
                    }

                    if ($result->ErrorCode == 0) {
                        header("Location: " . $result->Body->RedirectUrl);

                    } else {
                        wc_print_notice($result->Message, 'error');
                    }

                }

                if (!empty($_REQUEST['Reason'])) {
                    wc_print_notice(esc_html__(base64_decode($_REQUEST['Reason']), 'paywall'), 'error');
                }


                require_once "view/payment-form.php";


            }


            public function webhook()
            {

                global $wpdb;
                $read = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'MerchantUniqueCode' AND meta_value = '%s'", $_REQUEST['UniqueCode']));

                if (isset($read->post_id)) {


                    global $woocommerce;
                    $order = wc_get_order($read->post_id);
                    $order->payment_complete();
                    $woocommerce->cart->empty_cart();
                    update_post_meta($read->post_id, "odeme_saati", time());

                    header("Location: " . $order->get_checkout_order_received_url());
                } else {
                    esc_html__('Geçersiz istek', 'paywall');
                }


                exit;

            }

        }

    }


    function pw_register_meta_box()
    {
        add_meta_box('pw-meta-box-id', esc_html__('Paywall Ödeme Bilgileri', 'text-domain'), 'pw_meta_box_callback', 'shop_order', 'advanced', 'high');
    }

    add_action('add_meta_boxes', 'pw_register_meta_box');


    function pw_meta_box_callback($meta_id)
    {
        if(!empty(get_post_meta($meta_id->ID, "odeme_saati", true))){
            require_once "meta-box.php";

        }
    }

    add_action('wp_ajax_cancelPayment', 'cancelPayment');
    add_action('wp_ajax_nopriv_cancelPayment', 'cancelPayment');

    function cancelPayment()
    {
        require_once "class/class-paywall.php";


        $paywall = new paywall();
        $sonuc = $paywall->cancelRequest([
            'Date' => '' . wp_date("Y-m-d", get_post_meta($_REQUEST['order_id'], "odeme_saati", true)) . '',
            'MerchantUniqueCode' => $_REQUEST['MerchantUniqueCode']
        ]);
        if ($sonuc->Result == 1) {
            update_post_meta($_REQUEST['order_id'], "iptal", 1);
            $order = wc_get_order($_REQUEST['order_id']);
            $order->update_status("wc-cancelled");

            echo 1;
        } else {
            echo 0;
        }

        wp_die();
    }

    add_action('wp_ajax_refundPayment', 'refundPayment');
    add_action('wp_ajax_nopriv_refundPayment', 'refundPayment');

    function refundPayment()
    {
        require_once "class/class-paywall.php";


        $paywall = new paywall();
        $sonuc = $paywall->refundRequest([
            'Date' => '' . wp_date("Y-m-d", get_post_meta($_REQUEST['order_id'], "odeme_saati", true)) . '',
            'MerchantUniqueCode' => $_REQUEST['MerchantUniqueCode']
        ]);

        if ($sonuc->Result == 1) {
            update_post_meta($_REQUEST['order_id'], "iade", 1);
            $order = wc_get_order($_REQUEST['order_id']);
            $order->update_status("wc-refunded");

            echo 1;
        } else {
            echo 0;
        }

        wp_die();
    }


