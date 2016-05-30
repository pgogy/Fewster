<?PHP

	/*
		Plugin Name: Jenner Anti-Bad
		Description: Fighting Spam
		Author: pgogy
		Version: 0.1
	*/
	
	class jenner{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_menu_page( "Jenner Anti-Bad", "Jenner Anti-Bad", "manage_options", "jenner-anti-bad", array($this,"jenner_main"));
		}
		
		function jenner_main(){
			?>
				<h1>Jenner Anti-Bad</h1>
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
			wp_schedule_event(time(), 'hourly', 'jenner_hour_scan');
		}
		
		function check_size($file){
			return array($file, filesize($file));
		}

		function hour_scan(){

			require_once("jenner_library.php");
			$library = new jenner_library();

			$dir = $library->get_config_path();
			$files = array();
			
			$count = 0;
			$files = $library->recurse($dir, array($this, "check_size"), $files, $count);
			$data = array();
			
			foreach($files[1] as $file){
				$data[$file[0]] = $file[1];
			}
			
			$old_data = unserialize(file_get_contents(dirname(__FILE__) . "/files.json"));
			$email = "";
			$output = array();
			$output['new'] = array();
			$output['changed'] = array();
			foreach($data as $key => $value){
				if(!isset($old_data[$key])){
					$output['new'][$key] = date("Y-m-d H:i:s", filemtime($key));
				}else{
					if($old_data[$key]!=$data[$key]){
						$output['changed'][$key] = date("Y-m-d H:i:s", filemtime($key));
					}
				}
			}
				
			if(count($output['new'])!=0 || count($output['old'])!=0){
				ksort($output['new']);
				ksort($output['changed']);
				$email .= "<h2> New files </h2>";
				foreach($output['new'] as $file => $created){
					$email .= "<p>" . $file . " created / last changed " . $created . "</p>";
				}
				echo "<h2> Changed files </h2>";
				foreach($output['changed'] as $file => $created){
					$email .= "<p>" . $file . " last changed on " . $created . "</p>";
				}
			}

			$changed_files = count($output['new']) + count($output['changed']);

			if($email!=""){
				$email = "<p>" . $files[0] . " files have been scanned</p>" . $email;
				$last_changed = get_option("jenner_files_changed");
				if($last_changed!=$changed_files){
					add_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					wp_mail("patrick.lockley@googlemail.com", "Jenner Report : Site Changes detected", $email);
					remove_filter( 'wp_mail_content_type', array($this, 'set_content_type') );
					update_option("jenner_files_changed", $changed_files);
				}
			}
				
		}
		
		function deactivation(){
			wp_clear_scheduled_hook('jenner_hour_scan');
		}
		
		function set_content_type( $content_type ) {
				return 'text/html';
		}

	
	}
	
	$jenner = new jenner();
	
	register_activation_hook(__FILE__, array($jenner, 'activation'));

	register_deactivation_hook(__FILE__, array($jenner, 'deactivation'));
	
	add_action('jenner_hour_scan', array($jenner,'hour_scan'));
		
	require_once("jenner_scan.php");
	require_once("jenner_scan_core.php");
	require_once("jenner_rescan.php");
	require_once("jenner_parameter.php");