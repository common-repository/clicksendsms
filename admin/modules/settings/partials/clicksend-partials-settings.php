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
<div class="cs_setting_wrapper">
	<div class="cs_setting_header">
		<div class="col_md">
			<h4>ClickSend SMS Settings</h4>
		</div>
		<div class="col_md">
			<p>
				Current balance:
				<span>
					<?php
					if ( isset( $cs_current_bal['currency_sign'], $cs_current_bal['current_bal'] ) ) {
						echo esc_html( $cs_current_bal['currency_sign'] . ' ' . round( $cs_current_bal['current_bal'], 2 ) );
					} else {
						echo '--:--';
					}
					?>
				</span>
			</p>
		</div>
	</div>
	<p>Create SMS Templates for easy access on your order page. You may use the placeholders to personalize your messages.</p>
	<div class="cs_setting_info">
		<ol>
			<li>Don't have ClickSend account? <a href="https://dashboard.clicksend.com/signup/" target="_blank"> Create one here.</a></li>
			<li>Find your ClickSend credentials <a href="https://dashboard.clicksend.com/account/subaccounts" target="_blank"> here.</a></li>
			<li>Top up your account <a href="https://dashboard.clicksend.com/#/account/billing-recharge/top-up-account" target="_blank">here.</a></li>
		</ol>
	</div>
	<form method="POST" id="cs_settings_form" onsubmit="saveData( 'cs_settings_form','Please Wait ...',false,cs_afterFormSubmit )" action="<?php echo esc_url( admin_url( 'admin-ajax.php' ) ); ?>">
		<input type="hidden" name="action" id="action" value="clicksend_process_active_api_key">
		<input type="hidden" name="clicksend_process_active_api_key_n" id="clicksend_process_active_api_key_n" value="clicksend_process_active_api_key">
		<input type="hidden" name="_nonce" id="nonce">
		<div class="cs_setting_opt">
			<div class="form-group">
				<div class="col_sm">
					<label for="cs_username">ClickSend Username</label>
				</div>
				<div class="col_md">
					<input type="text" name="cs_username" id="cs_username" value="<?php echo esc_html( $cs_username ); ?>">
				</div>
			</div>
			<div class="form-group">
				<div class="col_sm">
					<label for="cs_api_key">ClickSend API Key</label>
				</div>
				<div class="col_md">
					<input type="text" name="cs_api_key" id="cs_api_key" value="<?php echo esc_html( $cs_api_key ); ?>">
				</div>
			</div>
			<div class="form-group">
				<div class="col_sm">
					<label for="cs_sender_name">Sender Number / From (Optional)</label>
				</div>
				<div class="col_md">
					<input type="text" name="cs_sender_name" id="cs_sender_name" value="<?php echo esc_html( $cs_sender_name ); ?>">
					<p><small>If left blank, your message will be sent from a ClickSend shared number. <a href="https://help.clicksend.com/article/4kgj7krx00-what-is-a-sender-id-or-sender-number" target="_blank">More info</a></small></p>
				</div>

			</div>
			<div class="form-group">
				<div class="col_sm">
					<label for="cs_sms_send_to">Send SMS to</label>
				</div>
				<div class="col_md">
					<select name="cs_sms_send_to" id="cs_sms_send_to">
						<option value="shipping" <?php echo 'shipping' === $cs_sms_send_to ? 'selected' : ''; ?>>Shipping Phone number</option>
						<option value="billing" <?php echo 'billing' === $cs_sms_send_to ? 'selected' : ''; ?>>Billing Phone number</option>
					</select>
					<p><small>Selected field will be required on the checkout form</small></p>
				</div>
			</div>

			<div class="form-group">
				<div class="col_sm">

				</div>
				<div class="col_md">
					<div class="form-group err_desc hidden">
						<p></p>
					</div>
					<button type="submit" class="button button-primary button-large">Save Changes</button>
				</div>
			</div>
		</div>
	</form>
</div>
<script>
	jQuery( document ).ready(function() {
		jQuery( '#nonce' ).val( postlove.security );
	})
	jQuery( "#cs_settings_form" ).submit(function(e) {
		e.preventDefault();
	});

	function cs_afterFormSubmit( res ) {
		if ( res.succ ) {
			window.location.reload();
		}
	}
</script>
