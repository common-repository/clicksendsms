function preloaderInlineOn(formID, msg_type_cls, msg) {
    preloaderInlineOff();
    msg = typeof msg != "undefined" ? msg : "Processing ...";
    msg_type_cls = typeof msg_type_cls != "undefined" ? msg_type_cls : "p_info";
    if (msg_type_cls == "p_success") {
        msg = "<span class='fa fa-check-circle'></span> " + msg;
    } else if (msg_type_cls == "p_danger") {
        msg = "<span class='fa fa-exclamation-circle'></span> " + msg;
    } else {
        msg = "<span class='fas fa-sync-alt gly-spin'></span> " + msg;
    } 
    jQuery("#" + formID)
        .find(".err_desc")
        .removeClass("hidden p_info p_danger p_success")
        .addClass(msg_type_cls)
        .find("p")
        .html(msg);
    jQuery("#" + formID)
        .find(".err_desc")
        .show();
    if (msg_type_cls === "p_success") {
        window.setTimeout(function () {
            preloaderInlineOff();
        }, 15000);
    }
}

function preloaderInlineOff() {
    jQuery(".err_desc").addClass("hidden").find("p").html("");
}

function getAllGetParams() {
    if (window.location.href.indexOf('?') !== -1) {

        let $getItems = window.location.href.split('?')[1].split('&');
        let $getParams = {};

        for (let i = 0; i < $getItems.length; i++) {
            let gp = $getItems[i].split('=');
            if (typeof gp[0] != "undefined" && typeof gp[1] != "undefined" && gp[0] != '' && gp[1] != '') {
                $getParams[gp[0]] = gp[1];
            }
        }

        return $getParams;

    }
    return {};
}

function setCookie(c_name, value) {
    document.cookie = c_name + "=" + escape(value) + "; expires=" + 60 * 60 * 24 * 30 + '; path=/';
}

function cleanForm($formID){
    jQuery("#"+$formID).find('.help-block').remove();
    jQuery("#"+$formID).find('.err_desc').removeClass('p_danger, p_success, p_info').addClass('hidden');
    jQuery("#"+$formID).find('.err_desc > p').html('');
}

jQuery("form").on('reset',function(){
    jQuery(this).find('.help-block').remove();
    jQuery(this).find('.err_desc').removeClass('p_danger, p_success, p_info').addClass('hidden');
    jQuery(this).find('.err_desc > p').html('');
});

window.ajaxHits = {};

