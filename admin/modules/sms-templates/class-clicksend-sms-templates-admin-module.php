<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link   https://kumaranup594.github.io/
 * @since  1.0.0
 *
 * @package Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/admin/modules/woo-integration
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * @link   https://kumaranup594.github.io/
 * @since  1.0.0
 *
 * @package Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/admin/modules/woo-integration
 */
class Clicksend_Sms_Templates_Admin_Module {

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
	 * @param  string $plugin_name   The name of this plugin.
	 * @param  string $version The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

		// filter to add new tab.
		add_action( 'clicksend_admin_tabs', array( $this, 'add_tab' ) );
		// add content of tab against above one.
		add_action( 'clicksend_admin_tab_content', array( $this, 'tab_content' ) );

		// add sub menu.
		add_action( 'admin_menu', array( $this, 'add_admin_sub_page' ), 25 );

		// START- AJAX Requests.
		add_action( 'wp_ajax_clicksend_get_sms_templates', array( $this, 'clicksend_get_sms_templates' ) );
		add_action( 'wp_ajax_clicksend_delete_template', array( $this, 'clicksend_delete_template' ) );
		add_action( 'wp_ajax_clicksend_upsert_template', array( $this, 'clicksend_upsert_template' ) );

		// END- AJAX Requests.
	}
	/**
	 * Get sms templates available
	 *
	 * @since    1.0.0
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function clicksend_get_sms_templates() {
		global $wpdb;
		$table_name = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_TEMPLATE;
		$_errs = array();
		// default positive response.
		$res = array(
			'succ' => true,
			'public_msg' => __( 'Templates Fetched Successfully', 'clicksend-woo-integration' ),
		);
		try {
			// receiving form details.
			if ( isset( $_POST['_nonce'] ) ) {

				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'love-nonce' ) ) {
					throw new Exception( __( 'Security check failed', 'clicksend-woo-integration' ) );
				}
			}

			$results = $wpdb->get_results(
				$wpdb->prepare( "SELECT * FROM ${table_name} WHERE temp_group = 'manual' ORDER BY created_at DESC" ),
				ARRAY_A
			);
			$res['sms_templates'] = $results;
		} catch ( Exception $exception ) {
			$res['succ']       = false;
			$res['errs']       = $_errs;
			$res['public_msg'] = $exception->getMessage();
		}
		wp_die( wp_json_encode( $res ) );
	}
	/**
	 * Save template
	 *
	 * @since    1.0.0
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function clicksend_upsert_template() {
		global $wpdb;
		$table_name = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_TEMPLATE;
		$_errs = array();
		$res = array(
			'succ' => true,
			'public_msg' => __( 'Templates Inserted Successfully', 'clicksend-woo-integration' ),
		);

		try {
			if ( isset( $_POST['_nonce'] ) ) {

				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'love-nonce' ) ) {
					throw new Exception( __( 'Security check failed', 'clicksend-woo-integration' ) );
				}
			}

			$result = 0;
			unset( $_POST['action'] );
			unset( $_POST['_nonce'] );
			foreach ( $_POST as $k => $v ) {

				if ( ! isset( ($v['template_label']) ) || '' === sanitize_text_field($v['template_label']) ) {
					throw new Exception( __( 'Template name required.', 'clicksend-woo-integration' ) );
				}
				if ( ! isset( $v['template_body'] ) || '' === sanitize_text_field($v['template_body']) ) {
					throw new Exception( __( 'Template body required.', 'clicksend-woo-integration' ) );
				}

				$data = array(
					'temp_group' => 'manual',
					'label'      => sanitize_text_field($v['template_label']),
					'body'       => sanitize_text_field($v['template_body']),
					'created_by' => get_current_user_id(),
					'updated_by' => get_current_user_id(),
				);
				if ( isset( $v['id'] ) && ! empty( $v['id'] ) ) {

					$res['public_msg'] = __( 'Templates have been successfully updated.', 'clicksend-woo-integration' );
					$template_lable    = trim( $v['template_label'] );
					$id                = $v['id'];
					$existing_templates = $wpdb->get_results( "SELECT * FROM ${table_name} WHERE  label like '${template_lable}' AND id != ${id}", ARRAY_A );

					if ( count( $existing_templates ) > 0 ) {
						throw new Exception( __( 'This is already in use, please choose different template name.', 'clicksend-woo-integration' ) );
					}

					$where = array( 'id' => trim( $v['id'] ) );
					$result = $wpdb->update( $table_name, $data, $where );
				} else {
					$template_lable    = trim( $v['template_label'] );
					$existing_templates = $wpdb->get_results( "SELECT * FROM ${table_name} WHERE  label like '${template_lable}' ", ARRAY_A );
					if ( count( $existing_templates ) > 0 ) {
						throw new Exception( __( 'This is already in use, please choose different template name.', 'clicksend-woo-integration' ) );
					}

					$result = $wpdb->insert( $table_name, $data );
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
	 * Delete template
	 *
	 * @since    1.0.0
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function clicksend_delete_template() {
		global $wpdb;
		$table_name = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_TEMPLATE;
		$_errs = array();
		$res = array(
			'succ' => true,
			'public_msg' => __( 'Template Deleted Successfully', 'clicksend-woo-integration' )
		);
		try {
			if ( isset( $_POST['_nonce'] ) ) {

				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'love-nonce' ) ) {
					throw new Exception( __( 'Security check failed', 'clicksend-woo-integration' ) );
				}
			}


			if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) ) {
				throw new Exception( __( 'Template id required.', 'clicksend-woo-integration' ) );
			}

			$where = array(
				'id' => trim( sanitize_text_field($_POST['id']) ),
			);
			$result = $wpdb->delete( $table_name, $where );
			if ( ! $result ) {
				throw new Exception( __( 'Some Problem Occurred While Processing', 'clicksend-woo-integration' ) );
			}
		} catch ( Exception $exception ) {
			$res['succ']       = false;
			$res['errs']       = $_errs;
			$res['public_msg'] = $exception->getMessage();
		}
		wp_die( wp_json_encode( $res ) );
	}
	/**
	 * Updating Status
	 *
	 * @since    1.0.0
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	public function clicksend_update_status() {
		global $wpdb;
		$table_name = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_TEMPLATE;
		$_errs = array();
		$res = array(
			'succ' => true,
			'public_msg' => __( 'Status Updated Successfully', 'clicksend-woo-integration' ),
		);
		try {
			if ( isset( $_POST['_nonce'] ) ) {

				if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['_nonce'] ) ), 'love-nonce' ) ) {
					throw new Exception( __( 'Security check failed', 'clicksend-woo-integration' ) );
				}
			}

			if ( null === sanitize_text_field($_POST['status']) || '' ===sanitize_text_field ($_POST['status']) ) {
				throw new Exception( __( 'Status Is Required', 'clicksend-woo-integration' ) );
			}
			if ( ! isset( $_POST['id'] ) || empty( $_POST['id'] ) ) {
				throw new Exception( __( 'Template id required.', 'clicksend-woo-integration' ) );
			}

			$where = array(
				'id' => trim( sanitize_text_field($_POST['id']) ),
			);
			$data = array(
				'status' => trim( sanitize_text_field($_POST['status']) ),
			);
			$result = $wpdb->update( $table_name, $data, $where );
			if ( ! $result ) {
				throw new Exception( __( 'Some Problem Occurred While Processing', 'clicksend-woo-integration' ) );
			}
		} catch ( Exception $exception ) {
			$res['succ']       = false;
			$res['errs']       = $_errs;
			$res['public_msg'] = $exception->getMessage();
		}
		wp_die( wp_json_encode( $res ) );
	}
	/**
	 * Registering new tab
	 *
	 * @since    1.0.0
	 */
	public function add_admin_sub_page() {
		add_submenu_page( 'clicksend-sms', 'Manual Templates', 'SMS Templates', 'manage_options', '/admin.php?page=clicksend-sms&tab=sms-templates', '', 25 );
	}

	/**
	 * Loads tab content
	 *
	 * @since    1.0.0
	 *
	 * @param string $tab name of the tab.
	 */
	public function tab_content( $tab ) {
		if ( 'sms-templates' !== $tab ) {
			return false;
		}
		$ajax_url = admin_url( 'admin-ajax.php' );
		require_once 'partials/clicksend-partials-sms-templates.php';
	}

	/**
	 * Adding tab
	 *
	 * @since    1.0.0
	 *
	 * @param string $tabs name of the tab.
	 */
	public function add_tab( $tabs ) {
		$tabs['sms-templates'] = array(
			'label' => 'SMS Templates',
		);
		return $tabs;
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
		 * An instance of this class should be passed to the run(  ) function
		 * defined in Clicksend_Woo_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clicksend_Woo_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name . '-sms-templates-admin', plugin_dir_url( __FILE__ ) . 'css/clicksend-woo-integration-admin.css', array(), $this->version, 'all' );
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
		 * An instance of this class should be passed to the run(  ) function
		 * defined in Clicksend_Woo_Integration_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Clicksend_Woo_Integration_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name . '-sms-templates-admin', plugin_dir_url( __FILE__ ) . 'js/clicksend-woo-integration-admin.js', array( 'jquery' ), $this->version, false );
		wp_localize_script(
			$this->plugin_name . '-sms-templates-admin',
			'postlove',
			array(
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'security' => wp_create_nonce( 'love-nonce' ),
			)
		);
	}
}
