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
			foreach($files[0] as $file){
				$update = $wpdb->update( 
					$wpdb->prefix . 'fewster_file_info', 
					array( 
						'file_m_time' => filemtime($file),	
						'file_size' => filesize($file),	
						'file_zip' => $library->zip_data($file),	
						'timestamp' => time()	
					), 
					array( 'file_path' => $file ), 
					array( 
						'%d',
						'%d',
						'%s',
						'%d'
					), 
					array( '%s' ) 
				);
			}
			foreach($files[1] as $file){
				$wpdb->query( 
						$wpdb->prepare( 
							"INSERT INTO " . $wpdb->prefix . "fewster_file_info (file_path,file_zip,file_size,file_m_time,timestamp)VALUES(%s,%s,%d,%d,%d)",$file, $library->zip_data($file), filesize($file), filemtime($file), time()
						)
					);
			}
			echo "<h2>" . __("Fewster : updating all") . "</h2>";
			echo "<p>" . (count($files[0]) + count($files[1]))  . " " . __("files updated") . "</p>";
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