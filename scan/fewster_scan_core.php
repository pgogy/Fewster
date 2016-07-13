<?PHP

	class fewster_scan_core{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", "Scan", "Scan Core", "manage_options", "fewster-scan-core", array($this, "scan") );
		}
		
		function scan(){

			?><h1>Scanning Core Files</h1><?PHP

			require_once("fewster_library.php");
			$library = new fewster_library();

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
	
	$fewster_scan_core = new fewster_scan_core();