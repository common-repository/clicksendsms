<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link    https://kumaranup594.github.io/
 * @since   1.0.0
 *
 * @package Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/admin/partials
 */

?>

<div class="notice notice-success is-dismissible cwi-notice-welcome">
	<p>
		<?php
		printf(
			/* translators: %s: Name of this plugin */
			esc_html( __( 'Thank you for installing %1$s!', 'clicksend-woo-integration' ) ),
			'ClickSend SMS for WordPress'
		);
		?>
		<a href="<?php echo esc_html( $setting_page ); ?>"><?php esc_html_e( 'Click here', 'clicksend-woo-integration' ); ?></a> <?php esc_html_e( 'to configure the plugin.', 'clicksend-woo-integration' ); ?>
	</p>
</div>
<script type="text/javascript">
	jQuery( document ).ready( function($) {
		$( document ).on( 'click', '.cwi-notice-welcome button.notice-dismiss', function( event ) {
			event.preventDefault();
			$.post( ajaxurl, {
				action: '<?php echo 'cwi_dismiss_dashboard_notices'; ?>',
				nonce: '<?php echo esc_html( wp_create_nonce( 'cwi-nonce' ) ); ?>'
			});
			$( '.cwi-notice-welcome' ).remove();
		});
	});
</script>
