<?PHP

	class jenner_scan_core{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "jenner-anti-bad", "Scan", "Scan Core", "manage_options", "jenner-scan-core", array($this, "scan") );
		}
		
		function scan(){

			?><h1>Scanning Core Files</h1><?PHP

			require_once("jenner_library.php");
			$library = new jenner_library();

			$this->dir = $library->get_config_path();
			$files = array();
			
			$count = 0;
			$files = $library->recurse($this->dir, array($this, "check_size"), $files, $count);
			$data = array();
				
			foreach($files[1] as $file){
				$data[$file[0]] = $file[1];
			}
				
			echo "<p>" . $files[0] . " files have been scanned</p>";

		}
		
		function check_core_size($file){
			echo $this->dir;
			return array($file, filesize($file));
		}
	
	}
	
	$jenner_scan_core = new jenner_scan_core();