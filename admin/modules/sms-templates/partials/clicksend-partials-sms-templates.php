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
	<div class="cs_woo_setting_info cs_woo__sms-templates-info">
		<h4>Manual SMS Templates</h4>
		<p class="mt-0 cs_woo__sms-temp-warn">Create SMS templates for easy access on your order page. You may use the placeholders below to personalize messages.</p>
		<h4>%first - First name of customer</h4>
		<h4>%orderno - Order number</h4>
		<h4>%total - Order total price</h4>
		<h4>%items - List of purchased items</h4>
		<h4>%orderstat - Order status</h4>
	</div>

	<!-- START- SMS Templates Module -->
	<input class="cs_woo__sms-templates--ajax-url" type="hidden" value="<?php echo esc_url( $ajax_url ); ?>" />
	<div class="cs_woo__sms-templates--section">
		<!-- <div class="cs_woo__sms-templates--item">
			<div class="cs_woo__sms-templates--name">
				<label for="cswt__name-input"><span>Template Name:</span>
					<input disabled="disabled" id="cswt__name-input" type="" name="" placeholder="Template Name">
				</label>	
			</div>
			<div class="cs_woo__sms-templates--body">
				<label for="cswt__body-input">
					<span>Body:
						<span class="cs_woo__sms-templates--word-count">
						</span>
					</span>
					<textarea oninput="checkWords(this)" disabled="disabled" id="cswt__body-input" placeholder="Template Message"></textarea>
				</label>
			</div>
			<div class="cs_woo__sms-templates--actions">
				<div class="cs_woo__sms-templates--actions">
					<div class="cs_woo__sms-templates--btn-group">
						<button onclick="toggleDisable(this)" class="cs_woo__sms-templates--btn cs_woo__sms-templates--btn-info"><i class="fa fa-edit"></i></button>
						<button class="cs_woo__sms-templates--btn cs_woo__sms-templates--btn-danger"><i class="fa fa-trash"></i></button>
					</div>
				</div>
			</div>
		</div> -->
		<!-- <div class="cs_woo__sms-templates--item">
			<div class="cs_woo__sms-templates--name">
				<label for="cswt__name-input"><span>Template Name:</span>
					<input disabled="disabled" id="cswt__name-input" type="" name="" placeholder="Template Name">
				</label>	
			</div>
			<div class="cs_woo__sms-templates--body">
				<label for="cswt__body-input">
					<span>Body:
						<span class="cs_woo__sms-templates--word-count">
						</span>
					</span>
					<textarea oninput="checkWords(this)" disabled="disabled" id="cswt__body-input" placeholder="Template Message"></textarea>
				</label>
			</div>
			<div class="cs_woo__sms-templates--actions">
				<div class="cs_woo__sms-templates--btn-group">
					<button onclick="toggleDisable(this)" class="cs_woo__sms-templates--btn cs_woo__sms-templates--btn-info"><i class="fa fa-edit"></i></button>
					<button class="cs_woo__sms-templates--btn cs_woo__sms-templates--btn-danger"><i class="fa fa-trash"></i></button>
				</div>
			</div>
		</div> -->
	</div>
	<!-- <button onclick="saveSmsTemplates()" style="margin: 10px;" class="cs_woo__sms-templates--btn cs_woo__sms-templates--btn-primary">Save Changes</button> -->
	<!-- END- SMS Templates Module -->
</div>
