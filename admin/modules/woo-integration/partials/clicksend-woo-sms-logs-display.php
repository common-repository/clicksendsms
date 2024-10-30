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
<input type="hidden" class="clicksend_ajax_url" value="<?php echo esc_url( $ajax_url ); ?>">
<input type="hidden" class="clicksend_order_id" value="<?php echo esc_html( $post->ID ); ?>">
<div class="clicksend__logs-section">
	<!-- <div class="clicksend__logs-item">
		<div class="clicksend__log-msg">
			This is the test  message which will be displayed on metabox area.
		</div>
		<hr>
		<div class="clicksend__log-date">
			Date: 17 Jan 2022 3:42pm			
		</div>
	</div>

	<div class="clicksend__logs-item">
		<div class="clicksend__log-msg">
			This is the test  message which will be displayed on metabox area.
		</div>
		<hr>
		<div class="clicksend__log-date">
			Date: 17 Jan 2022 3:42pm			
		</div>
	</div> -->
</div>
<hr>
<div class="clicksend_template_selection_wrapper">
	<h4>Write your SMS
		<?php
		$tip = 'Max of 1,224 characters. <a class="cs_help_block" href="https://clicksend.helpdocs.io/article/h474eseq3a-how-many-characters-can-i-send-in-an-sms" target="blank">More info</a>';
		echo wc_help_tip( $tip, false );
		?>
	</h4>
	<textarea name="cs_final_sms_body" rows="7" id="cs_final_sms_body"></textarea>
	<div class="action_btn_wrapper">
		<select name="cs_template_opt" id="cs_template_opt">
			<option value="" selected>--select template--</option>
			<?php
			foreach ( $order_status_temp_opt as $eachtemplate ) {
				?>
				<option value="<?php echo esc_html( $eachtemplate['id'] ); ?>"><?php echo esc_html( $eachtemplate['label'] ); ?></option>
				<?php
			};
			?>
		</select>
		<button class="cs_send_btn button save_order button-primary">Send</button>
	</div>
	<div class="form-group err_desc hidden">
		<p></p>
	</div>
</div>
<script>
	jQuery(".cs_send_btn").click(function(e) {
		e.preventDefault();
		formID = 'post';
		formData = {
			'order_id': document.querySelector('.clicksend_order_id').value,
			'sms_body': String(document.querySelector('#cs_final_sms_body').value),
			'send': true,
			'action': 'clicksend_single_order_send_sms'
		};
		sendSms(formID, formData);

	});
	jQuery("#cs_template_opt").change(function() {
		formID = 'post';
		formData = {
			'order_id': document.querySelector('.clicksend_order_id').value,
			'template_id': document.querySelector('#cs_template_opt').value,
			'action': 'clicksend_single_order_parse_template'
		};
		getTemplateData(formID, formData);
	})
</script>
