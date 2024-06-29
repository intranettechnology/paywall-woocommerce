<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://ameer.ir
 * @since             1.0.0
 * @package           Pwo
 *
 * @wordpress-plugin
 * Plugin Name:       Paywall
 * Plugin URI:        https://paywall.one
 * Description:       The official plugin for Paywall
 * Version:           1.0.0
 * Author:            Ameer Mousavi
 * Author URI:        https://ameer.ir/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       pwo
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'PWO_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-pwo-activator.php
 */
function activate_pwo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pwo-activator.php';
	Pwo_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-pwo-deactivator.php
 */
function deactivate_pwo() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-pwo-deactivator.php';
	Pwo_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_pwo' );
register_deactivation_hook( __FILE__, 'deactivate_pwo' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-pwo.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_pwo() {

	$plugin = new Pwo();
	$plugin->run();

}
run_pwo();
