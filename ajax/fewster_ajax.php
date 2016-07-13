<?PHP

	class fewster_ajax{
	
		function __construct(){
			add_action("wp_ajax_fewster_direct_core", array($this, "core"));
			add_action("wp_ajax_fewster_direct_addons", array($this, "addons"));
			add_action("wp_ajax_fewster_integrity_done", array($this, "done"));
		}
		
		function done(){
			if(wp_verify_nonce($_POST['nonce'],"fewster_integrity")){
				print_r($_POST);
				if($_POST['old_action']=="fewster_direct_addons"){
					update_option("fewster_plugin_integrity",true);
				}
				if($_POST['old_action']=="fewster_direct_core"){
					update_option("fewster_core_integrity",true);
				}
			}
			die();
		}
	
		function core(){
			if(wp_verify_nonce($_POST['nonce'],"fewster_integrity")){
				require_once(dirname(__FILE__) . "/../library/fewster_remote_library.php");
				$remote_library = new fewster_remote_library;
				
				$remote_file = $remote_library->get_core();
					
				if($remote_file){
					
					$local_file = file_get_contents($_POST['fewster_file']);
					
					if(strlen($local_file)!=strlen($remote_file)){
						echo false;
					}else{
						for($x=(strlen($local_file)-1);$x!=0;$x--){
							if($remote_file[$x]!=$local_file[$x]){
								echo false;
							}
						}
						echo true;
					}
					
				}
			}
			die();
		}
	
		function addons(){
			if(wp_verify_nonce($_POST['nonce'],"fewster_integrity")){
				require_once(dirname(__FILE__) . "/../library/fewster_remote_library.php");
				$remote_library = new fewster_remote_library;
				
				if(strpos($_POST['fewster_file'],"wp-content/plugins")!==FALSE){
					$file = $remote_library->get_plugin();
					if($file){
						$remote_file = $file[1];
					}else{
						echo 2;
						die();
					}
				}else if(strpos($_POST['fewster_file'],"wp-content/themes")!==FALSE){
					$file = $remote_library->get_theme();
					if($file){
						$remote_file = $file[1];
					}else{
						echo 2;
						die();
					}
				}else{
					$remote_file = $remote_library->get_core();
				}
		
				if($remote_file){
					
					$local_file = file_get_contents($_POST['fewster_file']);
					
					if(strlen($local_file)!=strlen($remote_file)){
						echo false;
					}else{
						for($x=(strlen($local_file)-1);$x!=0;$x--){
							if($remote_file[$x]!=$local_file[$x]){
								echo false;
							}
						}
						echo true;
					}
					
				}else{
					print_r($file);
				}
			}
			die();
		}

	}
	
	$fewster_ajax = new fewster_ajax();