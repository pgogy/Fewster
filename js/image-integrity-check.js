function fewster_image_integrity(items, orig_length, problems, ajax_action){

	if(items.length!=0){
	
		item = items.shift();
		
		ajax_action = "fewster_image"; 
		
		var data = {
			action : ajax_action,
			fewster_file : jQuery(item).attr("file"),
			nonce : fewster_image_check.nonce
		};
		
		jQuery.post(fewster_image_check.ajaxURL, data, function(response) {
		
			data = JSON.parse(response);
		
			if(data[0]==true){
				jQuery(item).attr("passed",true);
				jQuery("#" + jQuery(item).attr("id") + "_status")
					.html("Image safe")
					.css("color","#0F0");
			}else if(data[0]==false){
				if(data[1]=="No remote file found"){
					jQuery("#" + jQuery(item).attr("id") + "_status")
						.html("Error - " + data[1] + " - <a target='_blank' href='" + jQuery(item).attr("delete_url") + "&no_update=true'>Delete file</a>")
						.css("color","#F00");
	
				}else{
					jQuery("#" + jQuery(item).attr("id") + "_status")
						.html("Error - " + data[1] + " -  <a target='_blank' href='" + jQuery(item).attr("repair_url") + "&no_update=true'>Remote Repair</a> | <a target='_blank' href='" + jQuery(item).attr("diff_url") + "&no_update=true'>See differences</a>")
						.css("color","#F00");
				}
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
			
			fewster_image_integrity(items, orig_length, problems, ajax_action);
			
		});
	}else{	
	
		jQuery("#fewster_importProgress").fadeOut(100);
		jQuery("#fewster_integrity").fadeOut(100);
		jQuery("#fewster_select_options").fadeOut(100);
	
		if(problems==0){
			jQuery(".fewster_notice_good")
				.fadeIn(100);
			msg = "All images ok";
		}else{
			jQuery(".fewster_notice_bad")
				.fadeIn(100);
			msg = problems + " images have issues";
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
					
						jQuery("form#fewster_integrity_form input:checkbox:not(:checked)")
							.each(							
								function(index,value){	
									jQuery(value)
										.parent()
										.css("display","none");
								}
							);
					
						jQuery("form#fewster_integrity_form input:checked")
							.each(							
								function(index,value){	
									items.push(value);									
								}
							);
							
						fewster_image_integrity(items, items.length, 0, "");
					
					}
			);
	}
);