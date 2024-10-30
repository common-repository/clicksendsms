<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link https://kumaranup594.github.io/
 * @since 1.0.0
 *
 * @package Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package  Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/admin
 */
class Clicksend_Woo_Integration_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @var string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @var string $version The Version of this plugin.
	 */
	private $version;
	/**
	 * Defining as the private key variable
	 *
	 * @since 1.0.0
	 * @var string $db_welcome_dismissed_key The db_welcome_dismissed_key of this plugin.
	 */
	private $db_welcome_dismissed_key;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		$this->load_dependencies();
		add_action( 'admin_menu', array( $this, 'add_admin_page' ) );
		// name of unique admin notice close key.
		$this->db_welcome_dismissed_key = 'cwi_welcome_dismissed_key';

		// registering notice.
		add_action( 'admin_notices', array( &$this, 'dashboard_notices' ) );

		// private ajax URL to save admin has close the admin notice.
		add_action( 'wp_ajax_cwi_dismiss_dashboard_notices', array( $this, 'dismiss_dashboard_notices' ) );
	}

	/**
	 * Show relevant notices for the plugin.
	 */
	public function dashboard_notices() {
		// current admin page.
		global $pagenow;
		// 1. checking if user already closed the admin notice or not.
		// 2. checking current user have capability to manage this plugin or not.
		if ( ! get_option( $this->db_welcome_dismissed_key ) && current_user_can( 'manage_options' ) ) {
			// check if user is not on the plugin page as this notice must not be visible at plugin page.
			if ( ! ( 'admin.php' === $pagenow && isset( $_GET['page'], $_GET['tab'] ) && 'clicksend-sms' === $_GET['page'] && 'settings' === $_GET['tab'] ) ) {
				// generating setting page url to pass on view.
				$setting_page = admin_url( 'admin.php?page=clicksend-sms&tab=settings' );
				// load the notices view.
				require_once CLICKSEND_WOO_INTEGRATION_ROOT_INC_PATH . '/admin/modules/settings/partials/clicksend-partials-plugin-messege.php';
			}
		}
	}
	/**
	 * Dismiss activation dashboard notification.
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function dismiss_dashboard_notices() {
		if ( isset( $_POST['_nonce'] ) ) {

			if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'love-nonce' ) ) {
				throw new Exception( __( 'Security check failed', 'clicksend-woo-integration' ) );
			}
		}

		// user has dismissed the welcome notice.
		update_option( $this->db_welcome_dismissed_key, 1 );
		exit;
	}
	/**
	 * Registering admin page.
	 */
	public function add_admin_page() {

		global $menu, $submenu;

		add_menu_page(
			'ClickSend SMS',
			'ClickSend SMS',
			'manage_options',
			'clicksend-sms',
			array( $this, 'admin_page_html' ),
			'dashicons-format-chat'
		);

		add_submenu_page( 'clicksend-sms', 'Activate Account', 'Activate ClickSend', 'manage_options', 'clicksend-sms', '', 5 );
	}
	/**
	 * Registering admin page.
	 */
	public function admin_page_html() {
		// check user capabilities.
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Get the active tab from the $_GET param.
		$default_tab = 'settings';
		$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : $default_tab;

		$tabs = array( 'settings' => array( 'label' => 'Activate ClickSend' ) );
		$tabs = apply_filters( 'clicksend_admin_tabs', $tabs );
		require_once 'modules/settings/partials/clicksend-woo-integration-admin-display.php';
	}
	/**
	 * Loading dependencies.
	 */
	private function load_dependencies() {
		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/modules/settings/class-clicksend-settings-admin-module.php';
		$settings_obj     = new Clicksend_Settings_Admin_Module( $this->plugin_name, $this->version );
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/modules/woo-integration/class-clicksend-woo-integration-admin-module.php';
		$woo_int_obj       = new Clicksend_Woo_Integration_Admin_Module( $this->plugin_name, $this->version );

		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/modules/sms-templates/class-clicksend-sms-templates-admin-module.php';
		$sms_templates_obj = new Clicksend_Sms_Templates_Admin_Module( $this->plugin_name, $this->version );
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Clicksend_Woo_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clicksend_Woo_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/clicksend-woo-integration-admin-global.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Clicksend_Woo_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clicksend_Woo_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/clicksend-woo-integration-admin-global.js', array( 'jquery' ), $this->version, false );
	}
}
