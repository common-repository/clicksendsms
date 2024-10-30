<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link   https://kumaranup594.github.io/
 * @since  1.0.0
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
class Clicksend_Settings_Admin_Module {
	/**
	 * The ID of this plugin.
	 *
	 * @since 1.0.0
	 * @var  string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since 1.0.0
	 * @var  string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param  string $plugin_name  The name of this plugin.
	 * @param  string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		add_action( 'clicksend_admin_tab_content', array( $this, 'activate_api_key' ) );

		add_action( 'wp_ajax_clicksend_process_active_api_key', array( $this, 'process_active_api_key' ) );
		add_action( 'wp_ajax_nopriv_clicksend_process_active_api_key', array( $this, 'process_active_api_key' ) );
	}

	/**
	 * Below function will save the settings of clicksend api_key
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function process_active_api_key() {
		// settings_page 2: save & give back the response.
		// errors container.
		$_errs = array();

		// default positive response.
		$res = array(
			'succ'       => true,
			'public_msg' => __( 'Your details were saved succesfully', 'clicksend-woo-integration' )
		);
		try {
			// receiving form details.
			if ( isset( $_POST['_nonce'] ) ) {

				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'love-nonce' ) ) {
					throw new Exception( __( 'Security check failed', 'clicksend-woo-integration' ) );
				}
			}
			$form_data = wp_unslash( $_POST );
			/**
			 * Param
			 * cs_username
			 * cs_api_key
			 * cs_sender_name
			 * cs_sms_send_to = billing | shipping
			 */

			// validating first name.
			if ( ! isset( $form_data['cs_username'] ) || '' === $form_data['cs_username'] ) {
				$_errs['cs_username'][] = __( 'ClickSend username is required and can\'t be blank', 'clicksend-woo-integration' );
			}

			// validating api key.
			if ( ! isset( $form_data['cs_api_key'] ) || '' === $form_data['cs_api_key'] ) {
				$_errs['cs_api_key'][] = __( 'ClickSend Api Key is required and can\'t be blank', 'clicksend-woo-integration' );
			}

			// validating Send to.
			if ( ! isset( $form_data['cs_sms_send_to'] ) ) {
				$_errs['cs_sms_send_to'][] = __( 'Send SMS to field is required and can\'t be blank', 'clicksend-woo-integration' );
			}

			// if array isn't empty means some error has occurred.
			if ( ! empty( $_errs ) ) {
				throw new Exception( __( 'Invalid data to process, please check the respective field errors and try again.', 'clicksend-woo-integration' ) );
			}
			$check_details = $this->cs_get_account_details( $form_data['cs_username'], $form_data['cs_api_key'] );

			if ( 200 !== $check_details['response']->http_code ) {
				$response_msg = str_replace( '.', '', $check_details['response']->response_msg );
				throw new Exception( __( 'Username or API Key are incorrect.' ) );
			}
			// if all okay.
			// loading required db global.
			// sanitizing & preparing data to insert array.
			$option_to_insert = array(
				'cs_username'    => sanitize_text_field( $form_data['cs_username'] ),
				'cs_api_key'     => sanitize_text_field( $form_data['cs_api_key'] ),
				'cs_sender_name' => sanitize_text_field( $form_data['cs_sender_name'] ),
				'cs_sms_send_to' => sanitize_text_field( $form_data['cs_sms_send_to'] )
			);
			foreach ( $option_to_insert as $key => $value ) {
				update_option( $key, $value );
			}

			// for disabled the admin notice.
			update_option( 'cwi_welcome_dismissed_key', 1 );
		} catch ( Exception $exception ) {
			$res['succ']       = false;
			$res['errs']       = $_errs;
			$res['public_msg'] = $exception->getMessage();
		}
		wp_die( wp_json_encode( $res ) );
	}
	/**
	 * Activate tab.
	 *
	 * @param tab $tab comment about this variable.
	 */
	public function activate_api_key( $tab ) {
		if ( 'settings' !== $tab ) {
			return false;
		}

		// settings_page 3: Fetch the details and show in the form.
		$cs_username    = get_option( 'cs_username' );
		$cs_api_key     = get_option( 'cs_api_key' );
		$cs_sender_name = get_option( 'cs_sender_name' );
		$cs_sms_send_to = get_option( 'cs_sms_send_to' );
		if ( isset( $cs_username, $cs_api_key ) && ! empty( $cs_username ) && ! empty( $cs_api_key ) ) {
			$cs_current_bal = $this->cs_get_account_details( $cs_username, $cs_api_key );
		}

		require_once 'partials/clicksend-partials-settings.php';
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

		wp_enqueue_style( $this->plugin_name . '-settings-admin', plugin_dir_url( __FILE__ ) . 'css/clicksend-woo-integration-admin.css', array(), $this->version, 'all' );
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

		wp_enqueue_script( $this->plugin_name . '-settings-admin', plugin_dir_url( __FILE__ ) . 'js/clicksend-woo-integration-admin.js', array( 'jquery' ), $this->version, false );
	}
	/**
	 * For cs_get_account_details
	 *
	 * @param string $cs_username takes username.
	 * @param string $cs_api_key takes key.
	 */
	protected function cs_get_account_details( $cs_username, $cs_api_key ) {
		$args = array(
            'body'        => '',
            'timeout'     => '5',
            'redirection' => '5',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => array(
				'Content-Type' => 'application/json',
				'Authorization' =>  'Basic ' . base64_encode( "$cs_username:$cs_api_key" ),
			),
        );
		$apiUrl      = 'https://rest.clicksend.com/v3/account';
		$apiResponse = wp_remote_get($apiUrl, $args);

		if ( is_wp_error( $apiResponse ) ) {
			$error_message = $apiResponse->get_error_message();
			throw new Exception("Something went wrong: $error_message");
		} else {
			$response     = json_decode(wp_remote_retrieve_body($apiResponse));
			if ( isset( $response ) && ! empty( $response && null !== $response->data ) ) {
				$cs_acc_data = array(
					'response' => $response,
					'currency_sign' => $response->data->_currency->currency_prefix_d,
					'current_bal' => $response->data->balance,
				);
				return $cs_acc_data;
			} else {
				$cs_acc_data = array(
					'response' => $response,
					'currency_sign' => '-',
					'current_bal' => '-',
				);
				return $cs_acc_data;
			}
		}
	}
}
