<?PHP

	class fewster_scan{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Scan"), __("Scan the site"), "manage_options", "fewster-scan", array($this, "scan") );
		}
		
		function scan(){
			require_once(dirname(__FILE__) ."/../library/fewster_scan_library.php");
			$library = new fewster_scan_library();
			$library->scan();
			update_option("fewster_last_scan", time());
			if($library->counter!=0){
				?><form action="<?PHP echo admin_url("admin.php?page=fewster-update-all"); ?>" method="POST">
				<input type="submit" class="button-primary" value="<?php _e('Update file information and accept above changes') ?>" />
				</form><?PHP
			}
		}
	
	}
	
	$fewster_scan = new fewster_scan();