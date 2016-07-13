<?PHP

	class fewster_diff{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Difference"), __("Difference"), "manage_options", "fewster-diff", array($this, "diff") );
		}
		
		function diff(){
			require_once(dirname(__FILE__) . "/../library/fewster_diff_library.php");
			$library = new fewster_diff_library();
			$library->diff($_GET['file']);
		}
	
	}
	
	$fewster_diff = new fewster_diff();