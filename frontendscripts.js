function hwcreseller_update_custom_package_slider_totals(currencystart,currencyend) { 
	var total=0;
	total+=parseFloat(jQuery('#hwcreseller_monthlybandwidth_price').val());
	total+=parseFloat(jQuery('#hwcreseller_monthlydisk_price').val());
	total+=parseFloat(jQuery('#hwcreseller_monthlycompute_price').val());
	total+=parseFloat(jQuery('#hwcreseller_monthlywebsite_price').val());
	total+=parseFloat(jQuery('#hwcreseller_monthlydbase_price').val());
	total+=parseFloat(jQuery('#hwcreseller_monthlyemail_price').val());
	jQuery('#hwcreseller_top_total_text').val(currencystart+total.toFixed(2)+currencyend);
	jQuery('#hwcreseller_bottom_total_text').val(currencystart+total.toFixed(2)+currencyend);
}
jQuery(document).ready(function() {
	var currencystart='';
	var currencyend='';
	if (jQuery('#hwcreseller_currencysymbolbefore').val()=='true') { 
		currencystart=jQuery('#hwcreseller_currencysymbol').val();
	} else {
		currencyend=jQuery('#hwcreseller_currencysymbol').val();
	}
	var initialmonthlybwidth=10;
	jQuery('div.hwcreseller_monthlybandwidth_slider').slider({
		range: "min",
		value: initialmonthlybwidth,
		min: 20,
		max: 15000,
		step: 20,
		slide: function(event,ui) { 
			jQuery('input.hwcreseller_monthlybandwidth_text').val(ui.value+" GB for "+currencystart+(parseFloat(jQuery('#hwcreseller_monthlybandwidth').val())*ui.value).toFixed(4)+currencyend);
			jQuery('#hwcreseller_monthlybandwidth_price').val((parseFloat(jQuery('#hwcreseller_monthlybandwidth').val())*ui.value));
			hwcreseller_update_custom_package_slider_totals(currencystart,currencyend);
		}
	});
	jQuery('#hwcreseller_monthlybandwidth_price').val(parseFloat(jQuery('#hwcreseller_monthlybandwidth').val())*initialmonthlybwidth);
	jQuery( 'input.hwcreseller_monthlybandwidth_text' ).val( jQuery( "div.hwcreseller_monthlybandwidth_slider" ).slider( "value" )+ " GB for "+currencystart+(parseFloat(jQuery('#hwcreseller_monthlybandwidth').val())*initialmonthlybwidth).toFixed(4)+currencyend);
	var initialmonthlydisk=10;	
	jQuery('div.hwcreseller_monthlydisk_slider').slider({
		range: "min",
		value: initialmonthlydisk,
		min: 5,
		max: 2000,
		step: 5,
		slide: function(event,ui) { 
			jQuery('input.hwcreseller_monthlydisk_text').val(ui.value+" GB for "+currencystart+(parseFloat(jQuery('#hwcreseller_monthlydisk').val())*ui.value).toFixed(4)+currencyend);
			jQuery('#hwcreseller_monthlydisk_price').val((parseFloat(jQuery('#hwcreseller_monthlydisk').val())*ui.value));
			hwcreseller_update_custom_package_slider_totals(currencystart,currencyend);
		}
	});
	jQuery('#hwcreseller_monthlydisk_price').val(parseFloat(jQuery('#hwcreseller_monthlydisk').val())*initialmonthlydisk);
	jQuery( 'input.hwcreseller_monthlydisk_text' ).val( jQuery( "div.hwcreseller_monthlydisk_slider" ).slider( "value" )+ " GB for "+currencystart+(parseFloat(jQuery('#hwcreseller_monthlydisk').val())*initialmonthlydisk).toFixed(4)+currencyend);
	var initialmonthlyhcu=2000;	
	jQuery('div.hwcreseller_monthlycompute_slider').slider({
		range: "min",
		value: initialmonthlyhcu,
		min: 1000,
		step: 1000,
		max: 320000,
		slide: function(event,ui) { 
			jQuery('input.hwcreseller_monthlycompute_text').val(ui.value+" HCU for "+currencystart+(parseFloat(jQuery('#hwcreseller_monthlycompute').val())*ui.value).toFixed(4)+currencyend );
			jQuery('#hwcreseller_monthlycompute_price').val((parseFloat(jQuery('#hwcreseller_monthlycompute').val())*ui.value));
			hwcreseller_update_custom_package_slider_totals(currencystart,currencyend);
		}
	});
	jQuery('#hwcreseller_monthlycompute_price').val(parseFloat(jQuery('#hwcreseller_monthlycompute').val())*initialmonthlyhcu);
	jQuery( 'input.hwcreseller_monthlycompute_text' ).val( jQuery( "div.hwcreseller_monthlycompute_slider" ).slider( "value" )+ " HCU for "+currencystart+(parseFloat(jQuery('#hwcreseller_monthlycompute').val())*initialmonthlyhcu).toFixed(4)+currencyend );
	var initialmonthlywebsite=10;	
	jQuery('div.hwcreseller_monthlywebsite_slider').slider({
		range: "min",
		value: initialmonthlywebsite,
		min: 1,
		step: 1,
		max: 1000,
		slide: function(event,ui) { 
			jQuery('input.hwcreseller_monthlywebsite_text').val(ui.value+" Websites for "+currencystart+(parseFloat(jQuery('#hwcreseller_monthlywebsite').val())*ui.value).toFixed(4)+currencyend);
			jQuery('#hwcreseller_monthlywebsite_price').val((parseFloat(jQuery('#hwcreseller_monthlywebsite').val())*ui.value));
			hwcreseller_update_custom_package_slider_totals(currencystart,currencyend);
		}
	});
	jQuery('#hwcreseller_monthlywebsite_price').val(parseFloat(jQuery('#hwcreseller_monthlywebsite').val())*initialmonthlywebsite);
	jQuery( 'input.hwcreseller_monthlywebsite_text' ).val( jQuery( "div.hwcreseller_monthlywebsite_slider" ).slider( "value" )+ " Websites for "+currencystart+(parseFloat(jQuery('#hwcreseller_monthlywebsite').val())*initialmonthlywebsite).toFixed(4)+currencyend );
	var initialmonthlydbase=10;	
	jQuery('div.hwcreseller_monthlydbase_slider').slider({
		range: "min",
		value: initialmonthlydbase,
		min: 1,
		step: 1,
		max: 1000,
		slide: function(event,ui) { 
			jQuery('input.hwcreseller_monthlydbase_text').val(ui.value+" Databases for "+currencystart+(parseFloat(jQuery('#hwcreseller_monthlydbase').val())*ui.value).toFixed(4)+currencyend);
			jQuery('#hwcreseller_monthlydbase_price').val((parseFloat(jQuery('#hwcreseller_monthlydbase').val())*ui.value));
			hwcreseller_update_custom_package_slider_totals(currencystart,currencyend);
		}
	});
	jQuery('#hwcreseller_monthlydbase_price').val(parseFloat(jQuery('#hwcreseller_monthlydbase').val())*initialmonthlydbase);
	jQuery( 'input.hwcreseller_monthlydbase_text' ).val( jQuery( "div.hwcreseller_monthlydbase_slider" ).slider( "value" )+ " Databases for "+currencystart+(parseFloat(jQuery('#hwcreseller_monthlydbase').val())*initialmonthlydbase).toFixed(4)+currencyend );
	var initialmonthlyemail=10;	
	jQuery('div.hwcreseller_monthlyemail_slider').slider({
		range: "min",
		value: initialmonthlyemail,
		min: 1,
		step: 1,
		max: 1000,
		slide: function(event,ui) { 
			jQuery('input.hwcreseller_monthlyemail_text').val(ui.value+" Email Accounts for "+currencystart+(parseFloat(jQuery('#hwcreseller_monthlyemail').val())*ui.value).toFixed(4)+currencyend);
			jQuery('#hwcreseller_monthlyemail_price').val((parseFloat(jQuery('#hwcreseller_monthlyemail').val())*ui.value));
			hwcreseller_update_custom_package_slider_totals(currencystart,currencyend);
		}
	});
	jQuery('#hwcreseller_monthlyemail_price').val(parseFloat(jQuery('#hwcreseller_monthlyemail').val())*initialmonthlyemail);
	jQuery( 'input.hwcreseller_monthlyemail_text' ).val( jQuery( "div.hwcreseller_monthlyemail_slider" ).slider( "value" )+ " Email Accounts for "+currencystart+(parseFloat(jQuery('#hwcreseller_monthlyemail').val())*initialmonthlyemail).toFixed(4)+currencyend );
	hwcreseller_update_custom_package_slider_totals(currencystart,currencyend);
	jQuery('#hwcreseller_currency_selector').change(function() { 
		jQuery('#hwcreseller_currency_selector_form').submit();
	});
	jQuery('button.hwcreseller-accounttype-addtobasketbutton').click(function() {
		jQuery.post(String(document.location),{'hwcreseller_action': 'addaccounttobasket', 'hwcreseller_accounttypeid': jQuery(this).attr('data-accounttypeid')}, function (data) { 
			if (data=='Done') { 
				hwc_reseller_update_basket_summary();
			}
		});
	});
	jQuery('input.password').pstrength({'displayMinChar': false, 'minChar': 5, raisePower: 1.7});
	jQuery('input.password').pstrength.changeScore('length',1);
	jQuery('input.confirm_password').after('<div class="password-confirm"><span class="password-confirm-bar" style="background-color: rgb(255,255,255); ">Not Matched</span></div>');
	jQuery('input.password').keyup(hwc_reseller_update_password_confirm);
	jQuery('input.confirm_password').keyup(hwc_reseller_update_password_confirm);
	jQuery('input.username').after('<div class="username-confirm"><span class="username-confirm-bar" style="background-color: rgb(255,255,255);">Invalid</span></div>');
	jQuery('input.username').keyup(hwc_reseller_update_username_confirm);
	if (typeof jQuery('input.username').val() != 'undefined') {
	       if (jQuery('input.username').val().length>0) { 
			hwc_reseller_update_username_confirm();
	       }
	}
	hwcreseller_collector_country_change();
});
var hwc_reseller_update_username_confirm_run=null;
var hwc_reseller_update_username_confirm_effect=null;
function hwc_reseller_update_username_confirm() { 
	if (hwc_reseller_update_username_confirm_run!=null) { 
		hwc_reseller_update_username_confirm_run.abort();
	}
	if (hwc_reseller_update_username_confirm_effect!=null) { 
		hwc_reseller_update_username_confirm_effect.stop(true);
	}
	hwc_reseller_update_username_confirm_effect=jQuery('.username-confirm-bar').animate({'background-color': '#FFFFFF'},100,'linear',function() { hwc_reseller_update_username_confirm_effect=null;});
	jQuery('.username-confirm-bar').html('Checking...');
	hwc_reseller_update_username_confirm_run=jQuery.ajax({type: 'POST', url: document.location, data: {'hwcreseller_action': 'checkusername', 'hwcreseller_username': jQuery('input.username').val()}, success: function(data,st,x) { 
		if (data=='true') { 
			hwc_reseller_update_username_confirm_effect=jQuery('.username-confirm-bar').animate({'background-color': '#33FF33'},150,'linear',function() { hwc_reseller_update_username_confirm_effect=null;});
			jQuery('.username-confirm-bar').html('Available');

		} else {
			hwc_reseller_update_username_confirm_effect=jQuery('.username-confirm-bar').animate({'background-color': '#FF3333'},150,'linear',function() { hwc_reseller_update_username_confirm_effect=null;});
			jQuery('.username-confirm-bar').html('Unavailable');

		}
		hwc_reseller_update_username_confirm_run=null;
	}});
}
var hwc_reseller_update_password_confirm_run=null;
function hwc_reseller_update_password_confirm() { 
	if (hwc_reseller_update_password_confirm_run!=null) { 
		hwc_reseller_update_password_confirm_run.stop(true);
	}
	if (jQuery('input.password').val()==jQuery('input.confirm_password').val() && jQuery('input.password').val().length>0) { 
		jQuery('.password-confirm-bar').animate({'background-color': '#33FF33'},200,'linear',function() { 
			hwc_reseller_update_password_confirm_run=null;	
			jQuery('.password-confirm-bar').html('Matched');
		});
	} else {
		jQuery('.password-confirm-bar').animate({'background-color': '#FF3333'},200,'linear',function() { 
			hwc_reseller_update_password_confirm_run=null;	
			jQuery('.password-confirm-bar').html('Not Matched');
		});
	}
}
function hwc_reseller_update_basket_summary() { 
	jQuery.post(String(document.location),{'hwcreseller_action':'updatebasketsummary'}, function (data) { 
		jQuery('#hwcreseller-basket-summary-widget').html(data);
	});
}
function checkDomain(dom, tld) {
	jQuery.post(url_endpoint,{action: 'check_domain', domain:dom, tld:tld}, function(data) {
	if(data[1]) {
	    // Domain is available
		if(default_checked[tld]) {
			checked = 'checked="checked"'; 
		} else { 
			checked = '';
		}
	    yearlist = '<select name="yearlist['+dom+']">';
		for(i=min_periods[tld]; i<=max_periods[tld]; i++) {
			if (i==1) { 
				yearlist += '<option value="'+i+'">'+i+' year</option>';
			} else {
				yearlist += '<option value="'+i+'">'+i+' years</option>';
			}
	    }
	    yearlist += '</select>';
	    markup = '<input type=checkbox name="domains['+dom+']" class="checkbox" value="checked" '+checked+' id="checkbox_'+dom+'"> <label for="checkbox_'+dom+'"><span style="color:green; font-size:12px;"><b>'+dom+': </label><div style="float:right; margin-right:10px;">'+yearlist+'</div></b></span>';
	} else {
	    // Domain is taken
	    markup = '<input type=checkbox name="domains['+dom+']" class="checkbox" value="checked" id="checkbox_'+dom+'"> <label for="checkbox_'+dom+'"><span style="color:#f23000; font-size:12px;"><b>'+dom+': transfer-in</b></span></label>';
	}
	jQuery(document.getElementById('domain_'+dom)).html('<div style="height:30px;">'+markup+'</div>'); // Weird, huh?
    }, 'json');
}
var hwcreseller_vat_number_country_prevstate_visible=false;
var hwcreseller_us_state_country_prevstate_visible=false;
function hwcreseller_collector_country_change() {
       if (!jQuery('#el_country').length) {
		return;
       }	       
       var newusstate;
       if (jQuery('#el_country').val()=='USA') { 
		newusstate=true;
       } else {
		newusstate=false;
       }
	var newstate;
	if (jQuery.inArray(jQuery('#el_country').val(),eucountries)>-1) { 
		newstate=true;
	} else {
		newstate=false;
	}
	if (newstate!=hwcreseller_vat_number_country_prevstate_visible) { 
		hwcreseller_vat_number_country_prevstate_visible=newstate;
		if (newstate) { 
			jQuery('#row_el_vatnumber').stop(true,true);
			jQuery('#row_el_vatnumber').slideDown('slow');
		} else {
			jQuery('#row_el_vatnumber').stop(true,true);
			jQuery('#row_el_vatnumber').slideUp('slow');
		}
	}
	if (newusstate!=hwcreseller_us_state_country_prevstate_visible) { 
		hwcreseller_us_state_country_prevstate_visible=newusstate;
		if (newusstate) { 
			if (jQuery('#el_county').val().length==2) { 
				jQuery('#el_state').val(jQuery('#el_county').val());
			} else {
				jQuery('#el_state').find('option').each(function(i,o) { 
					if (jQuery(o).html().toLowerCase()==jQuery('#el_county').val().toLowerCase()) { 
						jQuery('#el_state').val(jQuery(o).val());
					}
				});
			}
			jQuery('#row_el_state').stop(true,true);
			jQuery('#row_el_state').slideDown('slow');
			jQuery('#row_el_county').stop(true,true);
			jQuery('#row_el_county').slideUp('slow');
		} else {
			jQuery('#row_el_state').stop(true,true);
			jQuery('#row_el_state').slideUp('slow');
			jQuery('#row_el_county').stop(true,true);
			jQuery('#row_el_county').slideDown('slow');
		}
	}
}
function hwcreseller_setup_collectors() { 
	jQuery('#el_country').change(hwcreseller_collector_country_change);
	jQuery('#el_country').keyup(hwcreseller_collector_country_change);
}
function hwcreseller_setup_us_state_collector() { 
}
