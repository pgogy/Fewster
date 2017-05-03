<?PHP

	class fewster_theme_integrity_check{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
			add_action("admin_enqueue_scripts", array($this, "scripts"));
		}
	
		function scripts(){
			if(isset($_GET['page'])){
				if($_GET['page']=="fewster-theme-check"){
					wp_enqueue_script( 'fewster-select', plugin_dir_url(dirname(__FILE__) . "/../fewster.php") . 'js/select.js', array( 'jquery' ) );
					wp_enqueue_script( 'fewster-integrity', plugin_dir_url(dirname(__FILE__) . "/../fewster.php") . 'js/integrity-check.js', array( 'jquery' ) );
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
			add_submenu_page( "fewster-anti-bad", __("Theme Integrity Check"), __("Theme Integrity Check"), "manage_options", "fewster-theme-check", array($this, "integrity") );
		}
		
		function integrity(){
			
			global $wp_version;
			
			?><h2><?PHP echo $_GET['name'] . " " . __("Theme Integrity Check"); ?></h2><?PHP
			
			require_once(dirname(__FILE__) . "/../library/fewster_library.php");
			$library = new fewster_library;
			
			require_once(dirname(__FILE__) . "/../library/fewster_scan_library.php");
			$library = new fewster_scan_library;
			
			$files = $library->single_plugin_files_list($_GET['theme']);
			
			echo "<div class='fewster_notice_good'>";
			echo "<h2>" . __("Success") . "</h2>";
			echo "<p>" . __("No Issues found.") . "</p>";
			echo "</div>";
			echo "<div class='fewster_notice_bad'>";
			echo "<h2>" . __("Warning") . "</h2>";
			echo "<p>" . __("Some files have issues. You can ignore these issues and continue if they are known problems, or fix using remote repair and then run the check again") . "</p>";
			echo "</div>";
			echo "<div id='fewster_importProgress'><p><strong>" . __("Scan Progress") . " <span id='importTotal'></span></strong></p><div id='importProgressBar'></div></div>";
			echo '<form id="fewster_integrity_form" action="javascript:function connect(){return false;};">';
			echo "<input type='submit' id='fewster_integrity' value='" . __("Run Integrity Check") . "' />";	
			echo "<p id='fewster_select_options'><span><a href='javascript:fewster_select_all()'>" . __("Select All") . "</a></span> <span><a href='javascript:fewster_unselect_all()'>" . __("Unselect All") . "</a></span></p>"; 
			echo "<ul>";
			$counter = 0;
			foreach($files as $file){
				echo "<li>";
				echo "<input repair_url='" . admin_url("admin.php?page=fewster-r-r&file=" . $file['name']) . "' id='fewster_file_" . $counter . "'  type='checkbox' checked file='" . $file['name'] . "'>" . $file['name'] . "<span class='fewster_integrity_response' id='fewster_file_" . $counter++ . "_status' ></span></li>";
			}
			echo "</ul>";
			echo "</form>";
			
		}
		
	}
	
	$fewster_theme_integrity_check = new fewster_theme_integrity_check();