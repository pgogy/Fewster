<?PHP

	class fewster_accept{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Accept"), __("Accept"), "manage_options", "fewster-accept", array($this, "accept") );
		}
		
		function accept($file){
			?><h2><?PHP echo __("Accepting"); ?> <?PHP echo $_GET['file']; ?></h2><?PHP
			if(!isset($_POST['fewster_file'])){
				?>
				<p><?PHP echo __("Are you certain you wish to accept") . " " . $_GET['file']; ?>?</p>
				<form action="" method="POST"> 
					<input type="hidden" name="fewster_file" value="<?PHP echo $_GET['file']; ?>" />
					<input type="submit" class="button-primary" value="<?php _e('Accept') ?>" />
					<?PHP echo wp_nonce_field("fewster_repair","fewster_repair"); ?>
				</form>
				<?PHP
			}else{
				if(wp_verify_nonce($_POST['fewster_repair'],"fewster_repair")){
					require_once(dirname(__FILE__) . "/../library/fewster_scan_library.php");
					$library = new fewster_scan_library;
			
					global $wpdb;
					$row = $wpdb->get_row('select id, file_path, file_zip from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $_POST['fewster_file'] . '"', OBJECT);
					
					$wpdb->update( 
						$wpdb->prefix . 'fewster_file_info', 
						array( 
							'file_m_time' => filemtime($_POST['fewster_file']),	
							'file_size' => filesize($_POST['fewster_file']),	
							'file_zip' => $library->zip_data($_POST['fewster_file']),	
							'timestamp' => time()	
						), 
						array( 'id' => $row->id ), 
						array( 
							'%d',
							'%d',
							'%s',
							'%d'
						), 
						array( '%d' ) 
					);
					
					echo __("File") . " " . $_POST['fewster_file'] . " " . __(" has been updated on the system");
					
				}
			}
		}
	
	}
	
	$fewster_accept = new fewster_accept();