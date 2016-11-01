<?PHP

	class fewster_scan_integrity{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
			add_action("admin_enqueue_scripts", array($this, "scripts"));
		}
	
		function scripts(){
			if(isset($_GET['page'])){
				if($_GET['page']=="fewster-scan-integrity-change"){
					wp_enqueue_script( 'fewster-select', plugins_url() . '/fewster/js/select.js', array( 'jquery' ) );
					wp_enqueue_script( 'fewster-integrity', plugins_url() . '/fewster/js/integrity-check.js', array( 'jquery' ) );
					wp_localize_script( 'fewster-integrity', 'fewster_select', 
																					array( 
																							'ajaxURL' => admin_url("admin-ajax.php"),
																							'nonce' => wp_create_nonce("fewster_integrity")
																						) 
					);
				}
			}
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Scan changed files"), __("Scan changed files"), "manage_options", "fewster-scan-integrity-change", array($this, "scan_changes") );
		}
		
		function scan_changes(){
			require_once(dirname(__FILE__) ."/../library/fewster_scan_library.php");
			$library = new fewster_scan_library();
			$library->scan_integrity();
		}
	
	}
	
	$fewster_scan_integrity = new fewster_scan_integrity();