<?php
/**
 * This file will deal with all the code related to single order
 * use CLICKSEND_WOO_INTEGRATION_ROOT_INC_PATH to include file
 * above give value upto plugin
 * all view will be in woo-integration/partials
 *
 * @link   https://kumaranup594.github.io/
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
 * @package Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/admin
 */
class Cwiso_Admin {
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		// START SMS LOG SCREEN AJAX.
		add_action( 'wp_ajax_clicksend_get_logs', array( $this, 'get_logs' ) );

		// END SMS LOG SCREEN AJAX.
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );

		// when user will select the template in manual action then we will modify the detials.
		add_action( 'wp_ajax_clicksend_single_order_parse_template', array( $this, 'parse_order_template' ) );
		add_action( 'wp_ajax_clicksend_single_order_send_sms', array( $this, 'send_sms_with_template' ) );
	}

	/**
	 * For parse from single order screen.
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function parse_order_template() {
		$_errs = array();
		// default positive response.
		$res = array(
			'succ'       => true,
			'public_msg' => __( 'Template selected Succesfully.', 'clicksend-woo-integration' ),
		);
		try {
			if ( isset( $_POST['_nonce'] ) ) {

				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'love-nonce' ) ) {
					throw new Exception( __( 'Security check failed', 'clicksend-woo-integration' ) );
				}
			}
			$order_id = isset( $_GET['order_id'] ) ? sanitize_text_field( wp_unslash( $_GET['order_id'] ) ) : false;
			if ( ! $order_id ) {
				return;
			}
			$template_id = isset( $_GET['template_id'] ) ? sanitize_text_field( wp_unslash( $_GET['template_id'] ) ) : false;
			if ( ! $template_id ) {
				return;
			}
			$res['data'] = Clicksend_Woo_Integration_Admin_Module::get_sms_details( $order_id, $template_id );

		} catch ( Exception $exception ) {
			$res['succ']       = false;
			$res['errs']       = $_errs;
			$res['public_msg'] = $exception->getMessage();
		}
		wp_die( wp_json_encode( $res ) );
	}
	/**
	 * For send sms from single order screen.
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function send_sms_with_template() {
		// errors container.
		$_errs = array();
		// default positive response.
		$res = array(
			'succ'       => true,
			'public_msg' => __( 'SMS sent succesfully.', 'clicksend-woo-integration' ),
		);
		try {
			if ( isset( $_POST['_nonce'] ) ) {

				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'love-nonce' ) ) {
					throw new Exception( __( 'Security check failed', 'clicksend-woo-integration' ) );
				}
			}
			$order_id = isset( $_GET['order_id'] ) ? sanitize_text_field( wp_unslash( $_GET['order_id'] ) ) : false;
			$sms_body = isset( $_GET['sms_body'] ) ? sanitize_text_field( wp_unslash( $_GET['sms_body'] ) ) : false;
			// order validate.
			if ( ! $order_id ) {
				return;
			}
			$order = new WC_Order( $order_id );
			if ( ! $order ) {
				return;
			}
			// repopulating sms body in case template is changed manually after selection.
			$sms_body = Clicksend_Woo_Integration_Admin_Module::parse_sms_body( $order, $sms_body );
			$contact  = Clicksend_Woo_Integration_Admin_Module::get_contact_details( $order );
			if ( isset( $_GET['send'] ) ) {
				$response = Clicksend_SDK::send_sms( $contact['mobile'], $sms_body, $contact['country_ext'], $order_id, $created_by = 0 );
				clicksend_woo_integration_log( 'sms send reponse are coming false' . print_r( $response, true ), __FILE__, __LINE__ );
				if (
					$response && isset( $response['http_code'] )
					&& isset( $response['data']['messages'][0]['status'])
					&& 200 === $response['http_code'] && 'SUCCESS' === $response['data']['messages'][0]['status']
				) {
					Clicksend_Logger::save( $contact['country_ext'], $contact['mobile'], $sms_body, $order_id, 0, Clicksend_Logger::LOG_GROUP_WOO_ORDERS, Clicksend_Logger::LOG_TYPE_OUTBOUND, ( isset( $response['data']['messages'][0]['status'] ) ? $response['data']['messages'][0]['status'] : '' ) );
				} else {
					Clicksend_Logger::save( $contact['country_ext'], $contact['mobile'], $sms_body, $order_id, 0, Clicksend_Logger::LOG_GROUP_WOO_ORDERS, Clicksend_Logger::LOG_TYPE_OUTBOUND, ( isset( $response['data']['messages'][0]['status'] ) ? $response['data']['messages'][0]['status'] : '' ) );
				}
			}
		} catch ( Exception $exception ) {
			$res['succ']       = false;
			$res['errs']       = $_errs;
			$res['public_msg'] = $exception->getMessage();
		}
		wp_die( wp_json_encode( $res ) );
	}
	/**
	 * Add_meta_boxes.
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function add_meta_boxes() {
		// Widget window title 'Clicksend SMS Logs'.
		add_meta_box( 'logs_screen', __( 'ClickSend SMS Logs', 'clicksend-woo-integration' ), array( $this, 'add_logs_screen' ), 'shop_order', 'side', 'core' );
	}
	/**
	 * Add logs
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function add_logs_screen() {
		global $post;
		$ajax_url = admin_url( 'admin-ajax.php' );
		$order_status_temp_opt = $this->get_order_status_templates();
		require_once CLICKSEND_WOO_INTEGRATION_ROOT_INC_PATH . 'admin/modules/woo-integration/partials/clicksend-woo-sms-logs-display.php';

	}

	/**
	 * Get_logs
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function get_logs() {
		global $post;
		$_errs = array();
		// default positive response.
		$res = array(
			'succ'       => true,
			'public_msg' => __( 'Logs Fetched Successfully', 'clicksend-woo-integration' ),
		);
		try {
			// receiving form details.
			if ( isset( $_POST['_nonce'] ) ) {

				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'love-nonce' ) ) {
					throw new Exception( __( 'Security check failed', 'clicksend-woo-integration' ) );
				}
			}
			$form_data           = wp_unslash( $_POST );

			$filters             = array();
			$filters['order_id'] = $form_data['order_id'];

			$res['logs']         = Clicksend_Logger::get( $filters );
		} catch ( Exception $exception ) {
			$res['succ']       = false;
			$res['errs']       = $_errs;
			$res['public_msg'] = $exception->getMessage();
		}
		wp_die( wp_json_encode( $res ) );
	}
	/**
	 * This function will return all template options
	 */
	public static function get_order_status_templates() {
		global $wpdb;
		$table_name  = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_TEMPLATE;
		$type        = Clicksend_Logger::LOG_GROUP_MANUAL;
		$templates   = $wpdb->get_results( "select * from ${table_name} where temp_group = '${type}' order by sort", ARRAY_A );
		return $templates;
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
		 * An instance of this class should be passed to the run(     ) function
		 * defined in Clicksend_Woo_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clicksend_Woo_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name . '-woo-sms-logs', CLICKSEND_WOO_INTEGRATION_ROOT_BASE_PATH . 'admin/modules/woo-integration/css/clicksend-woo-sms-logs.css', array(), $this->version, 'all' );
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
		 * An instance of this class should be passed to the run(     ) function
		 * defined in Clicksend_Woo_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clicksend_Woo_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name . '-woo-sms-logs', CLICKSEND_WOO_INTEGRATION_ROOT_BASE_PATH . 'admin/modules/woo-integration/js/clicksend-woo-sms-logs.js', array( 'jquery' ), $this->version, false );
	}
}
