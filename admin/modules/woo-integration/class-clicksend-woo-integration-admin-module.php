<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://kumaranup594.github.io/
 * @since      1.0.0
 *
 * @package    Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/admin/modules/woo-integration
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://kumaranup594.github.io/
 * @since      1.0.0
 *
 * @package    Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/admin/modules/woo-integration
 */
class Clicksend_Woo_Integration_Admin_Module {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @var      string $version    The current version of this plugin.
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

		$this->load_dependencies();

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// filter to add new tab.
		add_action( 'clicksend_admin_tabs', array( $this, 'add_tab' ) );
		// add content of tab against above one.
		add_action( 'clicksend_admin_tab_content', array( $this, 'tab_content_activate_api_key' ) );
		// on woo-status-setting form submit.
		add_action( 'wp_ajax_clicksend_process_save_status_setting', array( $this, 'process_save_status_setting' ) );
		add_action( 'wp_ajax_nopriv_clicksend_process_save_status_setting', array( $this, 'process_save_status_setting' ) );
		// order status changes.
		add_action( 'woocommerce_order_status_changed', array( $this, 'notify_on_wc_order_change' ) );
		add_filter( 'bulk_actions-edit-shop_order', array( $this, 'add_cs_send_bulk_sms_action' ) );
		add_filter( 'handle_bulk_actions-edit-shop_order', array( $this, 'handle_cs_send_bulk_sms_action' ), 10, 3 );
		add_action( 'woocommerce_order_actions', array( $this, 'add_resend_customer_wc_action' ) );
		add_action( 'woocommerce_order_action_cswi_resend_order_noti', array( $this, 'cswi_resend_order_noti_process' ) );
		// action for bulk screen template parsing.
		add_action( 'wp_ajax_clicksend_parse_manual_template_contents', array( $this, 'parse_manual_template_on_bulk_screen' ) );
		add_action( 'wp_ajax_clicksend_bulk_order_send_sms', array( $this, 'send_bulk_order_sms' ) );

