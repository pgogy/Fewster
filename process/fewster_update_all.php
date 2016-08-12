<?PHP

	class fewster_update_all{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Update all"), __("Update all"), "manage_options", "fewster-update-all", array($this, "update") );
		}
		
		function update(){
			require_once(dirname(__FILE__) . "/../library/fewster_scan_library.php");
			$library = new fewster_scan_library();
			$files = $library->update_all();
			global $wpdb;
			foreach($files[1] as $file){
				$update = $wpdb->update( 
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
				if($update == 0){
					$wpdb->query( 
						$wpdb->prepare( 
							"INSERT INTO " . $wpdb->prefix . "fewster_file_info (file_path,file_zip,file_size,file_m_time,timestamp)VALUES(%s,%s,%d,%d,%d)",$file['name'], $file['zip'], $file['size'], $file['time'], time()
						)
					);
				}
			}
			echo "<h2>" . __("Fewster : updating all") . "</h2>";
			echo "<p>" . $files[0] . " " . __("files updated") . "</p>";
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
		}
	
	}
	
	$fewster_update_all = new fewster_update_all();