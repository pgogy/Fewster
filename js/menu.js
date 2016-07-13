jQuery(document).ready(
	function(){
	
		jQuery("#toplevel_page_fewster-anti-bad li a")
			.each(
					function(index,value){
						if(jQuery(value).html()==""){
							jQuery(value).parent().remove();
						}
					}
			);
	}
);