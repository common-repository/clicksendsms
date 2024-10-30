var ajaxUrl ='';
const months = ["January","February","March","April","May","June","July","August","September","October","November","December"];
function getLogs(refresh=false){

		var postData = [];
		postData['action'] = 'clicksend_get_logs';
		postData['order_id'] = document.querySelector('.clicksend_order_id').value;
		postData['_nonce'] = postlove.security;

		if(refresh){
			postData['action'] = 'clicksend_refresh_logs';
		}

		
		jQuery.ajax({
            url: ajaxUrl,
            type: 'post',
            data: Object.assign({}, postData),
            dataType: 'json',
            success: function (response) {
                if (response.succ) {
                	console.log(response);	

                	let template = ``;
                	if(!response.logs || response.logs.length <=0){
                		let ele = document.querySelector('.clicksend__logs-section');
                		ele.innerHTML = '<div class="clicksend_no-content-text">No Logs Found</div>';	
                		ele.style.minHeight = "50px";
                		ele.style.height = "50px";
                		 
                		return;
                	}
					response.logs.forEach((log, index)=>{


						//START TIME
						let d = new Date(log['created_at']);
						let hours = 0;
						let ampm = 'am';
						if(d.getHours() > 12){
							hours = d.getHours() - 12;
							ampm = 'pm';
						}if( d.getHours() ==  0){
							hours = 12;
							ampm = 'am';
						}if(d.getHours() == 12){
							hours = d.getHours();
							ampm = 'pm';
						}if(d.getHours() < 12){
							hours = d.getHours();
							ampm = 'am';
						}

						let day = d.getDate().toLocaleString('en-US', {
											    minimumIntegerDigits: 2,
											    useGrouping: false
											  });
						hours = hours.toLocaleString('en-US', {
											    minimumIntegerDigits: 2,
											    useGrouping: false
											  });
						let mins = d.getMinutes().toLocaleString('en-US', {
											    minimumIntegerDigits: 2,
											    useGrouping: false
											  });


						let day_date = `${day} ${months[d.getMonth()]} ${d.getFullYear()}`;
						let time = `${hours}:${mins}${ampm}`;
						//END TIME
						if(log['remarks'] != "SUCCESS"){
							template += `<div class="clicksend__logs-item log_error">
										<div class="clicksend__log-msg">
											${log['sms']}
										</div>
										<hr>
										<div class="clicksend__log-date">
											${day_date} at ${time}			
										</div>
										<div class="clicksend__log-remarks">
											Status: Failed - ${log['remarks']}			
										</div>
									</div>`;
						}else{
							template += `<div class="clicksend__logs-item">
										<div class="clicksend__log-msg">
											${log['sms']}
										</div>
										<hr>
										<div class="clicksend__log-date">
											${day_date} at ${time}			
										</div>
									</div>`;
						}	
						
							
					});
                	
					document.querySelector('.clicksend__logs-section').innerHTML = template;

                } else {
                    
                }
            },
            complete: function (){
            	
            }
        });
	}

	//this function will append the template body to the textarea
function getTemplateData(formID,formData){
	preloaderInlineOn(formID, "p_info",'Please wait.....');
	jQuery.ajax({
		url:ajaxUrl,
		type: 'GET',
		data:formData,
		dataType: 'json',
		success: function (response) {
			preloaderInlineOff();
			// preloaderInlineOn(formID, response.succ ? 'p_success' : 'p_danger', response.public_msg);
			if (response.succ) {
				document.querySelector('#cs_final_sms_body').value = response.data.sms_body;
			}
		}
	});
};
	//this function will send whatever is in the textarea as sms
	function sendSms(formID,formData){
		preloaderInlineOn(formID, "p_info",'Please wait.....');
		jQuery.ajax({
			url:ajaxUrl,
			type: 'GET',
			data:formData,
			dataType: 'json',
			success: function (response) {
				preloaderInlineOn(formID, response.succ ? 'p_success' : 'p_danger', response.public_msg);
				if (response.succ) {
					getLogs();
				}
			}
		});
	};

	function toggleInfo() {
		var x = document.getElementById("cs_sms_info");
		if (x.offsetWidth == 0 && x.offsetHeight == 0) {
			x.style.display = 'block';
		  } else {
			x.style.display = 'none';
		  }
	  }

jQuery(document).ready(function(){
	if(document.querySelector('.clicksend_ajax_url')){
		ajaxUrl = document.querySelector('.clicksend_ajax_url').value;
		//console.log(ajaxUrl);
		getLogs();
	}
});
