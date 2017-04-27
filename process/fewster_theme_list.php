<?PHP

	class fewster_theme_list{
	
		function __construct(){
			if(is_admin()){
				add_filter("admin_footer", array($this, "theme_lists"));
			}
			add_action('admin_enqueue_scripts', array($this, 'scripts'));
		}

		function scripts(){
			wp_enqueue_style( 'fewster_admin', plugin_dir_url(__FILE__) . "../css/admin.css" );
			wp_enqueue_script( 'fewster_quick_integrity', plugin_dir_url(__FILE__) . "../js/quick_integrity.js" );
			wp_localize_script( 'fewster_quick_integrity', 'fewster_quick_integrity', 
																				array( 
																						'ajaxURL' => site_url() . "/wp-admin/admin-ajax.php",
																						'nonce' => wp_create_nonce("fewster_quick_integrity")
																					)
			);
		}
	
		function theme_lists(){
			
			$screen = get_current_screen();
			if($screen->base=="themes"){
				$path = get_template_directory();
				$parts = explode("/", $path);
				array_pop($parts);
				$base = implode("/", $parts);
				
				echo "<div class='fewster_themes'>";
				
				$dir = opendir($base);
				while($file = readdir($dir)){
					if($file!="."&&$file!=".."){
						if(is_dir($base . "/" . $file)){
							$data = wp_get_theme($file);
							if($data['Name']!=""){
								echo "<p><span class='fewster_integrity_options'>" . __("Theme") . " : " . $data['Name'] . " - " . __("Fewster Anti-Bad Integrity check") . "</strong> <a href='" . admin_url("admin.php?page=fewster-theme-check&name=" . $data['Name'] . "&theme=") . str_replace("\\","/", $base . "/" . $file) . "/'>" . __("Full") . "</a> | <a href=\"javascript:fewster_quick_integrity_data('theme','" . strtolower(str_replace(" ","_", $data['Name'])) . "','" . str_replace("\\","/", $base . "/" . $file) . "/')\">" . __("Quick") . "</a>";
								echo "<div class='fewster_integrity_update' id='fewster_integrity_update_" . strtolower(str_replace(" ","_", $data['Name'])) . "'><h4>" . __("Checking") . " " . $data['Name'] . "</h4><p></p></div>";
								echo "<div class='fewster_integrity_success' id='fewster_integrity_success_" . strtolower(str_replace(" ","_", $data['Name'])) . "'><p>" . __("Theme") . " " . $data['Name'] . " " . __("is free of issues") . "</p></div>";
								echo "<div class='fewster_integrity_fail' id='fewster_integrity_fail_" . strtolower(str_replace(" ","_", $data['Name'])) . "'><h4>" . __("Theme") . " " . $data['Name'] . " " . __("has issues") . "</h4><p><a href='" . admin_url("admin.php?page=fewster-theme-check&name=" . $data['Name'] . "&theme=") . str_replace("\\","/", $base . "/" . $file) . "/'>" . __("Run an integrity check") ."</a></p></div></p>";
			
							}
						}
					}
				}
				
				echo "</div>";
				
			}
		}
		
	}
	
	$fewster_theme_list = new fewster_theme_list();