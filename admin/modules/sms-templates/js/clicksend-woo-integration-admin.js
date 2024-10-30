(function( $ ) {
	'use strict';

})( jQuery );

let ajax_url = '';
let gn_confirmed_msg = ''; 

function toggleDisable(button){
	button.children[0].classList.toggle('fa-ban');
	button.children[0].classList.toggle('fa-edit');
	let item = button.closest('.cs_woo__sms-templates--item');

	item.querySelector('.submit-btn').classList.toggle('disabled');

	let input = item.querySelector('.cswt__name-input');
	let textArea = item.querySelector('.cswt__body-input');

	if( textArea.hasAttribute('disabled') ){
		textArea.removeAttribute('disabled');
			
	}else{
		textArea.setAttribute('disabled', 'true');
		
	}
	if( input.hasAttribute('disabled') ){
		input.removeAttribute('disabled');
	}else{
		input.setAttribute('disabled', 'true');
	}

}

function checkWords(textArea){
	let msg = textArea.value;
	let wordCountEle = textArea.previousElementSibling.children[0];
	let wordCount = 0;
	if(msg.replaceAll('\n', '') != ''){
        wordCount = msg.replaceAll('\n', ' ').split('').length;
    }else{
        wordCount = 0;    
    }

    if(wordCount<=1224){
        wordCountEle.innerText = `(${wordCount}/1224)`;
        wordCountEle.style.setProperty('color', 'black');
        gn_confirmed_msg = msg;
    }   
    else{
        msg = textArea.value = gn_confirmed_msg;
        wordCount = msg.trim().replaceAll('\n', ' ').split('').length;
        wordCountEle.innerText = `(${wordCount}/1224)`;
        wordCountEle.style.setProperty('color', 'red');    
        
    }   
}

// function toggleStatus(button){
// 	preload_on('Please Wait', 'p_info');
// 	var postData = [];
// 	postData['action'] = 'clicksend_update_status';
// 	postData['status'] = button.dataset.templateStatus == '1' ? '0': '1';
// 	postData['id'] = button.dataset.templateId;
// 	jQuery.ajax({
//         url: ajax_url,
//         type: 'post',
//         data: Object.assign({}, postData),
//         dataType: 'json',
//         success: function (response) {
//             if (response.succ) {
//             	button.classList.toggle('cs_woo__sms-templates--btn-primary');
//             	button.classList.toggle('cs_woo__sms-templates--btn-warning');
//             	button.dataset.templateStatus = postData['status'] == '1'? '0': '1';
//             	button.children[0].classList.toggle('fa-exclamation-triangle');
//             	button.children[0].classList.toggle('fa-check');

//             	preload_off();
//             	preload_on(response.public_msg, 'p_success');
//     			//console.log(response.public_msg);
//     		}else{
//     			preload_on(response.public_msg, 'p_danger');
//     			console.log(response);
//     		}
//     		window.setTimeout(preload_off, 3000);
//     	},
//     });	
// }

function removeSmsTemplate(button){
	let item = button.closest('.cs_woo__sms-templates--item');
	let isConfirm = true;
	if(item.querySelector('.cswt__body-input').value.trim().replaceAll('\n', '') != '' ){
		isConfirm = confirm('There is some content in template body, do you want to delete this Template?');
	}
	if(!isConfirm){
		return;
	}

	item.remove();
}

function deleteTemplate(button){
	if (confirm('Do you want to delete this Template?') != true){
		return;
	}
	// preload_on('Please Wait', 'p_info');
	preloaderInlineOn('preload-msg', 'p_info', 'Please Wait..');
	var postData = [];
	postData['action'] = 'clicksend_delete_template';
	postData['_nonce'] = postlove.security;
	postData['id'] = button.dataset.templateId;
	jQuery.ajax({
        url: ajax_url,
        type: 'post',
        data: Object.assign({}, postData),
        dataType: 'json',
        success: function (response) {
            preload_off();
            if (response.succ) {
            	
            	//preload_on(response.public_msg, 'p_success');
    			preloaderInlineOn('preload-msg', 'p_success', response.public_msg);
    			getSmsTemplates(true, {'icon': 'p_success', 'msg': response.public_msg});
    		}else{
    			preloaderInlineOn('preload-msg', 'p_info', response.public_msg);
    			// preload_on(response.public_msg, 'p_danger');
    			console.log(response);
    		}
    		///window.setTimeout(preload_off, 3000);
    	},
    });	
}

