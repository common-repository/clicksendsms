<?php
/**
 * It will contain all common global functions
 *
 * @link       https://kumaranup594.github.io/
 * @since      1.0.0
 *
 * @package    Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/includes
 */

if ( ! function_exists( 'clicksend_woo_integration_validate_sms_body' ) ) {
	/**
	 * Validate the sms body
	 *
	 * @param string $sms_body this is sms body.
	 *
	 * @throws InvalidArgumentException If the provided argument is not valid.
	 */
	function clicksend_woo_integration_validate_sms_body( $sms_body ) {
		// implement this function.
		if ( ! isset( $sms_body ) || empty( $sms_body ) ) {
			throw new Exception( __( 'The message body cannot be empty.' ) );
		} elseif ( strlen( $sms_body ) > 1224 ) {
			throw new Exception( __( 'Maximum number of allowed characters is 1,224' ) );
		}
		return true;
	}
}
if ( ! function_exists( 'clicksend_woo_integration_log' ) ) {
	/**
	 * Save the error log
	 *
	 * @param array  $data it will container data to print.
	 * @param string $file it will container file name.
	 * @param string $line it will contain on which lines number error happened.
	 */
	function clicksend_woo_integration_log( $data, $file = '', $line = '' ) {
		$upload_dir = wp_upload_dir();
		$myfile     = fopen( $upload_dir['basedir'] . '/anup-gp-logs-' . gmdate( 'Y-m-d' ) . '-1.txt', 'a' );
		$txt        = '\nDate & Time: ' . gmdate( 'Y-m-d H:i:s' ) . ' \n';
		$txt       .= 'File = ' . $file;
		$txt       .= '\n';
		$txt       .= 'Line = ' . $line;
		$txt       .= '\n';
		$txt       .= $data;
		$txt       .= '\n';
		fwrite( $myfile, $txt );
		fclose( $myfile );
	}
}
