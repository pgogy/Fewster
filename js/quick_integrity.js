function fewster_quick_integrity_data(type, name, file){

	var data = {
		action : "fewster_quick_integrity_data",
		fewster_file : file,
		nonce : fewster_quick_integrity.nonce
	};
	
	jQuery("#fewster_integrity_update_" + name)
		.css("display","block");
		
	jQuery.post(fewster_quick_integrity.ajaxURL, data, function(response) {
	
			return_data = JSON.parse(response);
			counter = 0;
			files = 0;
			for(url in return_data){
				var data = {
					action : "fewster_quick_integrity_file_" + type,
					fewster_file : return_data[url],
					nonce : fewster_quick_integrity.nonce
				};
				jQuery.post(fewster_quick_integrity.ajaxURL, data, function(response) {
						console.log(response);
						jQuery("#fewster_integrity_update_" + name)
							.children()
							.last()
							.html((files + 1) + " of " + return_data.length + " files have been checked");
						files++;
						if(response == 1){
							counter++;
						}
						if(files == return_data.length){
							if(counter==return_data.length){
								jQuery("#fewster_integrity_update_" + name)
									.css("display","none");
								jQuery("#fewster_integrity_success_" + name)
									.css("display","block");
							}else{
								jQuery("#fewster_integrity_fail_" + name)
									.css("display","block");
							}
						}
					}
				);				
			}
			
		}
	);
	
}