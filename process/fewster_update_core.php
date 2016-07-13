<?PHP

	class fewster_update_core{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Update Core"), __("Update Core"), "manage_options", "fewster-update-core", array($this, "update") );
		}
		
		function update(){
			require_once(dirname(__FILE__) . "/../library/fewster_scan_library.php");
			$library = new fewster_scan_library();
			$files = $library->update_core();
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
			echo "<h2>" . __("Fewster : updating core") . "</h2>";
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
	
	$fewster_update_core = new fewster_update_core();