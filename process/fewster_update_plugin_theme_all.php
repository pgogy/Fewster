<?PHP

	class fewster_update_plugin_theme_all{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Update all"), __("Update all"), "manage_options", "fewster-update-plugin-theme-all", array($this, "update") );
		}
		
		function update_plugin($data, $response, $library){
			global $wpdb;
			echo "<p>" . $data['Name'] . " " . __("being updated") . "</p>";
			$path = str_replace("/fewster/process//../../","/",$response->path);
			$files = $library->update_plugin(str_replace("fewster/process/../../","",$path));
			foreach($files[1] as $file){
	
				$result = $wpdb->update( 
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
		
				if($result === 0){
					 $wpdb->query( 
						$wpdb->prepare( 
							"INSERT INTO " . $wpdb->prefix . "fewster_file_info (file_path,file_zip,file_size,file_m_time,timestamp)
							VALUES
							(%s,%s,%d,%d,%d)",$file['name'], $file['zip'], $file['size'], $file['time'], time()
						)
					);
				}
			}
			
			$data = get_plugin_data($response->path);

			$wpdb->update( 
					$wpdb->prefix . 'fewster_site_info', 
					array( 
						'version' => $data['Version'],
					), 
					array( 'path' => $response->path ),
					array( 
						'%s',
					), 
					array( '%s' ) 
				);
			
		}
		
		function update_theme($theme_name, $library){
			
			$files = $library->update_theme(get_theme_root() . "/" . $theme_name);
			global $wpdb;
			echo "<p>" . __("Updating theme") . " " . $theme_name . "</p>";
			foreach($files[1] as $file){
				$result = $wpdb->update( 
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
				
				if($result === 0){
					 $wpdb->query( 
						$wpdb->prepare( 
							"INSERT INTO " . $wpdb->prefix . "fewster_file_info (file_path,file_zip,file_size,file_m_time,timestamp)
							VALUES
							(%s,%s,%d,%d,%d)",$file['name'], $file['zip'], $file['size'], $file['time'], time()
						)
					);
				}
			}
			
			$data = wp_get_theme($theme_name);
			
			$wpdb->update( 
				$wpdb->prefix . 'fewster_site_info', 
				array( 
					'version' => $data['Version'],
				), 
				array( 'path' => str_replace("\\", "/", get_theme_root()) . "/" . $theme_name ),
				array( 
					'%s',
				), 
				array( '%s' ) 
			);

		}
		
		function update_core($library){
			
			$files = $library->update_core();
			global $wpdb;
			foreach($files[1] as $file){
				$result = $wpdb->update( 
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
				
				if($result === 0){
					 $wpdb->query( 
						$wpdb->prepare( 
							"INSERT INTO " . $wpdb->prefix . "fewster_file_info (file_path,file_zip,file_size,file_m_time,timestamp)
							VALUES
							(%s,%s,%d,%d,%d)",$file['name'], $file['zip'], $file['size'], $file['time'], time()
						)
					);
				}
			}
			
			global $wp_version;
			$wpdb->update( 
				$wpdb->prefix . 'fewster_site_info', 
				array( 
					'version' => $wp_version,
				), 
				array( 'type' => "core" ),
				array( 
					'%s',
				), 
				array( '%s' ) 
			);
			
			echo "<p>" . __("Core updated") . "</p>";

		}
		
		function update(){
			require_once(dirname(__FILE__) . "/../library/fewster_scan_library.php");
			$library = new fewster_scan_library();
			global $wpdb;
			
			echo "<h2>" . __("Updating core") . "</h2>";
			
			$this->update_core($library);
			
			echo "<h2>" . __("Updating plugins and themes") . "</h2>";
			
			$plugins = get_plugins();
			foreach($plugins as $plugin => $data){
				$response = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fewster_site_info WHERE name = '" . $data['Name'] . "' and type='plugin'" );
				if(isset($response->version)){
					if($data['Version']!=$response->version){
						$this->update_plugin($data, $response, $library);
					}					
				}
			}
			
			$themes = wp_get_themes();
			
			foreach($themes as $theme_name => $data){
				$this->update_theme($theme_name, $library);
			}
			
		}
	
	}
	
	$fewster_update_plugin_theme_all = new fewster_update_plugin_theme_all();