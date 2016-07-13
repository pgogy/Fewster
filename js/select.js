function fewster_select_all(){
	
	jQuery("input")
		.each(							
			function(index,value){	
				jQuery(value).prop('checked', true);								
			}
		);
	
}

function fewster_unselect_all(){
	
	jQuery("input")
		.each(							
			function(index,value){	
				jQuery(value).prop('checked', false);									
			}
		);
	
}
