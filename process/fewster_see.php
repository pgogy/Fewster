<?PHP

	class fewster_see{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("See"), __("See"), "manage_options", "fewster-see", array($this, "see") );
		}
		
		function see($file){
			?><h2><?PHP echo __("Content of"); ?> <?PHP echo $_GET['file']; ?></h2><?PHP
			echo "<pre>" . htmlspecialchars(file_get_contents($_GET['file'])) . "</pre>";
		}
	
	}
	
	$fewster_see = new fewster_see();