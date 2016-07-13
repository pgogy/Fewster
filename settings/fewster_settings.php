<?PHP

class fewster_settings{

	function options_page() {
	  ?>
		<div class="wrap">
			<h2><?PHP echo __("Fewster Settings"); ?></h2>
			<form method="post" action="">
			<?php 
				
					wp_nonce_field('fewster_settings','fewster_settings');
			
			?>
			<label for="email_address"><?PHP echo __("Addresses to email when changes occur"); ?></label>
			<input type="text" style="width:100%" name="fewster_contact_email" id="fewster_contact_email" value="<?php echo get_option("fewster_contact_email"); ?>" />
			<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
			</form>
		</div>
	  <?php
	}

	function postform(){
	
		if (!empty($_POST['fewster_contact_email'])){

			if(!wp_verify_nonce($_POST['fewster_settings'],'fewster_settings') ){
			
				print 'Sorry, your nonce did not verify.';
				exit;
				
			}else{	

				update_option("fewster_contact_email",$_POST['fewster_contact_email']);
								
			}
			
		}
		
	}
	
	function options() {
		add_submenu_page( "fewster-anti-bad", __("Settings"), __("Settings"), "manage_options", 'fewster-settings', array($this,'options_page'));
	}

}

$fewster_settings = new fewster_settings;

add_action('admin_menu', array($fewster_settings,'options'));
add_action('admin_head', array($fewster_settings,'postform'));
