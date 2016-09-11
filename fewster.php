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
				<h1>Fewster Anti-Bad</h1>
				<p>
					Use "Scan" to scan the site for new files or size changes
				</p>
				<p>
					Use "Re-Scan" to scan the site for new files or size changes after the site has changed
				</p>
				<p>
					Use "Scan for IP" to scan the site for files with the poorly IP Address	
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
		
		function new_scan(){
		
			require_once("library/fewster_scan_library.php");
			$library = new fewster_scan_library();
			$data = $library->scan_new_cron();
			
			$email = "";

			if($data[2]!=""){
				$email = "<h2>" . $data[0] . " files have been scanned</h2>";
				if($data[1]!=1){
					$email .= "<p>" . $data[1] . " new files exist</p>";
				}else{
					$email .= "<p>" . $data[1] . " new file exists</p>";
				}
				$email .= "<h2>Details</h2>";
				$email .= $data[2];
				$last_changed = get_option("fewster_new_files_changed");
				if($last_changed!=$data[2]){
					add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					wp_mail(get_option("fewster_email"), __("Fewster Report : New files detected"), $email);
					remove_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					update_option("fewster_new_files_changed", ($data[2]));
				}
			}
				
		}
		
		function size_scan(){

			require_once("library/fewster_scan_library.php");
			$library = new fewster_scan_library();
			$data = $library->scan_size_cron();
			
			$email = "";

			if($data[4]!=""){
				$email = "<h2>" . $data[0] . " files have been scanned</h2>";
				if($data[1]!=1){
					$email .= "<p>" . $data[1] . " file size changes</p>";
				}else{
					$email .= "<p>" . $data[1] . " file size changed</p>";
				}
				$email .= "<h2>Details</h2>";
				$email .= $data[4];
				$last_changed = get_option("fewster_size_files_changed");
				if($last_changed!=$data[4]){
					add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					wp_mail(get_option("fewster_email"), __("Fewster Report : Size changes detected"), $email);
					remove_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					update_option("fewster_size_files_changed", $data[4]);
				}
			}
				
		}
		
		function time_scan(){
			
			require_once("library/fewster_scan_library.php");
			$library = new fewster_scan_library();
			$data = $library->scan_time_cron();
			$email = "";

			if($data[4]!=""){
				$email = "<h2>" . $data[0] . " files have been scanned</h2>";
				if($data[1]!=1){
					$email .= "<p>" . $data[1] . " time stamps changed</p>";
				}else{
					$email .= "<p>" . $data[1] . " time stamp changed</p>";
				}
				$email .= "<h2>Details</h2>";
				$email .= $data[4];
				$last_changed = get_option("fewster_time_files_changed");
				if($last_changed!=$data[4]){
					add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					wp_mail(get_option("fewster_email"), __("Fewster Report : Time stamp changes detected"), $email);
					remove_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					update_option("fewster_time_files_changed", $data[4]);
				}
			}
				
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
		
	require_once("scan/fewster_scan.php");
	require_once("process/fewster_register.php");
	require_once("process/fewster_update_core.php");	
	require_once("process/fewster_integrity.php");
	require_once("process/fewster_plugin_integrity.php");	
	require_once("settings/fewster_settings.php");
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
	require_once("process/fewster_menu.php");