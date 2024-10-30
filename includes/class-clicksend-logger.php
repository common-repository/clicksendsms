<?php
/**
 * It will manage all curl SDK
 *
 * @link  https://kumaranup594.github.io/
 * @since 1.0.0
 *
 * @package Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/includes
 */

/**
 * It will manage all curl SDK
 *
 * @link  https://kumaranup594.github.io/
 * @since 1.0.0
 *
 * @package Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/includes
 */
class Clicksend_Logger {

	const LOG_GROUP_WOO_ORDERS = 'woo-orders';
	const LOG_GROUP_MANUAL     = 'manual';
	const LOG_GROUP_GENERAL    = 'general';


	const LOG_TYPE_OUTBOUND    = 'outbound';
	const LOG_TYPE_INBOUND     = 'inbound';

	/**
	 * Save_general_log
	 *
	 * @param string $country_ext country_ext.
	 * @param string $mobile mobile.
	 * @param string $sms_body sms_body.
	 * @param string $table_id table_id.
	 * @param string $created_by created_by.
	 * @param string $table table.
	 * @param string $type type.
	 * @param string $remarks remarks.
	 */
	public static function save_general_log( $country_ext, $mobile, $sms_body, $table_id, $created_by = 0, $table = 'general', $type = 'outbound', $remarks = null ) {
		return false;
		self::save( $country_ext, $mobile, $sms_body, $table_id, $created_by = 0, $table = 'general', $type = 'outbound', $remarks = null );
	}

	/**
	 * This function punch the log into CLICKSEND_WOO_INTEGRATION_TABLE_SMS_LOG table
	 *
	 * @param string $country_ext country_ext.
	 * @param string $mobile mobile.
	 * @param string $sms_body sms_body.
	 * @param string $table_id table_id.
	 * @param string $created_by created_by.
	 * @param string $table table.
	 * @param string $type type.
	 * @param string $remarks remarks.
	 */
	public static function save( $country_ext, $mobile, $sms_body, $table_id, $created_by = 0, $table = 'woo-orders', $type = 'outbound', $remarks = null ) {
		// implement this function.
		global $wpdb;
		$table_name = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_LOG;
		// setting default timezone for date funtion.
		$data_to_insert = array(
			'wp_table'    => $table,
			'wp_id'       => $table_id,
			'country_ext' => $country_ext,
			'phone'       => $mobile,
			'sms'         => $sms_body,
			'type'        => $type,
			'created_at'  => gmdate( 'Y-m-d H:i:s' ),
			'created_by'  => $created_by ? $created_by : get_current_user_id(),
			'remarks'     => $remarks,
		);
		clicksend_woo_integration_log( 'data to insert in log' . print_r( $data_to_insert, true ), __FILE__, __LINE__ );
		if ( ! $wpdb->insert( $table_name, $data_to_insert ) ) {
			clicksend_woo_integration_log( 'last query' . $wpdb->last_query, __FILE__, __LINE__ );
			clicksend_woo_integration_log( 'last _err' . $wpdb->last_error, __FILE__, __LINE__ );
			return __( 'There was an error while updating log.' );
		}
		// save the log and return the id of the newely created log.
		$last_log_id = $wpdb->insert_id;
		return $last_log_id;
	}

	/**
	 * This function supports
	 * $table_id
	 * $table = default woo-orders
	 *
	 * @param array $filters filters.
	 */
	public static function get( $filters ) {
		// implement this function.
		// this function will tech the logs and return in assoc array based on filters.
		global $wpdb;
		$table_name = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_LOG;
		$type       = self::LOG_GROUP_WOO_ORDERS;
		if ( isset( $filters['order_id'] ) && ! empty( $filters['order_id'] ) ) {
			$order_id = $filters['order_id'];
			$logs     = $wpdb->get_results( "SELECT * from ${table_name} WHERE wp_id = ${order_id} and wp_table = '${type}' ORDER BY created_at DESC", ARRAY_A );
			return $logs;
		}
	}
}
