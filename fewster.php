<?PHP

	/*
		Plugin Name: Fewster Anti-Bad
		Description: Anti hacking
		Author: pgogy
		Version: 0.1
	*/
	
	class fewster{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
			add_action('admin_enqueue_scripts', array($this, 'scripts'));
			add_filter('cron_schedules', array($this,'additional_schedules'));
		}
		
		function additional_schedules($schedules) {
			$schedules['every60sec'] = array('interval' => 60, 'display' => __('60 Seconds'));
			$schedules['twohours'] = array('interval' => 120*60, 'display' => __('Two Hours'));
			$schedules['fourhours'] = array('interval' => 240*60, 'display' => __('Four Hours'));
			$schedules['eighthours'] = array('interval' => 480*60, 'display' => __('Eight Hours'));
			return $schedules;
		}

		function scripts(){
			wp_enqueue_style( 'fewster_admin', plugins_url() . "/fewster/css/admin.css" );
			wp_enqueue_script( 'fewster_menu_fix', plugins_url() . "/fewster/js/menu.js" );
			wp_enqueue_script( 'fewster_accept', plugins_url() . "/fewster/js/accept.js" );
			wp_localize_script( 'fewster_accept', 'fewster_accept', 
																				array( 
																						'ajaxURL' => site_url() . "/wp-admin/admin-ajax.php",
																						'nonce' => wp_create_nonce("fewster_accept")
																					)
			);
		}
	
		function menu_create(){
			add_menu_page( "Fewster Anti-Bad", "Fewster Anti-Bad", "manage_options", "fewster-anti-bad", array($this,"fewster_main"));
		}
		
		function fewster_main(){
			?>
				<h1><?PHP echo __("Fewster Anti-Bad"); ?></h1>
				<p>
					<?PHP echo __("Use"); ?> <a href="<?PHP echo admin_url("admin.php?page=fewster-scan"); ?>"><?PHP echo __("Scan"); ?></a> <?PHP echo __("to scan the site for new files or size changes"); ?>
				</p>
				<p>
					<?PHP echo __("Use"); ?> <a href="<?PHP echo admin_url("admin.php?page=fewster-settings"); ?>"><?PHP echo __("Settings"); ?></a> <?PHP echo __("to configure Fewster"); ?>
				</p>
				<p>
					<?PHP echo __("Use"); ?> <a href="<?PHP echo admin_url("admin.php?page=fewster-register"); ?>"><?PHP echo __("Register"); ?></a> <?PHP echo __("to tell Fewster about plugins and themes"); ?>
				</p>
				<p>
					<?PHP echo __("Use"); ?> <a href="<?PHP echo admin_url("admin.php?page=fewster-update-core"); ?>"><?PHP echo __("Update Core"); ?></a> <?PHP echo __("to update all of the core"); ?>
				</p>
				<p>
					<?PHP echo __("Use"); ?> <a href="<?PHP echo admin_url("admin.php?page=fewster-integrity-core"); ?>"><?PHP echo __("Core Integrity"); ?></a> <?PHP echo __("to check core code for anything odd"); ?>
				</p>
				<p>
					<?PHP echo __("Use"); ?> <a href="<?PHP echo admin_url("admin.php?page=fewster-integrity-plugins"); ?>"><?PHP echo __("Plugin and Theme Integrity"); ?></a> <?PHP echo __("to check plugins and theme code for anything odd"); ?>
				</p>
				<p>
					<?PHP echo __("Use"); ?> <a href="<?PHP echo admin_url("admin.php?page=fewster-integrity-plugins"); ?>"><?PHP echo __("Plugin and Theme Integrity"); ?></a> <?PHP echo __("to check plugins and theme code for anything odd"); ?>
				</p>
				<p>
					<?PHP echo __("Use"); ?> <a href="<?PHP echo admin_url("admin.php?page=fewster-manage-whitelist"); ?>"><?PHP echo __("Manage Whitelist"); ?></a> <?PHP echo __("to check files you've whitelisted"); ?>
				</p>
			<?PHP
			
		}	
		
		function activation(){
			wp_schedule_event(time(), 'every60sec', 'fewster_new_scan');
			wp_schedule_event(time(), 'every60sec', 'fewster_size_scan');
			wp_schedule_event(time(), 'every60sec', 'fewster_time_scan');
		}
		
		function database_activation(){
		
			global $wpdb;
			
			if(!get_option("fewster_init")){

				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

				$table_name = $wpdb->prefix . "fewster_file_info";

				$sql = "CREATE TABLE " . $table_name . " (
					  id bigint(20) NOT NULL AUTO_INCREMENT,
					  file_path text,
					  file_zip blob,
					  file_size bigint(20),
					  file_m_time bigint(20),
					  timestamp bigint(20),
					  UNIQUE KEY id(id)
					);";
				
				@dbDelta($sql);
				
				$table_name = $wpdb->prefix . "fewster_site_info";

				$sql = "CREATE TABLE " . $table_name . " (
					  id bigint(20) NOT NULL AUTO_INCREMENT,
					  path varchar(1000),
					  name varchar(200),
					  type varchar(200),
					  version varchar(200),
					  UNIQUE KEY id(id)
					);";
				
				@dbDelta($sql);
				
				add_option("fewster_init", 1);
				
			}

		}
		
		function get_updates(){
			
			require_once(dirname(__FILE__) . "/../../../wp-admin/includes/plugin.php");
			
			$this->updates = array();
			
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
											$path = str_replace("fewster/process/../../","",str_replace("\\","/",$base . $file));
											array_push($this->updates, array($data['Name'], $path));
										}
									}
								}
							}
						}
					}
				}
			}
			
			$plugins = get_plugins();
			foreach($plugins as $plugin => $data){
				$response = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fewster_site_info WHERE name = '" . $data['Name'] . "' and type='plugin'" );
				if(isset($response->version)){
					if($data['Version']!=$response->version){
						$path = str_replace("fewster/process//../../","",$response->path);
						$parts = explode("/",$path);
						array_pop($parts);
						$path = implode("/", $parts);
						array_push($this->updates, array($data['Name'], str_replace("//","/",$path)));
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
								array_push($this->updates, array($data['Name'], str_replace("\\","/",$base . "/" . $file)));
							}
						}
					}
				}
			}
			
			$themes = wp_get_themes();
			foreach($themes as $theme => $data){
				$response = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fewster_site_info WHERE name = '" . $data['Name'] . "' and type='theme'" );
				if(isset($response->version)){
					$base = get_template_directory();
				
					$parts = explode("/",str_replace("\\","/",$base));
					array_pop($parts);
					$base = implode("/", $parts);	
						
					if($data['Version']!=$response->version){
						array_push($this->updates, array($data['Name'], $response->path));
					}
				}
			}
			
			return $this->updates;
			
		}
		
		
		function new_scan(){
		
			require_once("library/fewster_scan_library.php");
			$updates = $this->get_updates();
			$library = new fewster_scan_library();
			$data = $library->scan_new_cron($updates);
			
			$email = "";
			
			if($data[2]!=""){
				$email = "<h2>" . $data[0] . " files have been scanned</h2>";
				if($data[1]!=1){
					$email .= "<p>" . $data[1] . " new files exist</p>";
				}else{
					$email .= "<p>" . $data[1] . " new file exists</p>";
				}
				$email .= "<h2>" . __("Major changes") . "</h2>";
				$email .= $this->site_check_overall();
				$email .= "<h2>" . __("Details") . "</h2>";
				$email .= $data[2];
				$last_changed = get_option("fewster_new_files_changed");
				if($last_changed!=$data[2]){
					add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					wp_mail(get_option("fewster_email"), __("Fewster Report") . " : " . $data[1] . " " . __("new files detected"), $email);
					remove_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					update_option("fewster_new_files_changed", ($data[2]));
				}
			}
				
		}
		
		function size_scan(){

			require_once("library/fewster_scan_library.php");
			$updates = $this->get_updates();
			$library = new fewster_scan_library();
			$data = $library->scan_size_cron($updates);
			
			$email = "";

			if($data[4]!=""){
				$email = "<h2>" . $data[0] . " files have been scanned</h2>";
				if($data[1]!=1){
					$email .= "<p>" . $data[1] . " file size changes</p>";
				}else{
					$email .= "<p>" . $data[1] . " file size changed</p>";
				}
				$email .= "<h2>" . __("Major changes") . "</h2>";
				$email .= $this->site_check_overall();
				$email .= "<h2>" . __("Details") . "</h2>";
				$email .= $data[4];
				$last_changed = get_option("fewster_size_files_changed");
				if($last_changed!=$data[4]){
					add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					wp_mail(get_option("fewster_email"), __("Fewster Report") . " : " . $data[1] . " " . __("size changes detected"), $email);
					remove_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					update_option("fewster_size_files_changed", $data[4]);
				}
			}
				
		}
		
		function time_scan(){
			
			require_once("library/fewster_scan_library.php");
			$updates = $this->get_updates();
			$library = new fewster_scan_library();
			$data = $library->scan_time_cron($updates);
			$email = "";

			if($data[4]!=""){
				$email = "<h2>" . $data[0] . " files have been scanned</h2>";
				if($data[1]!=1){
					$email .= "<p>" . $data[1] . " time stamps changed</p>";
				}else{
					$email .= "<p>" . $data[1] . " time stamp changed</p>";
				}
				$email .= "<h2>" . __("Major changes") . "</h2>";
				$email .= $this->site_check_overall();
				$email .= "<h2>" . __("Details") . "</h2>";
				$email .= $data[4];
				$last_changed = get_option("fewster_time_files_changed");
				if($last_changed!=$data[4]){
					add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					wp_mail(get_option("fewster_email"), __("Fewster Report") . " : " . $data[1] . " " . __("time stamp changes detected"), $email);
					remove_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					update_option("fewster_time_files_changed", $data[4]);
				}
			}
				
		}
		
		function site_check_overall(){
			require_once(dirname(__FILE__) . "/process/fewster_admin_notices.php");
			$fewster_admin_notices = new fewster_admin_notices();
			$fewster_admin_notices->check_version();
			$fewster_admin_notices->new_plugins();
			$fewster_admin_notices->check_plugins();
			$fewster_admin_notices->new_themes();
			$fewster_admin_notices->check_themes();
			return $fewster_admin_notices->email_output(); 
		}
		
		function deactivation(){
			wp_clear_scheduled_hook('fewster_hour_scan');
			wp_clear_scheduled_hook('fewster_new_scan');
			wp_clear_scheduled_hook('fewster_size_scan');
			wp_clear_scheduled_hook('fewster_time_scan');
		}
		
		function set_content_type( $content_type ) {
			return 'text/html';
		}

	
	}
	
	$fewster = new fewster();
	
	register_activation_hook(__FILE__, array($fewster, 'activation'));
	register_activation_hook(__FILE__, array($fewster, 'database_activation'));

	register_deactivation_hook(__FILE__, array($fewster, 'deactivation'));
	
	add_action('fewster_new_scan', array($fewster,'new_scan'));
	add_action('fewster_size_scan', array($fewster,'size_scan'));
	add_action('fewster_time_scan', array($fewster,'time_scan'));
		
	require_once("settings/fewster_settings.php");
	require_once("scan/fewster_scan.php");
	require_once("process/fewster_register.php");
	require_once("process/fewster_update_core.php");	
	require_once("process/fewster_integrity.php");
	require_once("process/fewster_plugin_integrity.php");	
	require_once("process/fewster_update_plugin.php");
	require_once("process/fewster_update_theme.php");
	require_once("ajax/fewster_ajax.php");
	require_once("process/fewster_see.php");
	require_once("process/fewster_whitelist.php");
	require_once("process/fewster_manage_whitelist.php");
	require_once("process/fewster_diff.php");
	require_once("process/fewster_add.php");
	require_once("process/fewster_accept.php");
	require_once("process/fewster_delete.php");
	require_once("process/fewster_local_repair.php");
	require_once("process/fewster_remote_repair.php");
	require_once("process/fewster_admin_notices.php");
	require_once("process/fewster_bypass.php");
	require_once("process/fewster_update_all.php");
	require_once("process/fewster_update_plugin_theme_all.php");
	require_once("process/fewster_plugin_list.php");
	require_once("process/fewster_theme_list.php");
	require_once("process/fewster_plugin_integrity_check.php");
	require_once("process/fewster_theme_integrity_check.php");
	require_once("process/fewster_menu.php");
	