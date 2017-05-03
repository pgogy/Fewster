<?PHP

	class fewster_plugin_list{
	
		function __construct(){
			add_action("plugin_action_links", array($this, "plugin_lists"),1,4);
			add_action('admin_enqueue_scripts', array($this, 'scripts'));
		}

		function scripts(){
			wp_enqueue_style( 'fewster_admin', plugin_dir_url(dirname(__FILE__) . "/../fewster.php") . "css/admin.css" );
			wp_enqueue_script( 'fewster_quick_integrity', plugin_dir_url(dirname(__FILE__) . "/../fewster.php") . "js/quick_integrity.js" );
			wp_localize_script( 'fewster_quick_integrity', 'fewster_quick_integrity', 
																				array( 
																						'ajaxURL' => site_url() . "/wp-admin/admin-ajax.php",
																						'nonce' => wp_create_nonce("fewster_quick_integrity")
																					)
			);
		}
	
		function plugin_lists($actions, $plugin_file, $plugin_data, $context){
			$parts = explode("/",$plugin_file);
			$actions['integrity'] = "<p><span class='fewster_integrity_options'>" . __("Fewster Anti-Bad Integrity check") . "</strong> <a href='" . admin_url("admin.php?page=fewster-plugin-check&name=" . $plugin_data['Name'] . "&plugin=") . str_replace("\\","/",ABSPATH) . 'wp-content/plugins/' . $parts[0] . "/'>" . __("Full") . "</a> | <a href=\"javascript:fewster_quick_integrity_data('plugin','" . strtolower(str_replace(" ","_", $plugin_data['Name'])) . "','" . str_replace("\\","/",ABSPATH) . 'wp-content/plugins/' . $parts[0] . "/')\">" . __("Quick") . "</a>";
			$actions['integrity'] .= "<div class='fewster_integrity_update' id='fewster_integrity_update_" . strtolower(str_replace(" ","_", $plugin_data['Name'])) . "'><h4>" . __("Checking") . " " . $plugin_data['Name'] . "</h4><p></p></div>";
			$actions['integrity'] .= "<div class='fewster_integrity_success' id='fewster_integrity_success_" . strtolower(str_replace(" ","_", $plugin_data['Name'])) . "'><p>" . __("Plugin") . " " . $plugin_data['Name'] . " " . __("is free of issues") . "</p></div>";
			$actions['integrity'] .= "<div class='fewster_integrity_fail' id='fewster_integrity_fail_" . strtolower(str_replace(" ","_", $plugin_data['Name'])) . "'><h4>" . __("Plugin") . " " . $plugin_data['Name'] . " " . __("has issues") . "</h4><p><a href='" . admin_url("admin.php?page=fewster-plugin-check&name=" . $plugin_data['Name'] . "&plugin=") . str_replace("\\","/",ABSPATH) . 'wp-content/plugins/' . $parts[0] . "/'>" . __("Run an integrity check") ."</a></p></div></p>";
			
			return $actions;
		}
		
	}
	
	$fewster_plugin_list = new fewster_plugin_list();