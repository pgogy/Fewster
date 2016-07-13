<?PHP

	class fewster_delete{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Delete"), __("Delete"), "manage_options", "fewster-delete", array($this, "delete") );
		}
		
		function delete($file){
			?><h2><?PHP echo __("Deleting"); ?> <?PHP echo $_GET['file']; ?></h2><?PHP
			if(!isset($_POST['fewster_file'])){
				?>
				<p><?PHP echo __("Are you certain you wish to delete") . " " . $_GET['file']; ?>?</p>
				<form action="" method="POST"> 
					<input type="hidden" name="fewster_file" value="<?PHP echo $_GET['file']; ?>" />
					<input type="submit" class="button-primary" value="<?php _e('Repair') ?>" />
					<?PHP echo wp_nonce_field("fewster_repair","fewster_repair"); ?>
				</form>
				<?PHP
			}else{
				if(wp_verify_nonce($_POST['fewster_repair'],"fewster_repair")){
				
					unlink($_POST['fewster_file']);
					echo $_POST['fewster_file'] . " " . __("has been deleted to the system");
					
				}
			}
		}
		
	}
	
	$fewster_delete = new fewster_delete();