		// add sub menu.
		add_action( 'admin_menu', array( $this, 'add_admin_sub_page' ), 20 );
		add_action( 'admin_menu', array( $this, 'register_cs_send_bulk_sms_page' ) );
	}
	/**
	 * Register custom send bulk sms option
	 *
	 * @param string $bulk_actions add_cs_send_bulk_sms_action.
	 */
	public function add_cs_send_bulk_sms_action( $bulk_actions ) {
		$bulk_actions['cs_send_bulk_sms'] = 'Send Bulk SMS';
		return $bulk_actions;
	}
	/**
	 * Handle bulk sms send option
	 *
	 * @param string $redirect_to redirect_to.
	 * @param string $action action.
	 * @param array  $order_ids order_ids.
	 */
	public function handle_cs_send_bulk_sms_action( $redirect_to, $action, $order_ids ) {

		if ( 'cs_send_bulk_sms' !== $action ) {
			return $redirect_to; // Exit.
		}

		if ( empty( $order_ids ) ) {
			return $redirect_to; // Exit.
		}

		$redirect_to = add_query_arg(
			array(
				'order_ids' => implode( ',', $order_ids ),
			),
			menu_page_url( 'cs-send-bulk-sms' )
		);

		return $redirect_to;
	}
	/**
	 * Bulk sms send pages
	 *
	 * @cs_send_bulk_sms_page
	 */
	public function cs_send_bulk_sms_page() {
		if ( isset( $_GET['order_ids'] ) ) {
			$order_ids = sanitize_text_field( wp_unslash( $_GET['order_ids'] ) );
		}
		if ( ! isset( $order_ids ) || empty( $order_ids ) ) {
			return false;
		}
		$order_ids = explode( ',', $order_ids );
		$orders    = array();

		foreach ( $order_ids as $order_id ) {
			$order = new WC_Order( $order_id );

			if ( empty( $order ) ) {
				continue;
			}

			$order_items = array();

			foreach ( $order->get_items() as $item ) {

				$order_items[] = array(
					'name' => $item->get_name(),
					'qty' => $item->get_quantity(),
					'weight' => get_post_meta( $item->get_product_id(), 'net_weight_display_text', true ),
					'meta_data' => $item->get_meta_data(),
				);
			}
			$currency_hex = Clicksend_SDK::get_currency_hex( $order->get_currency() );
			// fetching contact details billing or shipping.
			// below function is also doing logging incase details not found.
			$contact_details = self::get_contact_details( $order );
			// if false.
			if ( ! $contact_details ) {
				return false;
			}

			// converting keys into variables.
			// $mobile.
			// $country_ext.

			$orders[] = array(
				'order_id'      => $order->get_id(),
				'order_date'    => $order->get_date_created(),
				'customer_name' => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
				'amount'        => $currency_hex . $order->get_total(),
				'contact'       => $contact_details['mobile'],
				'country_ext'   => $contact_details['country_ext'],
				'order_status'  => $order->get_status(),
				'pincode'       => $order->get_billing_postcode(),
				'order_items'   => $order_items,
			);
		}
		if ( empty( $orders ) ) {
			return false;
		}
		// getting template of first order_id.
		$first_order        = reset( $orders );
		$first_order_id     = $first_order['order_id'];
		$order              = new WC_Order( $first_order_id );
		$woo_status         = 'wc-' . $order->get_status();
		$sms_template       = self::get_woo_status_sms_template( $woo_status );
		$populated_sms_body = self::generate_dynamic_sms_body( $woo_status, $order );

		if ( ! isset( $populated_sms_body['parsed_body'] ) ) {
			clicksend_woo_integration_log( 'parsed sms body is blank', __FILE__, __LINE__ );
			return false;
		}
		$ajax_url = admin_url( 'admin-ajax.php' );
		// fetching manual template options.
		$order_status_temp_opt = Cwiso_Admin::get_order_status_templates();
		require_once CLICKSEND_WOO_INTEGRATION_ROOT_INC_PATH . 'admin/modules/woo-integration/partials/clicksend-partials-woo-send-bulk-sms.php';
	}
	/**
	 * Bulk sms send pages
	 *
	 * @cs_send_bulk_sms_page
	 */
	public function register_cs_send_bulk_sms_page() {
		add_submenu_page( null, 'Send Bulk SMS', 'Send Bulk SMS', 'manage_options', 'cs-send-bulk-sms', array( $this, 'cs_send_bulk_sms_page' ) );
	}
	/**
	 * Automatic sms send pages
	 *
	 * @add_admin_sub_page
	 */
	public function add_admin_sub_page() {
		add_submenu_page( 'clicksend-sms', 'Woo Status', 'SMS Automation', 'manage_options', '/admin.php?page=clicksend-sms&tab=woo-status', '', 15 );
	}
	/**
	 * Send notification
	 *
	 * @param array $actions actions.
	 */
	public function add_resend_customer_wc_action( $actions ) {
		// add "mark printed" custom action.
		$actions['cswi_resend_order_noti'] = __( 'Resend latest order notification', 'clicksend-woo-integration' );
		return $actions;
	}
	/**
	 * Send notification process
	 *
	 * @param int $order order.
	 */
	public function cswi_resend_order_noti_process( $order ) {
		clicksend_woo_integration_log( 'cswi_resend_order_noti_process' . print_r( $order->id, true ), __FILE__, __LINE__ );
		$this->notify_on_wc_order_change( $order->get_id() );
	}
	/**
	 * Parsing template data for bulk sms page
	 */
	public function parse_manual_template_on_bulk_screen() {
		// errors container.
		$_errs = array();
		// default positive response.
		$res = array(
			'succ' => true,
			'public_msg' => __( 'Example message generated.', 'clicksend-woo-integration' ),
		);
		try {
			$template = isset( $_GET['template'] ) ? sanitize_text_field( wp_unslash( $_GET['template'] ) ) : false;
			if ( ! $template ) {
				return;
			}
			$first_order_id = isset( $_GET['first_order_id'] ) ? sanitize_text_field( wp_unslash( $_GET['first_order_id'] ) ) : false;
			if ( ! $first_order_id ) {
				return;
			}
			$order                             = new WC_Order( $first_order_id );
			$populated_sms_body['parsed_body'] = self::parse_sms_body( $order, $template );

			if ( ! isset( $populated_sms_body['parsed_body'] ) ) {
				clicksend_woo_integration_log( 'parsed sms body is blank', __FILE__, __LINE__ );
				return false;
			}
			$res['data'] = $populated_sms_body;
			$res['id']   = $first_order_id;
		} catch ( Exception $exception ) {
			$res['succ'] = false;
			$res['errs'] = $_errs;
			$res['public_msg'] = $exception->getMessage();
		}
		wp_die( wp_json_encode( $res ) );
	}
	/**
	 * Send Bulk sms
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function send_bulk_order_sms() {
		// errors container.
		$_errs = array();
		// default positive response.
		$res = array(
			'succ' => true,
			'public_msg' => __( 'Your messages have been sent. You may view SMS logs on the individual order pages.', 'clicksend-woo-integration' ),
		);
		try {
			if ( isset( $_POST['_nonce'] ) ) {

				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'love-nonce' ) ) {
					throw new Exception( __( 'Security check failed', 'clicksend-woo-integration' ) );
				}
			}

			$sms_body = isset( $_POST['sms_body'] ) ? sanitize_text_field( wp_unslash( $_POST['sms_body'] ) ) : false;
			if ( ! $sms_body ) {
				clicksend_woo_integration_log( 'parsed sms body is blank', __FILE__, __LINE__ );
				throw new Exception( __( 'parsed sms body is blank', 'clicksend-woo-integration' ) );
			}
			// $orders = isset( $_POST['orders'] ) ? sanitize_text_field( wp_unslash( $_POST['orders'] ) ) : false;
			/**
			 * since version 1.2.4
			 * sanitize_text_field gives error so using another sanitization method
			 */
			$orders = isset( $_POST['orders'] ) ? ( array ) $_POST['orders'] : false;

			if ( ! $orders ) {
				clicksend_woo_integration_log( 'order list is blank', __FILE__, __LINE__ );
				throw new Exception( __( 'order list is blank', 'clicksend-woo-integration' ) );
			}
			/**
			 * Keep multi-D array of
			 * $mobile, $country_ext, $sms_body
			 */
			$sms_data = array();
			foreach ( $orders as $each_order ) {
				$order                             = new WC_Order( (int) $each_order['order_id'] );
				$populated_sms_body['parsed_body'] = self::parse_sms_body( $order, $sms_body );
				$sms_data[] = array(
					'mobile' => $each_order['contact'],
					'country_ext' => $each_order['country_ext'],
					'sms_body' => $populated_sms_body['parsed_body'],
					'order_id' => $each_order['order_id'],
				);
			}
			if ( empty( $sms_data ) ) {
				throw new Exception( __( 'sms data is blank', 'clicksend-woo-integration' ) );
			}
			// send sms.
			$response = Clicksend_SDK::send_bulk_sms( $sms_data );
			// recording logs idividually .
			foreach ( $sms_data as $each_sms_data ) {
				clicksend_woo_integration_log( 'sms send reponse are coming false' . print_r( $response, true ), __FILE__, __LINE__ );
				if ( 
					$response && isset( $response['http_code'] )
					&& isset( $response['data']['messages'][0]['status'] )
					&& 200 === $response['http_code'] && 'SUCCESS' === $response['data']['messages'][0]['status'] 
				) {
					Clicksend_Logger::save( $each_sms_data['country_ext'], $each_sms_data['to'], $each_sms_data['sms_body'], $each_sms_data['order_id'], 0, Clicksend_Logger::LOG_GROUP_WOO_ORDERS, Clicksend_Logger::LOG_TYPE_OUTBOUND, ( isset( $response['data']['messages'][0]['status'] ) ? $response['data']['messages'][0]['status'] : '' ) );
				} else {
					Clicksend_Logger::save( $each_sms_data['country_ext'], $each_sms_data['to'], $each_sms_data['sms_body'], $each_sms_data['order_id'], 0, Clicksend_Logger::LOG_GROUP_WOO_ORDERS, Clicksend_Logger::LOG_TYPE_OUTBOUND, ( isset( $response['data']['messages'][0]['status']) ? $response['data']['messages'][0]['status'] : '' ) );
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
	 * Loading dependencies
	 */
	private function load_dependencies() {
		require_once CLICKSEND_WOO_INTEGRATION_ROOT_INC_PATH . 'admin/modules/woo-integration/includes/class-cwiso-admin.php';
		$woo_single_order = new Cwiso_Admin( $this->plugin_name, $this->version );
	}
	/**
	 * Process_save_status_setting
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function process_save_status_setting() {
		// settings_page 2: save & give back the response.
		// errors container.
		$_errs = array();
		// default positive response.
		$res = array(
			'succ' => true,
			'public_msg' => __( 'Your details were saved succesfully', 'clicksend-woo-integration' ),
		);
		try {
			// receiving form details.
			if ( isset( $_POST['_nonce'] ) ) {

				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'love-nonce' ) ) {
					throw new Exception( __( 'Security check failed', 'clicksend-woo-integration' ) );
				}
			}
			$form_data = wp_unslash( $_POST );
			global $wpdb;
			// setting default timezone for date funtion.
			// date_default_timezone_set(CLICKSEND_WOO_INTEGRATION_DEFAULT_TIME_ZONE).
			foreach ( $form_data['woo_status'] as $label => $body ) {
				$data_to_insert_or_update = array(
					'label' => $label,
					'body' => $body,
					'status' => (int) isset( $form_data['checked'][ $label ] ),
					'updated_at' => gmdate( 'Y-m-d H:i:s', time() ),
					'updated_by' => get_current_user_id(),
					'created_by' => get_current_user_id(),
					'sort' => $form_data['woo_sort'][ $label ],
					'temp_group' => 'woo-status',
				);
				$table_name = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_TEMPLATE;

				// if form id not coming then checking from label.
				if ( ! isset( $form_data['woo_status_id'][ $label ] ) ) {
					$form_data['woo_status_id'][ $label ] = $wpdb->get_var( "SELECT id FROM ${table_name} where label='${label}' and temp_group = 'woo-status' limit 1" );
				}

				if ( isset( $form_data['woo_status_id'][ $label ] ) && $form_data['woo_status_id'][ $label ] ) {
					// update if exists.
					if ( ! $wpdb->update( $table_name, $data_to_insert_or_update, array( 'id' => $form_data['woo_status_id'][ $label ] ) ) ) {
						throw new Exception( __( 'There was an error while updating settings', 'clicksend-woo-integration' ) );
					}
				} else {
					// insert if doesn't exists.

					if ( ! $wpdb->insert( $table_name, $data_to_insert_or_update ) ) {
						throw new Exception( __( 'There was an error while inserting settings', 'clicksend-woo-integration' ) );
					}
				}
			}
			$res['public_msg'] = 'Status saved successfully!';
		} catch ( Exception $exception ) {
			$res['succ']       = false;
			$res['errs']       = $_errs;
			$res['public_msg'] = $exception->getMessage();
		}
		wp_die( wp_json_encode( $res ) );
	}
	/**
	 * Notify_on_new_order
	 *
	 * @param int $order_id order id.
	 */
	public function notify_on_new_order( $order_id ) {
		$this->notify_on_wc_order_change( $order_id );
	}
	/**
	 * Notify_on_wc_order_change
	 *
	 * @param int $order_id order id.
	 */
	public function notify_on_wc_order_change( $order_id ) {
		$sms_details = self::get_sms_details( $order_id );
		if ( ! $sms_details ) {
			clicksend_woo_integration_log( 'sms details are coming false', __FILE__, __LINE__ );
			return false;
		}

		// sending sms if auto sms config is true.
		$response = Clicksend_SDK::send_sms( $sms_details['to'], $sms_details['sms_body'], $sms_details['country_ext'], $order_id, 0 );

		clicksend_woo_integration_log( 'sms send reponse are coming false' . print_r( $response, true ), __FILE__, __LINE__ );
		if ( $response && isset( $response['http_code'] ) && isset( $response['data']['messages'][0]['status'] ) && 200 === $response['http_code'] && 'SUCCESS' === $response['data']['messages'][0]['status'] ) {
			Clicksend_Logger::save( $sms_details['country_ext'], $sms_details['to'], $sms_details['sms_body'], $order_id, 0, Clicksend_Logger::LOG_GROUP_WOO_ORDERS, Clicksend_Logger::LOG_TYPE_OUTBOUND, ( isset( $response['data']['messages'][0]['status'] ) ? $response['data']['messages'][0]['status'] : '' ) );
		} else {
			Clicksend_Logger::save( $sms_details['country_ext'], $sms_details['to'], $sms_details['sms_body'], $order_id, 0, Clicksend_Logger::LOG_GROUP_WOO_ORDERS, Clicksend_Logger::LOG_TYPE_OUTBOUND, ( isset( $response['data']['messages'][0]['status'] ) ? $response['data']['messages'][0]['status'] : '' ) );
		}
	}
	/**
	 * Get_sms_details
	 *
	 * @param int $order_id order id.
	 * @param int $template_id template id.
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public static function get_sms_details( $order_id, $template_id = 0 ) {
		// order validate.
		// if order id is null.
		if ( ! $order_id ) {
			clicksend_woo_integration_log( 'order id is blank', __FILE__, __LINE__ );
			return false;
		}

		$order = new WC_Order( $order_id );

		// order not found.
		if ( ! $order ) {
			clicksend_woo_integration_log( 'order not found', __FILE__, __LINE__ );
			return false;
		}

		// fetching contact details billing or shipping.
		// below function is also doing logging incase details not found.
		$contact_details = self::get_contact_details( $order );
		// if false.
		if ( ! $contact_details ) {
			return false;
		}
		// converting keys into variables.
		// $mobile.
		// $country_ext.

		$order_data = $order->get_data();

		$woo_status = 'wc-' . $order_data['status'];

		// if template id is forced to diff.
		if ( $template_id ) {
			global $wpdb;
			$table_name = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_TEMPLATE;
			// get order status template when status is pending.
			$woo_status = $wpdb->get_var( "SELECT label FROM ${table_name} WHERE id = ${template_id}" );
			if ( ! $woo_status ) {
				// only throwing here because it will be called manually.
				clicksend_woo_integration_log( 'template not found for template_id' . $template_id, __FILE__, __LINE__ );
				throw new Exception( __( 'Template not found for this Order status.' ) );
			}
		}

		$populated_sms_body = self::generate_dynamic_sms_body( $woo_status, $order );

		if ( ! isset( $populated_sms_body['parsed_body'] ) ) {
			clicksend_woo_integration_log( 'parsed sms body is blank', __FILE__, __LINE__ );
			return false;
		}

		Clicksend_Logger::save_general_log( $contact_details['country_ext'], $contact_details['mobile'], $populated_sms_body['parsed_body'], $order_id, 0, Clicksend_Logger::LOG_GROUP_GENERAL, Clicksend_Logger::LOG_TYPE_OUTBOUND, 'in get_sms_details before sending' );
		$sms_details = array(
			'sms_template' => $populated_sms_body['body'],
			'sms_body' => $populated_sms_body['parsed_body'],
			'to' => $contact_details['mobile'],
			'country_ext' => $contact_details['country_ext'],
			'order' => $order,
		);
		return $sms_details;
	}
	/**
	 * Get_contact_details
	 *
	 * @param object $order valid wc_order object.
	 */
	public static function get_contact_details( $order ) {
		$order_data     = $order->get_data();
		$cs_sms_send_to = get_option( 'cs_sms_send_to' );
		$mobile         = null;
		$country_ext    = null;
		// get order status.

		// fetching billing/shipping saved option priority.
		$cs_sms_send_to = get_option( 'cs_sms_send_to' );
		// if billing then fetching billing details first.
		if ( isset( $order_data[ $cs_sms_send_to ]['phone'] ) && ! empty( $order_data[ $cs_sms_send_to ]['phone'] ) ) {
			$mobile      = $order_data[ $cs_sms_send_to ]['phone'];
			$country_ext = $order_data[ $cs_sms_send_to ]['country'];
		} else {
			// checking the other details.
			$cs_sms_send_to = 'billing' === $cs_sms_send_to ? 'shipping' : 'billing';
			if ( isset( $order_data[ $cs_sms_send_to ]['phone'] ) && ! empty( $order_data[ $cs_sms_send_to ]['phone'] ) ) {
				$mobile      = $order_data[ $cs_sms_send_to ]['phone'];
				$country_ext = $order_data[ $cs_sms_send_to ]['country'];
			}
		}

		// if nothing is found.
		if ( ! $mobile ) {
			// removing throw error as it was not allowing to place order.
			Clicksend_Logger::save_general_log( '', '', '', $order->get_id(), 0, '', '', "We found blank details to send sms mobile '$mobile' & country extension as '$country_ext'." );
			return false;
		}
		$contact = array(
			'mobile' => $mobile,
			'country_ext' => Clicksend_SDK::get_country_ext( $country_ext ),
		);
		clicksend_woo_integration_log( 'mobile & country_ext found' . print_r( $contact, true ), __FILE__, __LINE__ );
		return $contact;
	}
	/**
	 * Get_woo_status_sms_template
	 *
	 * @param string $woo_status valid woo status e.g wc-pending.
	 *
	 * @return string parsed sms template.
	 */
	public static function get_woo_status_sms_template( $woo_status ) {
		global $wpdb;
		$table_name = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_TEMPLATE;
		// get order status template when status is pending.
		return $wpdb->get_row( "SELECT * FROM ${table_name} WHERE label = '${woo_status}'", ARRAY_A );
	}
	/**
	 * Generate_dynamic_sms_body
	 *
	 * @param string $woo_status valid woo status e.g wc-pending.
	 * @param object $order valid wooOrder object.
	 *
	 * @return string parsed sms template
	 */
	public static function generate_dynamic_sms_body( $woo_status, $order ) {

		// fetching dynamic template.
		$sms_template = self::get_woo_status_sms_template( $woo_status );
		if ( ! $sms_template ) {
			clicksend_woo_integration_log( 'sms body not found for status - skipping sms', __FILE__, __LINE__ );
			Clicksend_Logger::save_general_log( '', '', 'sms body not found for status - skipping sms' . $woo_status, $order->get_id() );
			return false;
		}

		if ( ! $sms_template['status'] ) {
			clicksend_woo_integration_log( 'Template status is disabled - skipping sms' . print_r( $sms_template, true ), __FILE__, __LINE__ );
			Clicksend_Logger::save_general_log( '', '', $sms_template['body'], $order->get_id(), 0, '', '', 'Template status is disabled - skipping sms' );
			return false;
		}

		// if body is blank.
		if ( ! trim( $sms_template['body'] ) ) {
			clicksend_woo_integration_log( 'Template body is found - skipping sms' . print_r( $sms_template, true ), __FILE__, __LINE__ );
			Clicksend_Logger::save_general_log( '', '', $sms_template['body'], $order->get_id(), 0, '', '', 'Template body is found - skipping sms' );
			return false;
		}

		// dynamic template pending %f first_name.
		/**
		*%first-First name of customer
		*%orderno-Order number
		*%total-Order total price
		*%items - list of purchased items
		*%orderstat - Order status
		*/
		$populated_sms_body['body'] = $sms_template['body'];

		$populated_sms_body['parsed_body'] = self::parse_sms_body( $order, $populated_sms_body['body'] );
		return $populated_sms_body;
	}
	/**
	 * Generate_dynamic_sms_body
	 *
	 * @param object $order valid wooOrder object.
	 * @param string $sms_body valid woo status e.g wc-pending.
	 *
	 * @return string parsed sms template
	 */
	public static function parse_sms_body( $order, $sms_body ) {
		// dynamic template pending %f first_name.
		/**
		*%first-First name of customer
		*%orderno-Order number
		*%total-Order total price
		*%items - list of purchased items
		*%orderstat - Order status
		*/
		$sms_body     = stripslashes( $sms_body );
		$order_data   = $order->get_data();
		$first_name   = $order->get_billing_first_name();
		$currency_hex = Clicksend_SDK::get_currency_hex( $order_data['currency'] );
		$total_amount = $order->get_total();
		// looping over all order-items.
		foreach ( $order->get_items() as $item_id => $item ) {
			$product_name[] = $item->get_name();
		}
		if ( count( $product_name ) > 2 ) {
			// preparing %items + 5 items… like statement.
			$remaining_i = count( $product_name ) - 2;
			$items       = $product_name[0] . ', ' . $product_name[1] . ' +' . $remaining_i . ' items...';
		} elseif ( 2 === count( $product_name ) ) {
			$items = $product_name[0] . ', ' . $product_name[1];
		} else {
			$items = $product_name[0];
		}
		$populated_sms_body = str_ireplace( array( '%first', '%orderno', '%total', '%items', '%orderstat' ), array( $first_name, $order->get_id(), $currency_hex . $total_amount, $items, $order->get_status() ), $sms_body );
		clicksend_woo_integration_log( 'final sms body' . $populated_sms_body, __FILE__, __LINE__ );
		return $populated_sms_body;
	}
	/**
	 * Load tab content
	 *
	 * @param string $tab tab name.
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function tab_content_activate_api_key( $tab ) {
		if ( 'woo-status' !== $tab ) {
			return false;
		}
		// get all order statuses.
		$all_order_status = wc_get_order_statuses();
		global $wpdb;
		$table_name = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_TEMPLATE;

		$templates = $wpdb->get_results( "select * from ${table_name} where temp_group = 'woo-status' order by sort", ARRAY_A );

		foreach ( $templates as $each_row ) {
			$templates[ $each_row['label'] ] = $each_row;
		}

		require_once 'partials/clicksend-partials-woo-status.php';
	}

	/**
	 * Add tab
	 *
	 * @param string $tabs tabs.
	 *
	 * @since    1.0.0
	 */
	public function add_tab( $tabs ) {
		$tabs['woo-status'] = array(
			'label' => 'SMS Automation',
		);
		return $tabs;
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
		 * defined in Clicksend_Woo_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clicksend_Woo_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name . '-woo-integration-admin', plugin_dir_url( __FILE__ ) . 'css/clicksend-woo-integration-admin.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name . '-woo-integration-send-bulk-sms', CLICKSEND_WOO_INTEGRATION_ROOT_BASE_PATH . 'admin/modules/woo-integration/css/clicksend-woo-integration-send-bulk-sms.css', array(), $this->version, 'all' );
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
		 * defined in Clicksend_Woo_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clicksend_Woo_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name . '-woo-integration-admin', plugin_dir_url( __FILE__ ) . 'js/clicksend-woo-integration-admin.js', array( 'jquery' ), $this->version, false );
	}
}
