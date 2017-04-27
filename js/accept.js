function fewster_accept_scan(){
	
	var data = {
		action : "fewster_accept_scan",
		nonce : fewster_accept.nonce
	};
		
	jQuery.post(fewster_accept.ajaxURL, data, function(response) {
			alert(response);
		}
	);
}