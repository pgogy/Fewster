<?PHP

	class fewster_add{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Add"), __("Add"), "manage_options", "fewster-add", array($this, "add") );
		}
		
		function add($file){
			?><h2><?PHP echo __("Adding"); ?> <?PHP echo $_GET['file']; ?></h2><?PHP
			if(!isset($_POST['fewster_file'])){
				?>
				<p><?PHP echo __("Are you certain you wish to add") . " " . $_GET['file']; ?>?</p>
				<form action="" method="POST"> 
					<input type="hidden" name="fewster_file" value="<?PHP echo $_GET['file']; ?>" />
					<input type="submit" class="button-primary" value="<?php _e('Add') ?>" />
					<?PHP echo wp_nonce_field("fewster_repair","fewster_repair"); ?>
				</form>
				<?PHP
			}else{
				if(wp_verify_nonce($_POST['fewster_repair'],"fewster_repair")){
				
					require_once(dirname(__FILE__) . "/../library/fewster_scan_library.php");
					$library = new fewster_scan_library;
			
					global $wpdb;
			
					$response = $wpdb->query( 
						$wpdb->prepare( 
							"INSERT INTO " . $wpdb->prefix . "fewster_file_info (file_path,file_zip,file_size,file_m_time,timestamp)VALUES(%s,%s,%d,%d,%d)",$_POST['fewster_file'], $library->zip_data($_POST['fewster_file']), filesize($_POST['fewster_file']), filemtime($_POST['fewster_file']), time()
						)
					);
					
					echo $_POST['fewster_file'] . " " . __("has been added to the system");
					
				}
			}
		}
		
	}
	
	$fewster_add = new fewster_add();