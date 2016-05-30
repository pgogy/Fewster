<?PHP

	class jenner_rescan{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "jenner-anti-bad", "Re-scan", "Rescan the site", "manage_options", "jenner-rescan", array($this, "scan") );
		}	
	
		function scan(){
		
			require_once("jenner_library.php");
			$library = new jenner_library();
		
			$dir = $library->get_config_path();
			$files = array();
			$count = 0;
			$files = $library->recurse($dir, array($this, "check_size"), $files, $count);
			$data = array();

			foreach($files[1] as $file){
				$data[$file[0]] = $file[1];
			}

			file_put_contents(dirname(__FILE__) . "/files.json", serialize($data));	
			
			?><h1>File Updated / Site Re-scanned</h1><p>File updated</p><?PHP

			echo "<p>" . $files[0] . " files have been scanned</p>";

		}
		
		function check_size($file){
			return array($file, filesize($file));
		}
		
	}
	
	$jenner_rescan = new jenner_rescan();
	