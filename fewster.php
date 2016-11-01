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
			wp_schedule_event(time(), 'eighthours', 'fewster_new_scan');
			wp_schedule_event(time(), 'eighthours', 'fewster_size_scan');
			wp_schedule_event(time(), 'eighthours', 'fewster_time_scan');
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

				$table_name = $wpdb->prefix . "fewster_notifications";

				$sql = "CREATE TABLE " . $table_name . " (
					  id bigint(20) NOT NULL AUTO_INCREMENT,
					  file_path text,
					  file_change_type text,
					  file_size bigint(20),
					  file_size_prev bigint(20),
					  file_m_time bigint(20),
					  file_m_time_prev bigint(20),
					  timestamp bigint(20),
					  notification bigint(20),
					  UNIQUE KEY id(id)
					);";
				
				@dbDelta($sql);

				
				add_option("fewster_init", 1);
				
			}
			
			if(get_option("fewster_init")==1){
			
				require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
			
				$table_name = $wpdb->prefix . "fewster_notifications";

				$sql = "CREATE TABLE " . $table_name . " (
					  id bigint(20) NOT NULL AUTO_INCREMENT,
					  file_path text,
					  file_change_type text,
					  file_size bigint(20),
					  file_size_prev bigint(20),
					  file_m_time bigint(20),
					  file_m_time_prev bigint(20),
					  timestamp bigint(20),
					  notification_sent bigint(20),
					  UNIQUE KEY id(id)
					);";
					
				@dbDelta($sql);
			
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
		
		function size_scan(){
		
			global $wpdb;
			require_once("library/fewster_scan_library.php");
			$updates = $this->get_updates();
			
			$library = new fewster_scan_library();
			$root = $library->get_config_path();
			$data = $library->scan_notify_size();

			$new_files = $wpdb->get_results('select * from ' . $wpdb->prefix . 'fewster_notifications where file_change_type="size" and notification_sent = 0 ', OBJECT);
			
			$list = $new_files;	
		
			$new = count($new_files);
			$total = count($new_files);

			if($new != 0){
				$email = "";
				$main = "";
				$major = "";
				$core = "";
				$p_and_t = "";
				
				$main = "<h2>" . $data[0] . " files have been scanned</h2>";
				if($new!=1){
					$main .= "<p>" . $new . " file size changes have happened";
				}else{
					$main .= "<p>" . $new . " file size changes have happened";
				}
				
				$main .= "<span style='padding-left:20px'><a style='background:#66f; color:#fff; border:1px solid #fff; padding:10px; text-decoration:none; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px;' href='" . admin_url("admin.php?page=fewster-scan-integrity-change") . "'>" . __("Integrity check these files") . "</a></span></p>";
				
				$changes = $this->site_check_overall();
				if($changes!=""){
					$major .= "<h2>" . __("Major changes") . "</h2>";
					$major .= $changes;
				}
				
				$changes = array();
				
				foreach($new_files as $index => $file){
					if(strpos($file->file_path,"wp-content")!==FALSE){
						foreach($updates as $update){
							if(strpos($file->file_path,$update[1])!==FALSE){
								if(!isset($changes[$update[0]])){
									$changes[$update[0]] = array();
								}
								array_push($changes[$update[0]], $file);
								unset($new_files[$index]);
							}
						}
					} else if(strpos($file->file_path,"wp-includes")!==FALSE){
						if(!isset($changes[__("Core")])){
							$changes[__("Core")] = array();
						}
						array_push($changes[__("Core")], $file);
						unset($new_files[$index]);
					} else if(strpos($file->file_path,"wp-admin")!==FALSE){
						if(!isset($changes[__("Core")])){
							$changes[__("Core")] = array();
						}
						array_push($changes[__("Core")], $file);
						unset($new_files[$index]);
					} else {
						$clean = str_replace("\\","/",str_replace($root,"",$file->file_path));
						if(strpos($clean,"/")===FALSE){ 
							if(!isset($changes[__("Core")])){
								$changes[__("Core")] = array();
							}
							array_push($changes[__("Core")], $file);
							unset($new_files[$index]);
						}
					}
				}
				
				if(isset($changes[__("Core")])){
					$core .= "<h3>" . __("Core changes") . "</h3>";
					$core .= "<p>" . count($changes[__("Core")]) . " " . __("file size changes to files have happened in WordPress core");
					global $wp_version, $wpdb;
					$response = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fewster_site_info WHERE type='core'" );
					if(isset($response->version)){
						if($wp_version!=$response->version){
							$core .= '<p>' . __("WordPress has been updated, so this is to be expected") . ' <a style="background:#66f; color:#fff; border:1px solid #fff; padding:10px; text-decoration:none; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px;" target="_blank" href="' . admin_url("admin.php?page=fewster-update-core") . '">' . __("Run an update") . '</a></p>';
						}
					}
				}
				unset($changes[__("Core")]);
				
				if(count($changes)!=0){
					$p_and_t .= "<h3>" . __("Theme and plugin changes") . "</h3>";
					foreach($changes as $index => $data){
						
						$p_and_t .= "<h4>" . $index . "</h4>";
						if(count($data)==1){
							$p_and_t .= "<p>" . count($data) . " " . __("file size changes to files has happened in") . " " . $index;
						}else{
							$p_and_t .= "<p>" . count($data) . " " . __("file size changes to files has happened in") . " " . $index;
						}
						$p_and_t .= '<p>' . $index . __(" has been updated, so this is to be expected. You should run an update however.") . '</p>';
					}					
				}
				
				if(count($new_files)!=0){
					$new = "<h3>" . __("Important changes") . "</h3>";
					$new .= "<p>" . __("These files with file size changes are outside updated areas and so are potentially dangerous. You should check these files") . "</p>";
					foreach($new_files as $file){
						$new .= "<p>" . $file->file_path . " " . __("modified on") . " " . date( "G:i:s l jS F" , filemtime($file->file_path)) . "</p>";		
					}
				}
				
				$email = "<table>";
				$email .= "<tr>";
				$email .= "<td>" . $main . "</td>";
				$email .= "</tr>";
				$email .= "<tr>";
				$email .= "<td width='50%'>" . $new . $core . $p_and_t . "</td>";
				$email .= "<td width='45%' valign='top' style='padding-left:100px'>" . $major . "</td>";
				$email .= "</tr>";
				$email .= "<tr>";
				$email .= "<td><h3>" . __("All changes") . "</h3>";
				foreach($list as $file){
					$email .= "<p>" . $file->file_path . " " . __("modified on") . " " . date( "G:i:s l jS F" , filemtime($file->file_path)) . "</p>";	
				}
				$email .= "</td>";
				$email .= "</tr>";
				$email .= "</table>";
				
				add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
				add_filter( 'wp_mail_from_name', array($this, 'set_from_name') );
				$address = explode(";", get_option("fewster_email"));
				foreach($address as $recipient){
					wp_mail($recipient, __("Fewster Report for") . " " . get_bloginfo("name") . " : " . $total . " " . __("file size changes detected"), $email);
				}
				remove_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
				remove_filter( 'wp_mail_from_name', array($this, 'set_from_name') );
				
				$wpdb->query("Update " . $wpdb->prefix . "fewster_notifications set notification_sent = 1 where file_change_type='size' and notification_sent = 0");

				
			}
				
		}
		
		function time_scan(){
		
			global $wpdb;
			require_once("library/fewster_scan_library.php");
			$updates = $this->get_updates();
			
			$library = new fewster_scan_library();
			$root = $library->get_config_path();
			$data = $library->scan_notify_time();

			$new_files = $wpdb->get_results('select * from ' . $wpdb->prefix . 'fewster_notifications where file_change_type="time" and notification_sent =0 ', OBJECT);
			$list = $new_files;
		
			$new = count($new_files);
			$total = count($new_files);

			if($new != 0){
				$email = "";
				$main = "";
				$major = "";
				$core = "";
				$p_and_t = "";
				
				$main = "<h2>" . $data[0] . " files have been scanned</h2>";
				if($new!=1){
					$main .= "<p>" . $new . " time stamp changes have happened";
				}else{
					$main .= "<p>" . $new . " time stamp changes have happened";
				}
				
				$main .= "<span style='padding-left:20px'><a style='background:#66f; color:#fff; border:1px solid #fff; padding:10px; text-decoration:none; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px;' href='" . admin_url("admin.php?page=fewster-scan-integrity-change") . "'>" . __("Integrity check new files") . "</a></span></p>";
				
				$changes = $this->site_check_overall();
				if($changes!=""){
					$major .= "<h2>" . __("Major changes") . "</h2>";
					$major .= $changes;
				}
				
				$changes = array();
				
				foreach($new_files as $index => $file){
					if(strpos($file->file_path,"wp-content")!==FALSE){
						foreach($updates as $update){
							if(strpos($file->file_path,$update[1])!==FALSE){
								if(!isset($changes[$update[0]])){
									$changes[$update[0]] = array();
								}
								array_push($changes[$update[0]], $file);
								unset($new_files[$index]);
							}
						}
					} else if(strpos($file->file_path,"wp-includes")!==FALSE){
						if(!isset($changes[__("Core")])){
							$changes[__("Core")] = array();
						}
						array_push($changes[__("Core")], $file);
						unset($new_files[$index]);
					} else if(strpos($file->file_path,"wp-admin")!==FALSE){
						if(!isset($changes[__("Core")])){
							$changes[__("Core")] = array();
						}
						array_push($changes[__("Core")], $file);
						unset($new_files[$index]);
					} else {
						$clean = str_replace("\\","/",str_replace($root,"",$file->file_path));
						if(strpos($clean,"/")===FALSE){ 
							if(!isset($changes[__("Core")])){
								$changes[__("Core")] = array();
							}
							array_push($changes[__("Core")], $file);
							unset($new_files[$index]);
						}
					}
				}
				
				if(isset($changes[__("Core")])){
					$core .= "<h3>" . __("Core changes") . "</h3>";
					$core .= "<p>" . count($changes[__("Core")]) . " " . __("time stamp changes to files have happened in WordPress core");
					global $wp_version, $wpdb;
					$response = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fewster_site_info WHERE type='core'" );
					if(isset($response->version)){
						if($wp_version!=$response->version){
							$core .= '<p>' . __("WordPress has been updated, so this is to be expected") . ' <a style="background:#66f; color:#fff; border:1px solid #fff; padding:10px; text-decoration:none; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px;" target="_blank" href="' . admin_url("admin.php?page=fewster-update-core") . '">' . __("Run an update") . '</a></p>';
						}
					}
				}
				unset($changes[__("Core")]);
				
				if(count($changes)!=0){
					$p_and_t .= "<h3>" . __("Theme and plugin changes") . "</h3>";
					foreach($changes as $index => $data){
						
						$p_and_t .= "<h4>" . $index . "</h4>";
						if(count($data)==1){
							$p_and_t .= "<p>" . count($data) . " " . __("time stamp changes to files has happened in") . " " . $index;
						}else{
							$p_and_t .= "<p>" . count($data) . " " . __("time stamp changes to files has happened in") . " " . $index;
						}
						$p_and_t .= '<p>' . $index . __(" has been updated, so this is to be expected. You should run an update however.") . '</p>';
					}
				}
					
				if(count($new_files)!=0){
					$new = "<h3>" . __("Important changes") . "</h3>";
					$new .= "<p>" . __("These files with time stamp changes are outside updated areas and so are potentially dangerous. You should check these files") . "</p>";
					foreach($new_files as $file){
						$new .= "<p>" . $file->file_path . " " . __("modified on") . " " . date( "G:i:s l jS F" , filemtime($file->file_path)) . "</p>";		
					}
				}
				
				$email = "<table>";
				$email .= "<tr>";
				$email .= "<td>" . $main . "</td>";
				$email .= "</tr>";
				$email .= "<tr>";
				$email .= "<td width='50%'>" . $new . $core . $p_and_t . "</td>";
				$email .= "<td width='45%' valign='top' style='padding-left:100px'>" . $major . "</td>";
				$email .= "</tr>";				
				$email .= "<tr>";
				$email .= "<td><h3>" . __("All changes") . "</h3>";
				foreach($list as $file){
					$email .= "<p>" . $file->file_path . " " . __("modified on") . " " . date( "G:i:s l jS F" , filemtime($file->file_path)) . "</p>";	
				}
				$email .= "</td>";
				$email .= "</tr>";
				$email .= "</table>";
				
				add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
				add_filter( 'wp_mail_from_name', array($this, 'set_from_name') );
				$address = explode(";", get_option("fewster_email"));
				foreach($address as $recipient){
					wp_mail($recipient, __("Fewster Report for") . " " . get_bloginfo("name") . " : " . $total . " " . __("time stamp changes detected"), $email);
				}
				remove_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
				remove_filter( 'wp_mail_from_name', array($this, 'set_from_name') );
			
				$wpdb->query("Update " . $wpdb->prefix . "fewster_notifications set notification_sent = 1 where file_change_type='time' and notification_sent = 0");
			
			}
				
		}
		
		function new_scan(){
		
			global $wpdb;
			require_once("library/fewster_scan_library.php");
			$updates = $this->get_updates();
			
			$library = new fewster_scan_library();
			$root = $library->get_config_path();
			$data = $library->scan_notify_new();

			$new_files = $wpdb->get_results('select * from ' . $wpdb->prefix . 'fewster_notifications where file_change_type="new" and notification_sent = 0 ', OBJECT);
			$list = $new_files;		

			$new = count($new_files);
			$total = count($new_files);

			if($new != 0){
				$email = "";
				$main = "";
				$major = "";
				$core = "";
				$p_and_t = "";
				
				$main = "<h2>" . $data[0] . " files have been scanned</h2>";
				if($new!=1){
					$main .= "<p>" . $new . " new files exist";
				}else{
					$main .= "<p>" . $new . " new file exists";
				}
				
				$main .= "<span style='padding-left:20px'><a style='background:#66f; color:#fff; border:1px solid #fff; padding:10px; text-decoration:none; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px;' href='" . admin_url("admin.php?page=fewster-scan-integrity-change") . "'>" . __("Integrity check new files") . "</a></span></p>";
				
				$changes = $this->site_check_overall();
				if($changes!=""){
					$major .= "<h2>" . __("Major changes") . "</h2>";
					$major .= $changes;
				}
				
				$changes = array();
				
				foreach($new_files as $index => $file){
					if(strpos($file->file_path,"wp-content")!==FALSE){
						foreach($updates as $update){
							if(strpos($file->file_path,$update[1])!==FALSE){
								if(!isset($changes[$update[0]])){
									$changes[$update[0]] = array();
								}
								array_push($changes[$update[0]], $file);
								unset($new_files[$index]);
							}
						}
					} else if(strpos($file->file_path,"wp-includes")!==FALSE){
						if(!isset($changes[__("Core")])){
							$changes[__("Core")] = array();
						}
						array_push($changes[__("Core")], $file);
						unset($new_files[$index]);
					} else if(strpos($file->file_path,"wp-admin")!==FALSE){
						if(!isset($changes[__("Core")])){
							$changes[__("Core")] = array();
						}
						array_push($changes[__("Core")], $file);
						unset($new_files[$index]);
					} else {
						$clean = str_replace("\\","/",str_replace($root,"",$file->file_path));
						if(strpos($clean,"/")===FALSE){ 
							if(!isset($changes[__("Core")])){
								$changes[__("Core")] = array();
							}
							array_push($changes[__("Core")], $file);
							unset($new_files[$index]);
						}
					}
				}
				
				if(isset($changes[__("Core")])){
					$core .= "<h3>" . __("Core changes") . "</h3>";
					$core .= "<p>" . count($changes[__("Core")]) . " " . __("new files have been created in WordPress core");
					global $wp_version, $wpdb;
					$response = $wpdb->get_row( "SELECT * FROM " . $wpdb->prefix . "fewster_site_info WHERE type='core'" );
					if(isset($response->version)){
						if($wp_version!=$response->version){
							$core .= '<p>' . __("WordPress has been updated, so this is to be expected") . ' <a style="background:#66f; color:#fff; border:1px solid #fff; padding:10px; text-decoration:none; -webkit-border-radius: 10px; -moz-border-radius: 10px; border-radius: 10px;" target="_blank" href="' . admin_url("admin.php?page=fewster-update-core") . '">' . __("Run an update") . '</a></p>';
						}
					}
				}
				unset($changes[__("Core")]);
				
				if(count($changes)!=0){
					$p_and_t .= "<h3>" . __("Theme and plugin changes") . "</h3>";
					foreach($changes as $index => $data){
						$p_and_t .= "<h4>" . $index . "</h4>";
						if(count($data)==1){
							$p_and_t .= "<p>" . count($data) . " " . __("new file has been created in") . " " . $index;
						}else{
							$p_and_t .= "<p>" . count($data) . " " . __("new files have been created in") . " " . $index;
						}
						$p_and_t .= '<p>' . $index . __(" has been updated, so this is to be expected. You should run an update however.") . '</p>';
					}				
				}
				
				if(count($new_files)!=0){
					$new = "<h3>" . __("Important changes") . "</h3>";
					$new .= "<p>" . __("These new files are outside updated areas and so are potentially dangerous. You should check these files") . "</p>";
					foreach($new_files as $file){
						$new .= "<p>" . $file->file_path . " " . __("modified on") . " " . date( "G:i:s l jS F" , filemtime($file->file_path)) . "</p>";		
					}
				}
				
				$email = "<table>";
				$email .= "<tr>";
				$email .= "<td>" . $main . "</td>";
				$email .= "</tr>";
				$email .= "<tr>";
				$email .= "<td width='50%'>" . $new . $core . $p_and_t . "</td>";
				$email .= "<td width='45%' valign='top' style='padding-left:100px'>" . $major . "</td>";
				$email .= "</tr>";				
				$email .= "<tr>";
				$email .= "<td><h3>" . __("All changes") . "</h3>";
				foreach($list as $file){
					$email .= "<p>" . $file->file_path . " " . __("modified on") . " " . date( "G:i:s l jS F" , filemtime($file->file_path)) . "</p>";	
				}
				$email .= "</td>";
				$email .= "</tr>";
				$email .= "</table>";
				
				add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
				add_filter( 'wp_mail_from_name', array($this, 'set_from_name') );
				$address = explode(";", get_option("fewster_email"));
				foreach($address as $recipient){
					wp_mail($recipient, __("Fewster Report for") . " " . get_bloginfo("name") . " : " . $total . " " . __("new files detected"), $email);
				}
				remove_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
				remove_filter( 'wp_mail_from_name', array($this, 'set_from_name') );
				
				$wpdb->query("Update " . $wpdb->prefix . "fewster_notifications set notification_sent = 1 where file_change_type='new' and notification_sent = 0");

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

		function set_from_name( $name ) {
			return __("Fewster Anti-Bad");
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
	require_once("scan/fewster-scan-integrity.php");	
	require_once("scan/fewster-scan-notify.php");	