<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://ameer.ir
 * @since      1.0.0
 *
 * @package    Pwo
 * @subpackage Pwo/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pwo
 * @subpackage Pwo/admin
 * @author     Ameer Mousavi <me@ameer.ir>
 */
class Pwo_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}
	public function init_pwo_gateway_class()
	{
		// Make the WC_Gateway_Pwo_Gateway class available.
		if ( class_exists( 'WC_Payment_Gateway' ) ) {
			require_once plugin_dir_path(__FILE__) . '../includes/WC_Gateway_Pwo_Gateway.php';
		}
	}
	public function add_pwo_gateway_class( $gateways ) {
		$options = get_option($this->plugin_name);
		require_once plugin_dir_path(__FILE__) . '../includes/WC_Gateway_Pwo_Gateway.php';
		$gateways['WC_Gateway_Pwo_Gateway'] = new WC_Gateway_Pwo_Gateway($this->plugin_name, $options);
		return $gateways;
	}
	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pwo-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pwo-admin.js', array( 'jquery' ), $this->version, false );

	}

}