function createNewItem(isFirstEle){
	if(document.querySelectorAll('.cs_woo__sms-templates--item').length >= 10){
		//preload_on('Cannot Create more than 10 templates', 'p_danger');
		preloaderInlineOn('preload-msg', 'p_danger', 'Cannot create more than 10 templates');
		window.setTimeout(preload_off, 4000);
		return;
	}
	let smsTemplateItem = `<div class="cs_woo__sms-templates--item">
											<input class="template-id-hidden" type="hidden" value="0"></input>
								    		<div class="cs_woo__sms-templates--name">
								    			<label for="cswt__name-input"><span>Label:</span>
								    				<input class="components-text-control__input cswt__name-input" id="cswt__name-input" type="" name="" placeholder="">
								    			</label>	
								    		</div>
								    		<div class="cs_woo__sms-templates--body">
								    			<label for="cswt__body-input">
								    				<span>Template:
								    					<span class="cs_woo__sms-templates--word-count">
								    						
								    					</span>
								    				</span>
								    				<textarea oninput="checkWords(this)" class="components-text-control__input cswt__body-input" id="cswt__body-input" placeholder="(multiline)"></textarea>
								    				<span class="instructions">Max of 1,224 characters. <a href="https://clicksend.helpdocs.io/article/h474eseq3a-how-many-characters-can-i-send-in-an-sms" target="blank">More info</a></span>
								    			</label>
								    		</div>
								    		<div class="cs_woo__sms-templates--actions">
								    			
								    			<div class="cs_woo__sms-templates--actions">
									    			<div class="cs_woo__sms-templates--btn-group">
									    				
									    				<button onclick="removeSmsTemplate(this)" class="submit-btn cs_woo__sms-templates--btn cs_woo__sms-templates--btn-danger"><span class="dashicons dashicons-no"></span></button>
									    			</div>
									    		</div>
								    		</div>
								    	</div>`;
								    	//<span>Actions:</span>
								    	//<button onclick="upsertSmsTemplate(this)" class="submit-btn cs_woo__sms-templates--btn cs_woo__sms-templates--btn-success"><i class="fa fa-arrow-circle-right"></i></button>
	let element = document.querySelector('.cs_woo__sms-templates--section');
	let arr = element.querySelectorAll('.cs_woo__template-ctrl-area');
	if(arr && arr.length >0){
		arr.forEach((btn)=>{
			btn.remove();
		});
				
	}
	
	smsTemplateItem += `<div class="cs_woo__template-ctrl-area">
						<div id="preload-msg" class=""><div class="hidden err_desc"><p></p></div></div>
						<div class="cs_woo__template-ctrl-btns"><button onclick="saveSmsTemplates()" 
						class="add-template-btn cs_woo__sms-templates--btn cs_woo__sms-templates--btn-primary">
						Save Changes</button>
						<button onclick="createNewItem()" 
								class="cs_woo__sms-templates--btn add-template-btn 
								button-secondary">
								<span class="transform-down dashicons dashicons-plus">
								</span>Add More
						</button></div>
						</div>`;
	if(isFirstEle){
		element.innerHTML = smsTemplateItem;
	}else{
		element.innerHTML += smsTemplateItem;
	}

	let width = document.querySelector('.cswt__body-input').offsetWidth;
	document.querySelector('.cs_woo__template-ctrl-area').style.setProperty(`width`, `${width}px`);
}

