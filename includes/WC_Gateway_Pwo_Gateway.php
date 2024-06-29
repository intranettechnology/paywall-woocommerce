<?php

/**
 * WC_Gateway_Pwo class
 *
 * @author   Ameer Mousavi <me@ameer.ir>
 * @package  WooCommerce Paywall Payments Gateway
 * @since    1.0.0
 */
// Exit if accessed directly.
if (!defined('ABSPATH')) {
	exit;
}
class WC_Gateway_Pwo_Gateway extends WC_Payment_Gateway
{

	/**
	 * Whether the gateway is visible for non-admin users.
	 * @var boolean
	 *
	 */
	protected $hide_for_non_admin_users;
	/**
	 * Payment gateway instructions.
	 * @var string
	 *
	 */
	protected $instructions;

	protected $mode;
	protected $api_type;
	protected $public_client;
	protected $public_key;
	protected $private_client;
	protected $private_key;


	/**
	 * Unique id for the gateway.
	 * @var string
	 *
	 */
	public $id = 'pwo';

	/**
	 * Constructor for the gateway.
	 */
	public function __construct()
	{

		$this->icon               = apply_filters('wc_pwo_logo', $this->get_ico_url());
		$this->has_fields         = true;
		$this->supports           = array(
			'pre-orders',
			'products',
			'subscriptions',
			'subscription_cancellation',
			'subscription_suspension',
			'subscription_reactivation',
			'subscription_amount_changes',
			'subscription_date_changes',
			'multiple_subscriptions'
		);

		$this->method_title       = _x('Paywall Payment', 'Paywall payment method', 'pwo');
		$this->method_description = __('Allows Paywall payments.', 'pwo');

		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();

		// Define user set variables.
		$this->enabled = $this->get_option('enabled');
		$this->hide_for_non_admin_users = $this->get_option('hide_for_non_admin_users');
		$this->title                    = $this->get_option('title');
		$this->description              = $this->get_option('description');
		$this->instructions             = $this->get_option('instructions', $this->description);
		$this->mode = $this->get_option('mode', $this->mode);
		$this->api_type = $this->get_option('api_type', $this->api_type);
		$this->public_client = $this->get_option('public_client', $this->public_client);
		$this->public_key = $this->get_option('public_key', $this->public_key);
		$this->private_client = $this->get_option('private_client', $this->private_client);
		$this->private_key = $this->get_option('private_key', $this->private_key);
	}

	/**
	 * Initialise Gateway Settings Form Fields.
	 */
	public function init_form_fields()
	{

		$this->form_fields = array(
			'enabled' => array(
				'title'   => __('Enable/Disable', 'pwo'),
				'type'    => 'checkbox',
				'label'   => __('Enable Paywall One Payments', 'pwo'),
				'default' => 'yes',
			),
			'hide_for_non_admin_users' => array(
				'type'    => 'checkbox',
				'label'   => __('Hide at checkout for non-admin users', 'pwo'),
				'default' => 'no',
			),
			'title' => array(
				'title'       => __('Title', 'pwo'),
				'type'        => 'text',
				'description' => __('This controls the title which the user sees during checkout.', 'pwo'),
				'default'     => _x('Paywall One Payment', 'Paywall One payment method', 'pwo'),
				'desc_tip'    => true,
			),
			'description' => array(
				'title'       => __('Description', 'pwo'),
				'type'        => 'textarea',
				'description' => __('Payment method description that the customer will see on your checkout.', 'pwo'),
				'default'     => __('Here is your custom message', 'pwo'),
				'desc_tip'    => true,
			),
			'mode' => array(
				'title'    => __('Payment Mode', 'pwo'),
				'desc'     => __('Choose between 3D or 2D methods', 'pwo'),
				'id'       => 'woo_pwo_payment_result',
				'type'     => 'select',
				'options'  => array(
					'3d'  => __('3d', 'pwo'),
					'2d'  => __('2d', 'pwo'),
				),
				'default' => '3d',
				'desc_tip' => true,
			),
			'api_type' => array(
				'title' => esc_html__('Api Type', 'paywall'),
				'type' => 'select',
				'options' =>
				array(
					'https://payment-api.itspaywall.com/' => esc_html__('Real Environment', 'pwo'),
					'https://dev-payment-api.itspaywall.com/' => esc_html__('Test Environment', 'pwo')
				),
			),
			'public_client' => array(
				'title' => esc_html__('Public Client', 'pwo'),
				'type' => 'text'
			),

			'public_key' => array(
				'title' => esc_html__('Public Key', 'pwo'),
				'type' => 'text'
			),
			'private_client' => array(
				'title' => esc_html__('Private Client', 'pwo'),
				'type' => 'text'
			),
			'private_key' => array(
				'title' => esc_html__('Private Key', 'pwo'),
				'type' => 'text'
			),
		);
		add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
		add_action( 'woocommerce_scheduled_subscription_payment_pwo', array( $this, 'process_subscription_payment' ), 10, 2 );
	}
	function get_ico_url()
	{
		$url = plugins_url('/pwo')  . '/assets/img/paywall-logo.png';
		return $url;
	}
	public function process_payment($order_id)
	{
		$order = wc_get_order($order_id);
		return array(
			'result' => 'success',
			'redirect' => $order->get_checkout_payment_url(true)
		);
	}
}
