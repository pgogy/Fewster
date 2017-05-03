<?PHP

	class fewster_manage_whitelist{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Manage Whitelist"), __("Manage Whitelist"), "manage_options", "fewster-manage-whitelist", array($this, "whitelist") );
		}
		
		function whitelist($file){
			?><h2><?PHP echo __("Whitelistied files"); ?></h2><?PHP
			
			if(isset($_POST['fewster_file'])){
	
				if(wp_verify_nonce($_POST['fewster_repair'],"fewster_repair")){
				
					$whitelist = get_option("fewster_whitelist");
				
					$output = "";
				
					foreach($_POST as $key => $value){
						if($value == "remove"){
							if(($index = array_search(str_replace("_php",".php",$key), $whitelist)) !== false) {
								unset($whitelist[$index]);
								$output .= "<p>" . str_replace("_php",".php",$key) . " " . __("removed from whitelist") . "</p>";
							}
						}
					}
					if($output!=""){
						?><div class="notice error"><?PHP echo $output; ?></div><?PHP
					}
					update_option("fewster_whitelist", $whitelist);
					
				}
	
			}
			?>
			<form action="" method="POST">
				<input type="hidden" name="fewster_file" value="fewster_file" />
				<?PHP
					$whitelist = get_option("fewster_whitelist");
					if($whitelist!=FALSE){
						?><p><?PHP echo __("These files are in the whitelist. Tick the checkbox to remove them"); ?>?</p><?PHP
						foreach($whitelist as $index => $file){
							?><p><input type="checkbox" name="<?PHP echo $file; ?>" value="remove" /><?PHP echo $file . "</p>";
						}	
						?><input type="submit" class="button-primary" value="<?php _e('Remove from Whitelist') ?>" /><?PHP
						echo wp_nonce_field("fewster_repair","fewster_repair"); 
					}else{
						?><p><?PHP echo __("No files have been whitelisted"); ?></p><?PHP
					}
				?>
			</form>
			<?PHP
		}
		
	}
	
	$fewster_manage_whitelist = new fewster_manage_whitelist();