function saveSmsTemplates(){
	//preload_on('Please Wait', 'p_info');
	preloaderInlineOn('preload-msg', 'p_info', 'Please Wait..');
	var postData = [];
	postData['action'] = 'clicksend_upsert_template';
	postData['_nonce'] = postlove.security;
	let ele = document.querySelectorAll('.cs_woo__sms-templates--item');
	let id ="";
	ele.forEach((item, index)=>{
		id = item.querySelector('.template-id-hidden').value;
		let data = {};
		if(id=='0'){
			data['template_label'] = item.querySelector('.cswt__name-input').value;
			data['template_body'] = item.querySelector('.cswt__body-input').value;
			postData.push(data);
		}else{
			data['id'] = id;
			data['template_label'] = item.querySelector('.cswt__name-input').value;
			data['template_body'] = item.querySelector('.cswt__body-input').value;
			postData.push(data);
		}

		if( data['template_label'].trim().replaceAll('\n', '') == '' ){
			// preload_off();
			// preload_on('Template Name is Required!', 'p_danger');
			// window.setTimeout(preload_off, 2000);
			preloaderInlineOn('preload-msg', 'p_danger', 'Template name required.');
			return;
		}
		if( data['template_label'].trim().replaceAll('\n', '') == '' ){
			// preload_off();
			// preload_on('Template body required.', 'p_danger');
			// window.setTimeout(preload_off, 2000);
			preloaderInlineOn('preload-msg', 'p_danger', 'Template body required.');
			return;
		}

	});

	jQuery.ajax({
        url: ajax_url,
        type: 'post',
        data: Object.assign({}, postData),
        dataType: 'json',
        success: function (response) {
            //preload_off();
            if (response.succ) {
            	
            	//preload_on(response.public_msg, 'p_success');
    			preloaderInlineOn('preload-msg', 'p_success', response.public_msg);
    			getSmsTemplates(true, {'icon': 'p_success', 'msg': response.public_msg});
    			//window.location.reload();
    		}else{
    			preloaderInlineOn('preload-msg', 'p_danger', response.public_msg);
    			//preload_on(response.public_msg, 'p_danger');
    		}
    		//window.setTimeout(preload_off, 3000);
    	},
    });

}

function upsertSmsTemplate(button, isUpdate=false){
	//preload_on('Please Wait', 'p_info');
	preloaderInlineOn('preload-msg', 'p_info', 'Please Wait..');
	let item = button.closest('.cs_woo__sms-templates--item');
	var postData = [];
	postData['action'] = 'clicksend_upsert_template';
	postData['_nonce'] = postlove.security;
	if(isUpdate){
		postData['id'] = item.querySelector('.template-id-hidden').value;
		if( postData['id'] == '' ){
			// preload_off();
			// preload_on('Template id required.', 'p_danger');
			// window.setTimeout(preload_off, 2000);
			preloaderInlineOn('preload-msg', 'p_danger', 'Template id required.');
			return;
		}
	}
	postData['template_label'] = item.querySelector('.cswt__name-input').value;
	postData['template_body'] = item.querySelector('.cswt__body-input').value;
	
	if( postData['template_label'].trim().replaceAll('\n', '') == '' ){
		// preload_off();
		// preload_on('Template Name is Required!', 'p_danger');
		// window.setTimeout(preload_off, 2000);
		preloaderInlineOn('preload-msg', 'p_danger', 'Template name required.');
		return;
	}
	if( postData['template_body'].trim().replaceAll('\n', '') == '' ){
		// preload_off();

		// preload_on('Template body required.', 'p_danger');
		// window.setTimeout(preload_off, 2000);
		preloaderInlineOn('preload-msg', 'p_danger', 'Template body required.');
		return;
	}

	jQuery.ajax({
        url: ajax_url,
        type: 'post',
        data: Object.assign({}, postData),
        dataType: 'json',
        success: function (response) {
            preload_off();
            if (response.succ) {
            	
            	preload_on(response.public_msg, 'p_success');
    			getSmsTemplates();
    		}else{
    			preload_on(response.public_msg, 'p_danger');
    		}
    		//window.setTimeout(preload_off, 3000);
    	},
    });
}

