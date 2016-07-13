<?PHP

	class fewster_register{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Register"), __("Register"), "manage_options", "fewster-register", array($this, "register") );
		}
		
		function register(){
			
			global $wpdb;
			
			$output = "";
			
			$base = plugin_dir_path(__FILE__) . "/../../";
			$dir = opendir($base);
			while($file = readdir($dir)){
				if($file!="."&&$file!=".."){
					if(is_dir($base . "/" . $file)){
						$inner_dir = opendir($base . "/" . $file);
						while($inner_file = readdir($inner_dir)){
							if($inner_file!="."&&$inner_file!=".."){
								if(!is_dir($base . $file . "/" . $inner_file)){
									$data = get_plugin_data($base . $file . "/" . $inner_file);
									if($data['Name']!=""){
										$result = $wpdb->get_row("select * from " . $wpdb->prefix . "fewster_site_info where path ='" . str_replace("\\","/",$base . $file . "/" . $inner_file) . "'");
										if(!$result){
											$wpdb->query( 
												$wpdb->prepare( 
													"INSERT INTO " . $wpdb->prefix . "fewster_site_info (path,name,type,version)VALUES(%s,%s,%s,%s)",str_replace("\\","/",$base . $file . "/" . $inner_file),$data['Name'], "plugin", $data['Version']
												)
											);
										}else{
											$output .="<p>" . __("Updated") . " " . $data['Name'] . "</p>";
											$wpdb->update( 
												$wpdb->prefix . 'fewster_site_info', 
												array( 
													'version' => $data['Version']
												), 
												array( 'id' => $result->id ), 
												array( 
													'%s'
												), 
												array( '%d' ) 
											);
										}
									}
								}
							}
						}
					}
				}
			}
			
			$path = get_template_directory();
			$parts = explode("/", $path);
			array_pop($parts);
			$base = implode("/", $parts);
			
			$dir = opendir($base);
			while($file = readdir($dir)){
				if($file!="."&&$file!=".."){
					if(is_dir($base . "/" . $file)){
						$data = wp_get_theme($file);
						if($data['Name']!=""){
							$result = $wpdb->get_row("select * from " . $wpdb->prefix . "fewster_site_info where path ='" . str_replace("\\","/",$base . "/" . $file) . "'");
							if(!$result){
								$wpdb->query( 
									$wpdb->prepare( 
									"INSERT INTO " . $wpdb->prefix . "fewster_site_info (path,name,type,version)VALUES(%s,%s,%s,%s)",str_replace("\\","/",$base . "/" . $file),$data['Name'], "theme", $data['Version']
									)
								);
							}else{
								$output .="<p>" . __("Updated") . " " . $data['Name'] . "</p>";
								$wpdb->update( 
									$wpdb->prefix . 'fewster_site_info', 
									array( 
										'version' => $data['Version']
									), 
									array( 'id' => $result->id ), 
									array( 
										'%s'
									), 
									array( '%d' ) 
								);
							}
						}
					}
				}
			}
			
			global $wp_version;
			
			$result = $wpdb->get_row("select * from " . $wpdb->prefix . "fewster_site_info where type='core'");
			if(!$result){
				$wpdb->query( 
					$wpdb->prepare( 
						"INSERT INTO " . $wpdb->prefix . "fewster_site_info (name,type,version)VALUES(%s,%s,%s)","WordPress", "core", $wp_version
					)
				);
			}else{
				global $wp_version;
				$output .="<p>" . __("Updated") . " " . $data['Name'] . "</p>";
				$wpdb->update( 
					$wpdb->prefix . 'fewster_site_info', 
					array( 
						'version' => $wp_version
					), 
					array( 'id' => $result->id ), 
					array( 
						'%s'
					), 
					array( '%d' ) 
				);
			}
			
			?><h2><?PHP echo __("Fewster plugins, themes and core registration"); ?></h2><?PHP
			
			if($output!=""){
				echo $output;
			}else{
				?><p><?PHP echo __("Registration complete"); ?></p><p><?PHP echo __("Remember to update when you add new plugins and themes"); ?></p><?PHP
			}
			
		}
	
	}
	
	$fewster_register = new fewster_register();