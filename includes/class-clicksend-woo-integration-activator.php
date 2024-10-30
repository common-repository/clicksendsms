<?php
/**
 * Fired during plugin activation
 *
 * @link       https://kumaranup594.github.io/
 * @since      1.0.0
 *
 * @package    Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/includes
 */
class Clicksend_Woo_Integration_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		self::cs_create_tables();
		self::cs_fill_in_default_templates();
	}
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function cs_create_tables() {
		global $wpdb;
		// creating log table.
		$logs_table = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_LOG;
		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS  ${logs_table}  ( id  bigint(20) unsigned NOT NULL AUTO_INCREMENT,wp_table varchar(25) NOT NULL DEFAULT 'woo-orders' COMMENT 'for future integrations',
			wp_id bigint(20) unsigned DEFAULT NULL COMMENT 'woo-order.id pk',
			country_ext varchar(5) DEFAULT NULL COMMENT 'without +',
			phone varchar(25) DEFAULT NULL,
			sms text DEFAULT NULL,
			type varchar(15) NOT NULL DEFAULT 'outbound' COMMENT 'it can be inbound',
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , 
			created_by INT NOT NULL DEFAULT '0' COMMENT '0 means auto generated' , 
			remarks text DEFAULT NULL COMMENT 'it will keep response and other remarks',
			PRIMARY KEY (id),
			KEY type (type),
			KEY wp_id (wp_id),
			KEY country_ext (country_ext),
			KEY phone (phone),
			KEY wp_table (wp_table)
		   ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 AUTO_INCREMENT=1;"
		);

		// creating sms template.
		$template_table = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_TEMPLATE;
		$wpdb->query(
			"CREATE TABLE IF NOT EXISTS ${template_table} ( 
			id INT UNSIGNED NOT NULL AUTO_INCREMENT , 
			label VARCHAR(50) NOT NULL COMMENT 'required and can\'t be null - keep woo status' , 
			body TEXT NULL DEFAULT NULL , 
			status BOOLEAN NOT NULL DEFAULT TRUE , 
			temp_group VARCHAR(15) NOT NULL DEFAULT 
			'woo-status' COMMENT 'it can be manual for manual templates' , 
			created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP , 
			updated_at TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP , 
			updated_by INT NULL DEFAULT NULL , 
			created_by INT NOT NULL DEFAULT '0' COMMENT '0 means auto generated' , 
			sort SMALLINT UNSIGNED NULL DEFAULT NULL COMMENT 'keep ordering for backend' , 
			PRIMARY KEY (id), INDEX (temp_group)) ENGINE = InnoDB AUTO_INCREMENT=1;"
		);
	}
	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function cs_fill_in_default_templates() {
		global $wpdb;
		/**
		 * This function will fill in the default templates
		 */
		$table_to_insert = $wpdb->prefix . CLICKSEND_WOO_INTEGRATION_TABLE_SMS_TEMPLATE;

		$template_array = array(
			'wc-pending'    => 'Hi %first, your items are waiting for you. Kindly settle your amount due of %total within 24 to 48 hours so that we can process your order.',
			'wc-processing' => 'Hi %first, thank you for your order (%orderno). Your order is currently in processing.',
			'wc-on-hold'    => 'Hi %first, we are sorry to inform you that your order (%orderno) is on hold. For more information please check your email.',
			'wc-completed'  => 'Hi %first, your order (%orderno) has now been completed. We look forward to serving you in the future.',
			'wc-cancelled'  => 'Hi %first, your order (%orderno) has been cancelled. For more information please check your email.',
			'wc-refunded'   => 'Hi %first, your refund of %total has been processed for order number: (%orderno). For more information please check your email.',
			'wc-failed'     => 'Hi %first, your order (%orderno) has been cancelled. For more information please check your email.',
		);

		foreach ( $template_array as $status => $template ) {
			$id   = $wpdb->get_var( "SELECT id FROM ${table_to_insert} where label='${status}'" );
			$body = $wpdb->get_var( "SELECT body FROM ${table_to_insert} where label='${status}'" );
			if ( empty( $id ) || ! isset( $id ) ) {
				$insert = $wpdb->insert(
					$table_to_insert,
					array(
						'label' => $status,
						'body'  => $template,
					),
					array( '%s', '%s' )
				);
			} elseif ( isset( $id ) && null === $body || '' === $body ) {
				$update = $wpdb->update(
					$table_to_insert,
					array(
						'label' => $status,
						'body'  => $template,
					),
					array( 'label' => $status )
				);
			}
		}
	}
}
