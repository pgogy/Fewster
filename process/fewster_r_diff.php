<?PHP

	class fewster_remote_diff{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Remote Difference"), __("Remote Difference"), "manage_options", "fewster-r-diff", array($this, "diff") );
		}
		
		function diff(){
			require_once(dirname(__FILE__) . "/../library/fewster_diff_library.php");
			$library = new fewster_diff_library();
			$library->remote_diff($_GET['file']);
		}
	
	}
	
	$fewster_remote_diff = new fewster_remote_diff();