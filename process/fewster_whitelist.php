<?PHP

	class fewster_whitelist{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Add"), __("Add"), "manage_options", "fewster-whitelist", array($this, "whitelist") );
		}
		
		function whitelist($file){
			?><h2><?PHP echo __("Whitelisting"); ?> <?PHP echo $_GET['file']; ?></h2><?PHP
			if(!isset($_POST['fewster_file'])){
				
				$whitelisted = false;

				$whitelist = get_option("fewster_whitelist");

				if(is_array($whitelist)){
	
					if(in_array($_GET['file'], $whitelist)){

						$whitelisted = true;

					}
		
				}
				if(!$whitelisted){
				?>
					<p><?PHP echo __("Are you certain you wish to whitelist") . " " . $_GET['file']; ?>?</p>
					<form action="" method="POST"> 
						<input type="hidden" name="fewster_file" value="<?PHP echo $_GET['file']; ?>" />
						<input type="submit" class="button-primary" value="<?php _e('Whitelist') ?>" />
						<?PHP echo wp_nonce_field("fewster_repair","fewster_repair"); ?>
					</form>
					<?PHP
				}else{
					?><p><?PHP echo __("This file is already whitelisted"); ?></p><?PHP
				}
			}else{
				if(wp_verify_nonce($_POST['fewster_repair'],"fewster_repair")){
				
					$whitelist = get_option("fewster_whitelist");
					
					if(!is_array($whitelist)){
						$whitelist = array();
					}
				
					array_push($whitelist, $_POST['fewster_file']); 
				
					update_option("fewster_whitelist", $whitelist);
					
					echo $_POST['fewster_file'] . " " . __("has been whitelisted");
					
				}
			}
		}
		
	}
	
	$fewster_whitelist = new fewster_whitelist();