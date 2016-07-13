function fewster_integrity(items, orig_length, problems, ajax_action){
	
	if(items.length!=0){
	
		item = items.shift();
		
		if(jQuery(item).attr("file").indexOf("wp-content")==-1){
			ajax_action = "fewster_direct_core"; 
		}else{
			ajax_action = "fewster_direct_addons"; 
		}
		
		var data = {
			action : ajax_action,
			fewster_file : jQuery(item).attr("file"),
			nonce : fewster_select.nonce
		};
		
		jQuery.post(fewster_select.ajaxURL, data, function(response) {
		
			if(response==1){
				jQuery(item).attr("passed",true);
				jQuery("#" + jQuery(item).attr("id") + "_status")
					.html("Verified")
					.css("color","#0F0");
			}else if(response==0){
				jQuery("#" + jQuery(item).attr("id") + "_status")
					.html("Error - <a target='_blank' href='" + jQuery(item).attr("repair_url") + "&no_update=true'>Remote Repair</a>")
					.css("color","#F00");
					problems++;
			}else if(response==2){
				jQuery("#" + jQuery(item).attr("id") + "_status")
					.html("Error - Remote File not found")
					.css("color","#F00");
					problems++;
			}
		
			width = jQuery("#fewster_importProgress")
						.width();
						
			width = width - 10;
						
			progress = (orig_length - items.length) * (width / orig_length);

			jQuery("#importTotal")
				.html((orig_length - items.length) + " / " + orig_length);

			jQuery("#importProgressBar")
				.animate({width:progress+"px"}, 400);
				
			percentage = (100-((items.length/orig_length) * 100)).toString();
			percentage = percentage.split(".");

			jQuery("#importProgressBar")
				.html(percentage[0] + "%");
			
			fewster_integrity(items, orig_length, problems, ajax_action);
			
		});
	}else{	
	
		if(problems==0){
	
			var data = {
				action : "fewster_integrity_done",
				old_action : ajax_action,
				nonce : fewster_select.nonce
			};
			
			jQuery.post(fewster_select.ajaxURL, data, function(response) {
				}
			);
			
		}
	
		jQuery("#fewster_importProgress").fadeOut(100);
		jQuery("#fewster_integrity").fadeOut(100);
		jQuery("#fewster_select_options").fadeOut(100);
	
		if(problems==1){
			jQuery(".fewster_notice_bad")
				.fadeIn(100);
			msg = "Core Integrity Check Complete\n 1 problem found";
		}else{
			if(problems==0){
				jQuery(".fewster_notice_good")
					.fadeIn(100);
				jQuery("#fewster_integrity_form").fadeOut(100);
				msg = "Core Integrity Check Complete\n " + problems + " problems found\n\n";
			}else{
				jQuery(".fewster_notice_bad")
					.fadeIn(100);
				msg = "Core Integrity Check Complete\n " + problems + " problems found\n\nRepair this file before running a scan";
			}
		}
		alert(msg);
		jQuery("#fewster_select")
			.fadeIn(100);
		jQuery("form#fewster_integrity_form input:checked")
			.each(							
				function(index,value){
					if(jQuery(value).attr("passed")=="true"){
						jQuery(value)
							.parent()
							.fadeOut(100);
					}
				}
			);
	}
}

jQuery(document).ready(
	function(){
	
		jQuery("form#fewster_integrity_form #fewster_integrity")
			.on("click", 
					function(){
					
						items = Array();
						
						jQuery("#fewster_importProgress")
							.slideDown(500);
							
						jQuery("#importProgressBar")
							.animate({width:"40px"}, 400);
							
						jQuery("#importProgressBar")
							.html("0%");	
					
						jQuery("form#fewster_integrity_form input:checked")
							.each(							
								function(index,value){	
									items.push(value);									
								}
							);
							
						fewster_integrity(items, items.length, 0, "");
					
					}
			);
	}
);