<?PHP

	class fewster_remote_repair{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Remote Repair"), __("Remote Repair"), "manage_options", "fewster-r-r", array($this, "repair") );
		}
		
		function repair($file){
			?><h2><?PHP echo __("Repairing"); ?> <?PHP echo $_GET['file']; ?></h2><?PHP
			if(!isset($_POST['fewster_file'])){
				?>
				<p><?PHP echo __("Are you certain you wish to repair") . " " . $_GET['file']; ?>?</p>
				<form action="" method="POST"> 
					<input type="hidden" name="fewster_file" value="<?PHP echo $_GET['file']; ?>" />
					<input type="submit" class="button-primary" value="<?php _e('Repair') ?>" />
					<?PHP echo wp_nonce_field("fewster_repair","fewster_repair"); ?>
				</form>
				<?PHP
			}else{
				if(wp_verify_nonce($_POST['fewster_repair'],"fewster_repair")){
				
					require_once(dirname(__FILE__) . "/../library/fewster_scan_library.php");
					$library = new fewster_scan_library;
					
					require_once(dirname(__FILE__) . "/../library/fewster_remote_library.php");
					$remote_library = new fewster_remote_library;
			
					global $wpdb;
					$row = $wpdb->get_row('select id, file_path, file_zip from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $_POST['fewster_file'] . '"', OBJECT);
					
					$file = "fuck off";
					
					if(strpos($_POST['fewster_file'],"wp-content/plugins")!==FALSE){
						$file = $remote_library->get_plugin();
					}
					if(strpos($_POST['fewster_file'],"wp-content/themes")!==FALSE){
						$file = $remote_library->get_theme();
					}
					if(strpos($_POST['fewster_file'],"wp-includes/")!==FALSE){
						$file = $remote_library->get_core();
					}
					if(strpos($_POST['fewster_file'],"wp-admin/")!==FALSE){
						$file = $remote_library->get_core();
					}
					if(strpos($_POST['fewster_file'],"/")!==FALSE){
						$file = $remote_library->get_core();
					}
					
					if($file){
					
						file_put_contents($_POST['fewster_file'],$file);
						if(!isset($_GET['no_update'])){
							$wpdb->update( 
								$wpdb->prefix . 'fewster_file_info', 
								array( 
									'file_zip' => $library->zip_data($_POST['fewster_file']),	
									'file_m_time' => filemtime($_POST['fewster_file']),	
									'timestamp' => time()	
								), 
								array( 'file_path' => $_POST['fewster_file'] ), 
								array( 
									'%d',
									'%d'
								), 
								array( '%d' ) 
							);
						}
						echo __("File") . " " . $_POST['fewster_file'] . " " . __("repaired");
						
					}else{
						echo __("File") . " " . $_POST['fewster_file'] . " " . __("cannot be remotely repaired");
						echo "<br />";
						echo "<a href='" . admin_url("admin.php?page=fewster-r-r&file=" . $_POST['fewster_file']) . "'>" . __("Try Local Repair") . "</a>";
					
					}
					
				}
			}
		}
		
	}
	
	$fewster_remote_repair = new fewster_remote_repair();