<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://ameer.ir
 * @since      1.0.0
 *
 * @package    Pwo
 * @subpackage Pwo/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Pwo
 * @subpackage Pwo/public
 * @author     Ameer Mousavi <me@ameer.ir>
 */
class Pwo_Public
{

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version)
	{

		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
	public static function woocommerce_gateway_pwo_woocommerce_block_support()
	{
		if (class_exists('Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType')) {
			require_once plugin_dir_path(__FILE__) . '../includes/blocks/class-wc-pwo-payments-blocks.php';
			add_action(
				'woocommerce_blocks_payment_method_type_registration',
				function (Automattic\WooCommerce\Blocks\Payments\PaymentMethodRegistry $payment_method_registry) {
					$payment_method_registry->register(new WC_Gateway_Pwo_Blocks_Support());
				}
			);
		}
	}
	public function pwo_payment_redirect($order_id)
	{
		require_once plugin_dir_path(__FILE__) . '../includes/class-paywall.php';

		$options = get_option("woocommerce_pwo_settings");
		$public_client = $options['public_client'];
		$public_key = $options['public_key'];

		$order = wc_get_order($order_id);
		$purchaseAmount = $order->get_total();
		$okUrl = get_site_url() . "/?wc-api=wc_gateway_pwo";
		$failUrl = $order->get_checkout_payment_url(true);

		$uniq_code = substr(sha1((string) microtime()), 0, 16);
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
			$paywall = new Paywall();

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
					"CurrencyId" => $this->get_currency_code(),
					"MerchantSuccessBackUrl" => $okUrl,
					"MerchantFailBackUrl" => $failUrl,
					"Installement" => $_REQUEST['InstallmentCount'],
					"ChannelId" => 0,
					"TagId" => 0,
					"Half3D" => $options['mode'] === '2d'
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

			if ($options['mode'] === '2d') {
				$result = $paywall->directPayRequest($vars);
			} else {
				$result = $paywall->payRequest($vars);
			}
			if (empty($result)) {
				wc_print_notice(esc_html__('The live environment is not active.', 'pwo'), 'error');
			} else if ($result->ErrorCode == 0) {
				if ($options['mode'] === '2d') {
					header("Location: " . $order->get_checkout_order_received_url());
				} else {
					header("Location: " . $result->Body->RedirectUrl);
				}
			} else {
				wc_print_notice($result->Message, 'error');
			}
		}

		if (!empty($_REQUEST['Reason'])) {
			wc_print_notice(esc_html__(base64_decode($_REQUEST['Reason']), 'pwo'), 'error');
		}


		require_once "partials/payment-form.php";
	}
	public function pwo_checkout_response()
	{
		global $wpdb;
		$read = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}postmeta WHERE meta_key = 'MerchantUniqueCode' AND meta_value = '%s'", $_REQUEST['UniqueCode']));

		if (isset($read->post_id)) {
			global $woocommerce;
			$order = wc_get_order($read->post_id);
			$order->payment_complete();
			$woocommerce->cart->empty_cart();
			update_post_meta($read->post_id, "payment_time", time());
			return wp_redirect(
				$order->get_checkout_order_received_url()
			);
		} else {
			esc_html__('Invalid request', 'pwo');
		}
		exit;
	}

	/**
	 * Get the currency code used in WooCommerce.
	 *
	 * The currency code is used to determine which currency to use in the
	 * payment request to Paywall.
	 *
	 * @return int The currency code used in WooCommerce.
	 */
	public function get_currency_code()
	{
		// Get the current currency in use with WooCommerce
		$current_currency = get_woocommerce_currency();

		// The supported currencies array contains the currency codes as keys
		// and the corresponding integer ID as the value.
		$supported_currencies = array(
			"TRY" => 1,
			"USD" => 2,
			"EUR" => 3,
			"BAM" => 4,
			"MKD" => 5
		);

		// Check if the current currency is in the supported currencies array
		$currency_id = 0;
		if (array_key_exists($current_currency, $supported_currencies)) {
			// Get the ID of the current currency
			$currency_id = $supported_currencies[$current_currency];
		}

		return $currency_id;
	}
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pwo_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pwo_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style($this->plugin_name, plugin_dir_url(__FILE__) . 'css/pwo-public.css', array(), $this->version, 'all');
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts()
	{

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pwo_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pwo_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script($this->plugin_name, plugin_dir_url(__FILE__) . 'js/pwo-public.js', array('jquery'), $this->version, false);
	}
}
