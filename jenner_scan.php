<?PHP

	class jenner_scan{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "jenner-anti-bad", "Scan", "Scan the site", "manage_options", "jenner-scan", array($this, "scan") );
		}
		
		function scan(){

			?><h1>Scanning for size</h1><?PHP

			require_once("jenner_library.php");
			$library = new jenner_library();

			$dir = $library->get_config_path();
			$files = array();
			
			if(!file_exists(dirname(__FILE__) . "/files.json")){

				$count = 0;
				$files = $library->recurse($dir, array($this, "check_size"), $files, $count);
				$data = array();
				
				foreach($files[1] as $file){
					$data[$file[0]] = $file[1];
				}
				
				file_put_contents(dirname(__FILE__) . "/files.json", serialize($data));
				
				echo "<p>" . $files[0] . " files have been scanned</p>";

				?>File created - when you return to this page it will check for changes<?PHP
				
			}else{
				
				$count = 0;
				$files = $library->recurse($dir, array($this, "check_size"), $files, $count);
				$data = array();
				
				foreach($files[1] as $file){
					$data[$file[0]] = $file[1];
				}
				
				$old_data = unserialize(file_get_contents(dirname(__FILE__) . "/files.json"));
				$output = array();
				$output['new'] = array();
				$output['changed'] = array();
				foreach($data as $key => $value){
					if(!isset($old_data[$key])){
						$output['new'][$key] = date("Y-m-d H:i:s", filemtime($key));
					}else{
						if($old_data[$key]!=$data[$key]){
							$output['changed'][$key] = date("Y-m-d H:i:s", filemtime($key));
						}
					}
				}
				
				echo "<p>" . $files[0] . " files have been scanned</p>";

				if(count($output['new'])==0 && count($output['old'])==0){
					echo "<p>No files have changed or been added</p>";
				}else{
					echo "<h1 style='color:#f00'>Changes detected</h1>";;
					ksort($output['new']);
					ksort($output['changed']);
					echo "<h2> New files </h2>";
					foreach($output['new'] as $file => $created){
						echo "<p>" . $file . " created / last changed " . $created . "</p>";
					}
					echo "<h2> Changed files </h2>";
					foreach($output['changed'] as $file => $created){
						echo "<p>" . $file . " last changed on " . $created . "</p>";
					}
					
				}
				
			}

		}
		
		function check_size($file){
			return array($file, filesize($file));
		}
	
	}
	
	$jenner_scan = new jenner_scan();