function getSmsTemplates(isUpdate=false, obj={}){
	var postData = [];
	postData['action'] = 'clicksend_get_sms_templates';
	postData['_nonce'] = postlove.security;

	jQuery.ajax({
        url: ajax_url,
        type: 'post',
        data: Object.assign({}, postData),
        dataType: 'json',
        success: function (response) {
            if (response.succ) {
				console.table(response.sms_templates)
    			let sms_templates = response.sms_templates;
    			let smsTemplateItem ='';
    			let temp ='';
    			
    			if(response.sms_templates.length<=0){
            		createNewItem(true);
            		return;
            	}

    			sms_templates.forEach((sms_temp, index)=>{
    	// 			if(sms_temp['status'] == '0'){
					// 	temp = `<button onclick="toggleStatus(this)" data-template-status="${sms_temp['status']}" data-template-id="${sms_temp['id']}" class="cs_woo__sms-templates--btn cs_woo__sms-templates--btn-primary"><i class="fa fa-check"></i></button>`;							
					// }else{
					// 	temp = `<button onclick="toggleStatus(this)" data-template-status="${sms_temp['status']}" data-template-id="${sms_temp['id']}" class="cs_woo__sms-templates--btn cs_woo__sms-templates--btn-warning"><i class="fa fa-exclamation-triangle"></i></button>`;
					// }
    				smsTemplateItem += `<div class="cs_woo__sms-templates--item">
    										<input class="template-id-hidden" type="hidden" value="${sms_temp['id']}"></input>
								    		<div class="cs_woo__sms-templates--name">
								    			<label for="cswt__name-input-${sms_temp['id']}"><span>Label:</span>
								    				<input class="components-text-control__input cswt__name-input" id="cswt__name-input-${sms_temp['id']}" type="" name="" placeholder="" value="${sms_temp['label']}">
								    			</label>	
								    		</div>
								    		<div class="cs_woo__sms-templates--body">
								    			<label for="cswt__body-input-${sms_temp['id']}">
								    				<span class="label-name">Template:
								    					<span class="cs_woo__sms-templates--word-count">
								    						
								    					</span>
								    				</span>
								    				<textarea oninput="checkWords(this)" class="components-text-control__input cswt__body-input" id="cswt__body-input-${sms_temp['id']}" placeholder="(multiline)">${sms_temp['body']}</textarea>
								    				<span class="instructions">Max of 1,224 characters. <a href="https://clicksend.helpdocs.io/article/h474eseq3a-how-many-characters-can-i-send-in-an-sms" target="blank">More info</a></span>
								    			</label>
								    		</div>
								    		<div class="cs_woo__sms-templates--actions">
								    			
								    			<div class="cs_woo__sms-templates--actions">
									    			<div class="cs_woo__sms-templates--btn-group">
									    				
									    				<button onclick="deleteTemplate(this)" data-template-id="${sms_temp['id']}" class="cs_woo__sms-templates--btn cs_woo__sms-templates--btn-danger"><span class="dashicons dashicons-no"></span></button>
									    			</div>
									    		</div>
								    		</div>
								    	</div>`;
    			});  
    			//<span>Actions:</span>
    			//<button onclick="toggleDisable(this)" class="cs_woo__sms-templates--btn cs_woo__sms-templates--btn-info"><i class="fa fa-edit"></i></button>
									    				//<button onclick="upsertSmsTemplate(this, true)" data-template-id="${sms_temp['id']}" class="submit-btn cs_woo__sms-templates--btn cs_woo__sms-templates--btn-success disabled"><i class="fa fa-arrow-circle-right"></i></button>     
    			//let width = document.querySelector('.cswt__body-input').offsetWidth;
    			smsTemplateItem += `<div class="cs_woo__template-ctrl-area">
	    								<div id="preload-msg" class=""><div class="hidden err_desc"><p></p></div></div>
		    							<div class="cs_woo__template-ctrl-btns"><button onclick="saveSmsTemplates()" 
											class="add-template-btn cs_woo__sms-templates--btn cs_woo__sms-templates--btn-primary">
											Save Changes</button>
		    								<button onclick="createNewItem()" class="button-secondary cs_woo__sms-templates--btn add-template-btn"><span class="transform-down transform-down dashicons dashicons-plus">
		    								</span>Add More</button>
	    								</div>
	    							</div>`;
    			document.querySelector('.cs_woo__sms-templates--section').innerHTML = smsTemplateItem;     
    			
    			let width = document.querySelector('.cswt__body-input').offsetWidth;
				document.querySelector('.cs_woo__template-ctrl-area').style.setProperty(`width`, `${width}px`);
            	if(isUpdate){
            		preloaderInlineOn('preload-msg', obj.icon, obj.msg);
            	}
            }else{
				preload_on(response.public_msg, 'p_danger');
			}
        }
    });
}

jQuery(document).ready(function(){
	if(document.querySelector('.cs_woo__sms-templates--ajax-url')){
		ajax_url= document.querySelector('.cs_woo__sms-templates--ajax-url').value;
		getSmsTemplates();	
	}
	
});