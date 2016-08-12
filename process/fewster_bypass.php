<?PHP

	class fewster_bypass{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Bypass Core"), __("Bypass Core"), "manage_options", "fewster-scan-bypass", array($this, "core") );
			add_submenu_page( "fewster-anti-bad", __("Bypass plugins"), __("Bypass plugins"), "manage_options", "fewster-scan-bypass-plugins", array($this, "plugins") );
		}
		
		function core(){
			?><h2><?PHP echo __("Bypassing core integrity"); ?></h2><?PHP
			if(!isset($_POST['fewster_bypass'])){
				?>
				<p><?PHP echo __("Are you certain you wish to bypass core integrity"); ?>?</p>
				<form action="" method="POST"> 
					<input type="submit" class="button-primary" value="<?php _e('Bypass') ?>" />
					<?PHP echo wp_nonce_field("fewster_bypass","fewster_bypass"); ?>
				</form>
				<?PHP
			}else{
				if(wp_verify_nonce($_POST['fewster_bypass'],"fewster_bypass")){
					update_option("fewster_core_integrity",1);
					?><p><?PHP echo __("Core integrity bypassed"); ?></p>
					 <p><a href="<?PHP echo admin_url("admin.php?page=fewster-scan"); ?>"><?PHP echo __("Now run a scan"); ?></p><?PHP
									
				}
			}
	
		}

		function plugins(){
			?><h2><?PHP echo __("Bypassing plugin integrity"); ?></h2><?PHP
			if(!isset($_POST['fewster_bypass_p'])){
				?>
				<p><?PHP echo __("Are you certain you wish to bypass core integrity"); ?>?</p>
				<form action="" method="POST"> 
					<input type="submit" class="button-primary" value="<?php _e('Bypass') ?>" />
					<?PHP echo wp_nonce_field("fewster_bypass_p","fewster_bypass_p"); ?>
				</form>
				<?PHP
			}else{
				if(wp_verify_nonce($_POST['fewster_bypass_p'],"fewster_bypass_p")){
					update_option("fewster_plugin_integrity",1);
					?><p><?PHP echo __("Plugin integrity bypassed"); ?></p>
					 <p><a href="<?PHP echo admin_url("admin.php?page=fewster-scan"); ?>"><?PHP echo __("Now run a scan"); ?></p><?PHP
									
				}
			}
	
		}
	
	}
	
	$fewster_bypass = new fewster_bypass();