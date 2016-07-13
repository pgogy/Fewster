<?PHP

	class fewster_update_plugin{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Update plugin"), __("Update plugin"), "manage_options", "fewster-update-plugin", array($this, "update") );
		}
		
		function add(){
			require_once("../library/fewster_scan_library.php");
			$library = new fewster_scan_library();
			$files = $library->update_plugin($_GET['root']);
			global $wpdb;
			foreach($files[1] as $file){
				$response = $wpdb->query( 
					$wpdb->prepare( 
						"INSERT INTO " . $wpdb->prefix . "fewster_file_info (file_path,file_zip,file_size,file_m_time,timestamp)VALUES(%s,%s,%d,%d,%d)",$file['name'], $file['zip'], $file['size'], $file['time'], time()
					)
				);
			}
			
			$data = get_plugin_data($_GET['root']);

			echo "<h2>" . __("Fewster : adding plugin") . " " . $data['Name'] . "</h2>";
			echo "<p>" . $files[0] . " " . __("files added") . "</p>";
			
			$wpdb->query( 
				$wpdb->prepare( 
					"INSERT INTO " . $wpdb->prefix . "fewster_site_info (path,name,type,version)VALUES(%s,%s,%s,%s)",str_replace("\\","/",$_GET['root']),$data['Name'], "plugin", $data['Version']
				)
			);		
		}
		
		function update(){
			if(!isset($_GET['add'])){
				require_once("../library/fewster_scan_library.php");
				$library = new fewster_scan_library();
				$files = $library->update_plugin($_GET['root']);
				global $wpdb;
				foreach($files[1] as $file){
					$wpdb->update( 
						$wpdb->prefix . 'fewster_file_info', 
						array( 
							'file_m_time' => filemtime($file['name']),	
							'file_size' => filesize($file['name']),	
							'file_zip' => $library->zip_data($file['name']),	
							'timestamp' => time()	
						), 
						array( 'file_path' => $file['name'] ), 
						array( 
							'%d',
							'%d',
							'%s',
							'%d'
						), 
						array( '%s' ) 
					);
				}
				
				$data = get_plugin_data($_GET['root']);

				echo "<h2>" . __("Fewster : updating plugin") . " " . $data['Name'] . "</h2>";
				echo "<p>" . $files[0] . " " . __("files updated") . "</p>";
				global $wp_version;
				
				$wpdb->update( 
					$wpdb->prefix . 'fewster_site_info', 
					array( 
						'version' => $data['Version'],
					), 
					array( 'path' => $_GET['root'] ),
					array( 
						'%s',
					), 
					array( '%s' ) 
				);
			}else{
				$this->add();
			}
		}
	
	}
	
	$fewster_update_plugin = new fewster_update_plugin();