function runAjaxJson(containerID, url, jsonData, method, eventName, callBackFun) {
 
    if (jsonData == 1) {
        jsonData = jQuery("#" + containerID).serialize();
    }
    containerID = typeof containerID != "undefined" ? containerID : '';
    url = typeof url != "undefined" ? url : '';
    jsonData = typeof jsonData != "undefined" ? jsonData : {};
    method = typeof method != "undefined" ? method : 'post';

    // containerID is not blank
    if (containerID != '') {
        cleanForm(containerID);
        preloaderInlineOn(containerID, "p_info", 'please wait ...');
    }

    $headers = {
        // 'Content-Type': 'application/json',
        'X-CSRF-TOKEN': jQuery('meta[name="csrf-token"]').attr('content')
    };
    let userToken = localStorage.getItem('userToken');

    if (userToken != "undefined") {
        $headers['Authorization'] = 'Bearer ' + userToken;
    }
    if (typeof window.ajaxHits[url] != "undefined") {
        window.ajaxHits[url].abort();
    }
    window.ajaxHits[url] = jQuery.ajax({
        url: url,
        method: method,
        dataType: 'json',
        headers: $headers,
        data: jsonData,
        complete: function (xhr, textStatus) {
            // console.log('textStatus',xhr.status);
            // when status is not 200 success
            if (xhr.status != 200) {
                if (typeof callBackFun != "undefined") {
                    let ErrorRes = {};
                    ErrorRes['succ'] = false;
                    ErrorRes['xhrstatus'] = xhr.status;
                    callBackFun({
                        res: ErrorRes
                    });
                }
            }
        },
        success: function (response) {
            // console.log('succ');
            response['xhrstatus'] = 200;
            // 200
            if (typeof eventName != 'undefined') {
                if (DEBUG_MODE) {
                    console.log('#' + containerID + ' trigger with eventName ' + eventName);
                }

                jQuery(containerID == '' ? 'body' : '#' + containerID).trigger(eventName, {
                    url: url,
                    jsonData: jsonData,
                    res: response
                });
            }

            if (typeof window.ajaxHits[url] != "undefined") {
                delete window.ajaxHits[url];
            }

            if (typeof callBackFun != "undefined") {
                callBackFun({
                    url: url,
                    jsonData: jsonData,
                    res: response
                });
            }

            if (containerID != '') {
                preloaderInlineOn(
                    containerID,
                    response.succ ? "p_success" : "p_danger",
                    response.public_msg
                );

                if (response.succ) {
                    jQuery('#' + containerID).find('.hide-after-succ').hide();
                }
            }

            if (response.succ == false && containerID != '') {
                jQuery.each(response.errs, function (fields, errArr) {
                    jQuery("#" + containerID).find("[name=" + fields + "]").parent().closest('.form-group').append('<span class="help-block">' + errArr.join(',') + '</span>');
                });
            }
        }
    });
}
function saveData(formID, msg, resetRequired, callBackFun) {

    if (typeof ids != "undefined") {
//        console.log("ids are ", ids);
        jQuery(ids).each(function (index, id) {
//            console.log(id);
            CKEDITOR.instances[id].updateElement();
        });
    }

//    console.log("form id called ", formID + callBackFun);
    resetRequired = typeof resetRequired == "undefined" ? true : resetRequired;
    var form = jQuery("#" + formID);
//    console.log("action url ", form.attr("action"));
    form.find("input[type=submit], button[type=submit]").addClass("hidden");
    // if (!form.data('bootstrapValidator').validate().isValid()) {
    //     form.find("input[type=submit], button[type=submit]").removeClass("hidden");
    //     preloaderInlineOff();
    //     return false;
    // }
    preloaderInlineOn(formID, "p_info", msg);
    jQuery.ajax({
        type: "post",
        url: form.attr("action"),
        data: new FormData(jQuery('#' + formID)[0]),
        processData: false,
        contentType: false,
        dataType: "json",
        success: function (data, textStatus, jqXHR) {
            preloaderInlineOn(formID, data.succ ? 'p_success' : 'p_danger', data.public_msg);
            form.find("input[type=submit], button[type=submit]").removeClass("hidden");
            // form.data('bootstrapValidator').resetForm();
            if (data.succ) {
//                console.log(data);
                if (resetRequired) {
                    document.getElementById(formID).reset();
                    form.find('input:text, input:password, input:file, select, textarea, input:hidden').val('');
                }

            }
            if (data.succ == false && formID != '') {
                // error showing elments errEls removing older errors show
                const errEls = document.getElementById(formID).querySelectorAll('.help-block');
                errEls.forEach(el=>{
                    el.remove();
                })
                jQuery.each(data.errs, function (fields, errArr) {
                    jQuery("#" + formID).find("[name=" + fields + "]").parent().closest('.form-group').append('<span class="help-block">' + errArr.join(',') + '</span>');
                });
            }
//            console.log(typeof callBackFun);
            if (typeof callBackFun != 'undefined') {
                callBackFun(data);
            }
//            window.setTimeout(function () {
//                preload_off();
//            }, data.succ?1000:5000);
        }
    });
}
function preload_on(msg, msg_type_cls, hide_cta) {
    if (typeof hide_cta != "undefined") {
        jQuery('.hide_while_preload').hide();
    }
    // preload_off();
    msg = typeof msg != "undefined" ? msg : "Processing ...";
    msg_type_cls = typeof msg_type_cls != "undefined" ? msg_type_cls : "p_info";
    if (msg_type_cls == "p_success") {
        msg = "<span class='fa fa-check-circle'></span> " + msg;
    } else if (msg_type_cls == "p_danger") {
        msg = "<span class='fa fa-exclamation-circle'></span> " + msg;
    } else {
        msg = "<span class='fas fa-sync-alt gly-spin'></span> " + msg;
    }
    var gap = 20;
    var total = 0;
    jQuery(".preloader").each(function () {
        gap = gap + jQuery(this).height() + 28 + 10;
    });

    var $div = jQuery("<div>", {class: "preloader " + msg_type_cls})
        .html(msg + '<span class="fas fa-times pull-right"></span></div>')
      
        .click(function () {
            jQuery(this).remove();
        });

    jQuery("body").append($div);

    $div.css({bottom: gap + "px"});
    $div.addClass("animate__animated animate__backInUp");
}

function preload_off() {
    jQuery('.hide_while_preload').show();
    jQuery(".preloader").remove();
}




