<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.clicksend.com?utm_source=integrations&utm_medium=referral&utm_campaign=wp-sms-plugin&utm_content=wp-admin-area
 * @since             1.0.0
 * @package           Clicksend_Woo_Integration
 *
 * @wordpress-plugin
 * Plugin Name:       ClickSend SMS for WooCommerce
 * Plugin URI:        https://integrations.clicksend.com/listings/woocommerce?utm_source=integrations&utm_medium[…]al&utm_campaign=wp-sms-plugin&utm_content=wp-admin-areaintegrations.clicksend.comintegratio
 * Description:       Send and receive SMS text messages straight from your WooCommerce store. Automatically send SMS notifications when orders are placed or their status change. Set up templates to fire off messages quickly to one customer, a select group, or everyone. All within WordPress, powered by ClickSend.
 * Version:           1.2.5
 * Author:            ClickSend
 * Author URI:        https://www.clicksend.com?utm_source=integrations&utm_medium=referral&utm_campaign=wp-sms-plugin&utm_content=wp-admin-area
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       clicksend-woo-integration
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
define( 'CLICKSEND_WOO_INTEGRATION_VERSION', '1.2.3' );

/**
 * Tables constants
 */

define( 'CLICKSEND_WOO_INTEGRATION_TABLE_SMS_LOG', 'cwi_sms_logs' );
define( 'CLICKSEND_WOO_INTEGRATION_TABLE_SMS_TEMPLATE', 'cwi_sms_template' );
define( 'CLICKSEND_WOO_INTEGRATION_DEFAULT_TIME_ZONE', 'Asia/kolkata' );
define( 'CLICKSEND_WOO_INTEGRATION_ROOT_INC_PATH', plugin_dir_path( __FILE__ ) );
define( 'CLICKSEND_WOO_INTEGRATION_ROOT_BASE_PATH', plugin_dir_url( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-clicksend-woo-integration-activator.php
 */
function activate_clicksend_woo_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-clicksend-woo-integration-activator.php';
	Clicksend_Woo_Integration_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-clicksend-woo-integration-deactivator.php
 */
function deactivate_clicksend_woo_integration() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-clicksend-woo-integration-deactivator.php';
	Clicksend_Woo_Integration_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_clicksend_woo_integration' );
register_deactivation_hook( __FILE__, 'deactivate_clicksend_woo_integration' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-clicksend-woo-integration.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_clicksend_woo_integration() {

	$plugin = new Clicksend_Woo_Integration();
	$plugin->run();

}
run_clicksend_woo_integration();
