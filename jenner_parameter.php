<?PHP

	class jenner_parameter{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "jenner-anti-bad", "Scan", "Parameter Scan", "manage_options", "jenner-scan-ip", array($this, "scan") );
		}		
			
		function check_parameter($file){
			if($file != str_replace("\\","/",__FILE__)){
				if(strpos(file_get_contents($file), $_GET['parameter'])){
					return $file;
				}
			}
		}	
			
		function scan(){
		
			if(!isset($_GET['parameter'])){
			
				?><h1>Jenner Search</h1>
				<form action="" method="GET">
					<label>Enter the search term</label>
					<input type="text" name="parameter" value="Enter parameter here" />
					<input type="hidden" name="page" value="jenner-scan-ip" />
					<input type="submit" value="Search" />
				</form><?PHP
			
			}else{

				?><h1>Scanning for <?PHP echo $_GET['parameter']; ?></h1><?PHP

				require_once("jenner_library.php");
				$library = new jenner_library();
			
				$dir = $library->get_config_path();

				$files = array();
				$count = 0;
				$files = $library->recurse($dir, array($this, "check_parameter"), $files, $count);
				
				echo "<p>" . $files[0] . " files have been scanned</p>";

				if(count($files[1])==0){
					?><p>No files found</p><?PHP
				}else{
					foreach($files[1] as $file){
						echo str_replace($dir,"", $file) . " - " . date("Y-m-d H:i:s", filemtime($file)) . "<br />";
					}
				}
				
			}
		
		}
	
	}
	
	$jenner_parameter = new jenner_parameter();
