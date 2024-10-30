<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link   https://kumaranup594.github.io/
 * @since  1.0.0
 *
 * @package Clicksend_Woo_Integration
 * @subpackage Clicksend_Woo_Integration/admin/partials
 */

?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<div class="cs_woo_setting_wrapper">
	<div class="cs_woo_setting_info">
		<h4>Automated SMS</h4>
		<p>Enable this feature if you want to send SMS to customer on order status change.
			You may use the placeholders below to personalize messages. SMS messages have a limit of 1,224 characters.</p>
		<h4>%first - First name of customer</h4>
		<h4>%orderno - Order number</h4>
		<h4>%total - Order total price</h4>
		<h4>%items - List of purchased items</h4>
		<h4>%orderstat - Order status</h4>
	</div>
	<div class="cs_woo_setting_status">
		<form method="POST" id="cs_woo_setting_form" onsubmit="saveData( 'cs_woo_setting_form','Please Wait ...',false,cs_afterWooSettingFormSubmit )" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
			<input type="hidden" name="action" id="action" value="clicksend_process_save_status_setting">
			<input type="hidden" name="clicksend_process_save_status_setting_n" id="clicksend_process_save_status_setting_n" value="clicksend_process_save_status_setting">
			<input type="hidden" name="_nonce" id="nonce">
			<div class="status_body_title">
				<div class="col_xs">
					<h4>Status</h4>
				</div>
				<div class="col_md">
					<h4>Automatic SMS Message Body</h4>
				</div>
				<div class="col_xs">
					<h4>Enabled</h4>
				</div>
			</div>
			<div class="status_body">
				<?php
				$sort = 1;
				foreach ( $all_order_status as $status_slug => $order_status ) {
					?>
					<div class="status_field">
						<div class="col_xs">
							<input type="hidden" name="woo_status_id[<?php echo esc_attr( $status_slug ); ?>]" value="<?php echo isset( $templates[ $status_slug ] ) ? esc_attr( $templates[ $status_slug ]['id'] ) : ''; ?>">
							<input type="hidden" name="woo_sort[<?php echo esc_attr( $status_slug ); ?>]" value="<?php echo esc_attr( $sort++ ); ?>">
							<label for="<?php echo esc_attr( $status_slug ); ?>"><?php echo esc_attr( $order_status ); ?></label>
						</div>
						<div class="col_md">
							<textarea name=" woo_status[<?php echo esc_attr( $status_slug ); ?>]" id="<?php echo esc_attr( $status_slug ); ?>" placeholder="<?php echo esc_attr( $order_status ) . 'Template'; ?>"><?php echo isset( $templates[ $status_slug ] ) ? esc_attr( $templates[ $status_slug ]['body'] ) : ''; ?></textarea>
						</div>
						<div class="col_xs">
							<input type="checkbox" name="checked[<?php echo esc_attr( $status_slug ); ?>]" <?php echo ( isset( $templates[ $status_slug ] ) && '1' === $templates[ $status_slug ]['status'] ) ? 'checked' : ''; ?>>
						</div>
					</div>
					<?php
				};
				?>
				<div class="status_field col_xs">
					<button class="button button-primary button-large">Save changes</button>
				</div>
				<div class="form-group err_desc hidden status_field">
					<p></p>
				</div>
			</div>
		</form>
	</div>
</div>
<script>
	jQuery(document).ready(function() {
		jQuery('#nonce').val(postlove.security);
	})
	jQuery("#cs_woo_setting_form").submit(function(e) {
		e.preventDefault();
	});

	function cs_afterWooSettingFormSubmit(res) {
		if (res.succ) {
			window.location.reload();
		}
	}
</script>
