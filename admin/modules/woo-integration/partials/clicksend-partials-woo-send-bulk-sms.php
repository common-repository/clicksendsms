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

<div class="cs_woo_setting_info cs_woo__sms-templates-info">
	<h4>Manage Manual SMS templates</h4>
	<p>Configure the message that will be send manually.</p>
	<p>Go to order page to send manual bulk or individual messages.</p>
	<p class="mt-0 cs_woo__sms-temp-warn">You can use the following strings below optionally to fetch values. Values are dynamic and affects the number of character when sent.</p>
	<h4>%first - First name of customer</h4>
	<h4>%orderno - Order number</h4>
	<h4>%total - Order total price</h4>
	<h4>%items - List of purchased items</h4>
	<h4>%orderstat - Order status</h4>
</div>
<div class="clicksend_bulk_order_container">
	<form action="#" id="bulkSmsForm">
		<h4>SMS TEMPLATE :</h4>
		<input type="hidden" class="clicksend_ajax_url" value="<?php echo esc_url( $ajax_url ); ?>">
		<input type="hidden" class="clicksend_order_id" value="<?php echo esc_html( $first_order_id ); ?>">
		<div class="clicksend_bulk_order_template_selection_wrapper">
			<textarea name="cs_final_sms_body" rows="7" id="cs_final_sms_body"><?php echo esc_html( $sms_template['body'] ); ?></textarea>
			<div class="parsedBody">
				<h4>EXAMPLE SMS FOR ORDER ID-<?php echo esc_html( $first_order_id ); ?> :</h4>
				<p id="parsedBody"><?php echo esc_html( $populated_sms_body['parsed_body'] ); ?></p>
			</div>
			<?php if ( count( $order_status_temp_opt ) > 0 ) { ?>
				<select name="cs_template_opt" id="cs_template_opt">
					<option value="" selected disabled>--select template--</option>
					<?php
					foreach ( $order_status_temp_opt as $eachtemplate ) {
						?>
						<option value="<?php echo esc_html( $eachtemplate['id'] ); ?>"><?php echo esc_html( $eachtemplate['label'] ); ?></option>
						<?php
					};
					?>
				</select>
			<?php }; ?>
			<div class="action_btn_wrapper">
				<button class="cs_send_btn button save_order button-primary">Send</button>
				<a href="<?php echo esc_url( admin_url( 'edit.php?post_type=shop_order' ) ); ?>" class=" button  button-secondary">Go Back</a>
			</div>
			<div class="form-group err_desc hidden">
				<p></p>
			</div>
		</div>
	</form>
	<div class="bulk_send_sms_table_wrapper">
		<div class="tableFixHead">
			<table class="wp-list-table widefat fixed striped table-view-list posts">
				<thead>
					<th class="manage-column column-order_number column-primary">Order Id</th>
					<th class="manage-column column-order_number column-primary">Customer Name</th>
					<th class="manage-column column-order_number column-primary">Customer Recipient</th>
					<th class="manage-column column-order_number column-primary">Order Amount</th>
				</thead>
				<tbody>
					<?php foreach ( $orders as $each_order ) { ?>
						<tr>
							<td><?php echo esc_html( $each_order['order_id'] ); ?></td>
							<td><?php echo esc_html( $each_order['customer_name'] ); ?></td>
							<td><?php echo esc_html( $each_order['contact'] ); ?></td>
							<td><?php echo esc_html( $each_order['amount'] ); ?></td>
						</tr>
					<?php }; ?>
				</tbody>
			</table>
		</div>
	</div>
</div>
<script>
	jQuery(".cs_send_btn").click(function(e) {
		e.preventDefault();
		if (confirm('Are you sure? Click ok to send this to everyone')) {
			formID = 'bulkSmsForm';
			formData = {
				'orders': <?php echo wp_json_encode( $orders ); ?>,
				'sms_body': document.querySelector('#cs_final_sms_body').value,
				'action': 'clicksend_bulk_order_send_sms',
				'_nonce': postlove.security
			};
			sendBulkSms(formID, formData);
		}
	});
	jQuery("#cs_template_opt").change(function() {
		formID = 'bulkSmsForm';
		formData = {
			'order_id': document.querySelector('.clicksend_order_id').value,
			'template_id': document.querySelector('#cs_template_opt').value,
			'action': 'clicksend_single_order_parse_template'
		};
		getManualTemplateData(formID, formData);
		setTimeout(prepareParsedBody, 1000);
	})
	jQuery("#cs_final_sms_body").on('change keyup paste', function() {
		prepareParsedBody();
	})

	function prepareParsedBody() {
		formID = 'bulkSmsForm';
		if (document.querySelector('#cs_final_sms_body').value == '') {
			document.querySelector('.cs_send_btn').disabled = true;
			document.querySelector('.parsedBody').innerHTML = '';
		} else {
			document.querySelector('.cs_send_btn').disabled = false;
		}
		formData = {
			'first_order_id': document.querySelector('.clicksend_order_id').value,
			'template': document.querySelector('#cs_final_sms_body').value,
			'action': 'clicksend_parse_manual_template_contents'
		};
		getParsedManualTemplate(formID, formData);
	}
</script>
