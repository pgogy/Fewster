<?PHP

	class fewster_local_repair{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Local Repair"), __("Local Repair"), "manage_options", "fewster-l-r", array($this, "repair") );
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
			
					global $wpdb;
					$row = $wpdb->get_row('select id, file_path, file_zip from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $_POST['fewster_file'] . '"', OBJECT);
					$dir = wp_upload_dir();
					file_put_contents($dir['path'] . '/fewster.zip', $row->file_zip);
					$zip = new ZipArchive;
					if($zip->open($dir['path'] . '/fewster.zip')){
						$file_data = $zip->getFromName(str_replace($library->get_config_path(), "", $row->file_path));
						$zip->close();
						unlink($dir['path'] . '/fewster.zip');
						if($file_data){
							file_put_contents($_POST['fewster_file'],$file_data);
							$wpdb->update( 
								$wpdb->prefix . 'fewster_file_info', 
								array( 
									'file_m_time' => filemtime($_POST['fewster_file']),	
									'timestamp' => time()	
								), 
								array( 'id' => $row->id ), 
								array( 
									'%d',
									'%d'
								), 
								array( '%d' ) 
							);
							echo __("File") . " " . $_POST['fewster_file'] . " " . __("repaired");
						}
					}
				}
			}
		}
	
	}
	
	$fewster_local_repair = new fewster_local_repair();