<?PHP

	class fewster_admin_notices{
	
		function __construct(){
			$this->output = "";
			$this->scan = true;
			$this->integrity = true;
			$this->register = true;
			$this->new_theme = false;
			$this->new_plugin = false;
			if(isset($_GET['page'])){
				if(strpos($_GET['page'],"fewster")===FALSE){
					add_action("admin_notices", array($this, "notices"));
				}
			}else{
				add_action("admin_notices", array($this, "notices"));
			}
		}
	
		function notices(){
			$this->check_integrity();
			if($this->integrity){
				$this->check_scan();
				$this->check_scan_addons();
				$this->check_plugin_theme_core();
			}else{
				$this->output .= "<p><strong>" . __("You must run these checks for Fewster to work") . "</strong></p>";
				$this->ran_scan();
			}
			if($this->scan){
				$this->check_scan_time();
			}
			$this->check_version();
			if($this->register){
				$this->check_plugins();
				$this->new_plugins();
				$this->check_themes();
				$this->new_themes();
			}
			$this->output();
		}
		
		function new_themes(){
			global $wpdb;
			
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
								$this->new_theme = true;
								$this->output .= '<p>' . __("Theme") . " " . $data['Name'] . " " . __("is new") . ' <a target="_blank" href="' . admin_url("admin.php?page=fewster-update-theme&add=get&root=") . str_replace("\\","/",$base . "/" . $file) . '">' . __("Add theme") . '</a></p>';
							}
						}
					}
				}
			}
		}

		function ran_scan(){
			$this->output .= '<p><strong>' . __("If you've ran a scan") . '</strong>';
			$this->output .= ' : <a href=\'javascript:fewster_accept_scan()\'>' . __("click here to suppress these messages") . '</a></p>';		
		}
		
		function new_plugins(){
		
			require_once(dirname(__FILE__) . "/../../../../wp-admin/includes/plugin.php");
			
			global $wpdb;
			
			$output = "";
			
			$base = dirname(__FILE__) . "/../../";

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
											$this->new_plugin = true;
											$this->output .= '<p>' . __("Plugin") . " " . $data['Name'] . " " . __("is new") . ' <a target="_blank" href="' . admin_url("admin.php?page=fewster-update-plugin&add=get&root=") . str_replace("\\","/",$base . $file . "/" . $inner_file) . '">' . __("Add plugin") . '</a></p>';
										}
									}
								}
							}
						}
					}
				}
			}
			
		}
		
		function check_integrity(){
			if(get_option("fewster_core_integrity")==0){
				$this->output .= '<p>' . __("You need to check integrity") . ' <a target="_blank" href="' . admin_url("admin.php?page=fewster-integrity-core") . '">' . __("check now") . '</a></p>';
				$this->integrity = false;
				$this->scan = false;
				$this->register = false;
			}
			if(get_option("fewster_plugin_integrity")==0){
				$this->output .= '<p>' . __("You need to check the integrity of plugins and themes") . ' <a target="_blank" href="' . admin_url("admin.php?page=fewster-integrity-plugins") . '">' . __("check now") . '</a></p>';
				$this->integrity = false;
				$this->scan = false;
				$this->register = false;
			}
		}
		
		function check_scan(){
			global $wpdb;
			$db_files = $wpdb->get_results("select * from " . $wpdb->prefix . "fewster_file_info");
			if(count($db_files)==0){
				$this->output .= '<p>' . __("You've never run a scan") . ' <a target="_blank" href="' . admin_url("admin.php?page=fewster-scan") . '">' . __("run a scan") . '</a></p>';
				$this->scan = false;
			}
		}
		
		function check_scan_addons(){
			global $wpdb;
			$db_files = $wpdb->get_results("select * from " . $wpdb->prefix . "fewster_file_info where file_path like '%wp-content%'");
			if(count($db_files)==0){
				$this->output .= '<p>' . __("You've never run a plugin theme scan") . ' <a target="_blank" href="' . admin_url("admin.php?page=fewster-integrity-plugins") . '">' . __("run a scan") . '</a></p>';
				$this->scan = false;
			}
		}
		
		function check_plugin_theme_core(){
			global $wpdb;
			$db_files = $wpdb->get_results("select * from " . $wpdb->prefix . "fewster_site_info");
			if(count($db_files)==0){
				$this->output .= '<p>' . __("You've need to register plugins and themes ") . ' <a target="_blank" href="' . admin_url("admin.php?page=fewster-register") . '">' . __("register now") . '</a></p>';
				$this->register = false;
			}
		}
		
		function check_scan_time(){
			$last_scan = get_option("fewster_last_scan");
			if($last_scan){
				if($last_scan<=(time()-100000)){
					$this->output .= '<p>' . __("You should run a new scan") . ' <a target="_blank" href="' . admin_url("admin.php?page=fewster-scan") . '">' . __("run a scan") . '</a></p>';
				}
			}
		}
		
		function check_version(){
			global $wp_version, $wpdb;
			$response = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fewster_site_info WHERE type='core'" );
			if(isset($response->version)){
				if($wp_version!=$response->version){
					$this->output .= '<p>' . __("WordPress has been updated") . ' <a target="_blank" href="' . admin_url("admin.php?page=fewster-update-core") . '">' . __("Run an update") . '</a></p>';
				}
			}
		}
		
		function check_plugins(){
			require_once(dirname(__FILE__) . "/../../../../wp-admin/includes/plugin.php");
			global $wpdb;
			$plugins = get_plugins();
			foreach($plugins as $plugin => $data){
				$response = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fewster_site_info WHERE name = '" . $data['Name'] . "' and type='plugin'" );
				if(isset($response->version)){
					if($data['Version']!=$response->version){
						$this->new_plugin = true;
						$this->output .= '<p>' . __("Plugin") . " " . $data['Name'] . " " . __("has been updated") . ' <a target="_blank" href="' . admin_url("admin.php?page=fewster-update-plugin&root=") . str_replace("//","/",$response->path) . '">' . __("Run an update") . '</a></p>';
					}
				}
			}	
		}
		
		function check_themes(){
			global $wpdb;
			$themes = wp_get_themes();
			foreach($themes as $theme => $data){
				$response = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fewster_site_info WHERE name = '" . $data['Name'] . "' and type='theme'" );
				if(isset($response->version)){
				
					$base = get_template_directory();
				
					$parts = explode("/",str_replace("\\","/",$base));
					array_pop($parts);
					$base = implode("/", $parts);	
						
					if($data['Version']!=$response->version){
						$this->new_plugin = true;
						$this->output .= '<p>' . __("Theme") . " " . $data['Name'] . " " . __("has been updated") . ' <a target="_blank" href="' . admin_url("admin.php?page=fewster-update-theme&root=") . $response->path . '">' . __("Run an update") . '</a></p>';
					}
				}
			}
		
		}
		
		function email_output(){
			$extra = "";
			if($this->new_theme || $this->new_plugin){
				$extra = "<p><a style='padding-left:20px'><a style='background:#66f; color:#fff; border:1px solid #fff; padding:10px; text-decoration:none; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px;' target='_blank' href='" . admin_url("admin.php?page=fewster-update-plugin-theme-all") . "'>" . __("Update all") . "</a></p>";
			}
			return $this->output . $extra;
		}
		
		function output(){
			if($this->output!=""){
				?><div class="notice notice-error"><h2><?PHP echo __("Fewster notices"); ?></h2><?PHP echo $this->output; ?></div><?PHP
			}
		}
		
	}
	
	$fewster_admin_notices = new fewster_admin_notices();