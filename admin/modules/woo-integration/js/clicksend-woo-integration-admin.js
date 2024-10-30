(function( $ ) {
	'use strict';

	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */

})( jQuery );
window.ajaxHits = {};
	//this function will append the template body to the textarea
	function getParsedManualTemplate(formID,formData){
		preloaderInlineOn(formID, "p_info",'Please wait.....');
		if (typeof window.ajaxHits[ajaxUrl] != "undefined") {
			window.ajaxHits[ajaxUrl].abort();
		}
		hideExampleAndActionPart();
		window.ajaxHits[ajaxUrl] = jQuery.ajax({
			url:ajaxUrl,
			type: 'GET',
			data:formData,
			dataType: 'json',
			success: function (response) {
				preloaderInlineOff();
				// preloaderInlineOn(formID, response.succ ? 'p_success' : 'p_danger', response.public_msg);
				showExampleAndActionPart();
				if (typeof window.ajaxHits[ajaxUrl] != "undefined") {
					delete window.ajaxHits[ajaxUrl];
				}
				if (response.succ) {
					// console.log('parsed_body----->',response)
					document.querySelector('.parsedBody').innerHTML = `<h4>EXAMPLE SMS FOR ORDER ID-${response.id} :</h4><p id="parsedBody">${response.data.parsed_body}</p>`;
					window.setTimeout( function(){
						preloaderInlineOff();
					}, 2000 );
				}
			}
		});
	};
	window.ajaxHits = {};
			//this function will append the template body to the textarea
	function getManualTemplateData(formID,formData){
		preloaderInlineOn(formID, "p_info",'Please wait.....');
		if (typeof window.ajaxHits[ajaxUrl] != "undefined") {
			window.ajaxHits[ajaxUrl].abort();
		}
		hideExampleAndActionPart();
		jQuery.ajax({
			url:ajaxUrl,
			type: 'GET',
			data:formData,
			dataType: 'json',
			success: function (response) {
				preloaderInlineOff();
				// preloaderInlineOn(formID, response.succ ? 'p_success' : 'p_danger', response.public_msg);
				if (typeof window.ajaxHits[ajaxUrl] != "undefined") {
					delete window.ajaxHits[ajaxUrl];
				}
				if (response.succ) {
					document.querySelector('#cs_final_sms_body').value = response.data.sms_template;
				}
			}
		});
	};
	//this function will send whatever is in the textarea as sms template in bulk
	function sendBulkSms(formID,formData){
		preloaderInlineOn(formID, "p_info",'Please wait.....');
		jQuery.ajax({
			url:ajaxUrl,
			type: 'POST',
			data:formData,
			dataType: 'json',
			success: function (response) {
				preloaderInlineOn(formID, response.succ ? 'p_success' : 'p_danger', response.public_msg);
				if (response.succ) {
					window.setTimeout( function(){
						window.location = "edit.php?post_type=shop_order";
					}, 10000 );
				}
			}
		});
	};
	function hideExampleAndActionPart()
	{
		document.querySelector('.parsedBody').style.display = 'none';
		document.querySelector('.cs_send_btn').style.display = 'none';
	}
	function showExampleAndActionPart()
	{
		document.querySelector('.parsedBody').style.display = '';
		document.querySelector('.cs_send_btn').style.display = '';
	}
	// jQuery(document).ready(function(){
	// 	if(document.querySelector('.clicksend_ajax_url')){
	// 		ajaxUrl = document.querySelector('.clicksend_ajax_url').value;
	// 		//console.log(ajaxUrl);
	// 	}
	// });
