<?PHP

	class fewster_scan_notify{
	
		function __construct(){
			add_action("admin_menu", array($this, "menu_create"));
			add_action('network_admin_menu', array($this, 'menu_create'));
			add_action('admin_enqueue_scripts', array($this, 'scripts'));
		}
		
		function scripts(){
			wp_enqueue_style( 'fewster_admin', plugins_url() . "/fewster/css/admin.css" );
		}
	
		function menu_create(){
			add_submenu_page( "fewster-anti-bad", __("Notification log"), __("Notification log"), "manage_options", "fewster-scan-notify", array($this, "notifications") );
		}
		
		function notifications(){
		
			global $wpdb;
			echo "<h2>" . __("Notifications log") . "</h2>";
			if(isset($_POST['purge'])){
				$deleted = $wpdb->query('truncate ' . $wpdb->prefix . 'fewster_notifications');
				if($deleted!==false){
					echo "<p>" . __("Notifications purged") . "</p>";
				}else{
					echo "<p>" . __("Error") . "</p>";
					echo "<pre>";
					print_r($wpdb);
					echo "</pre>";
				}				
			}else{
				$sort = "order by timestamp desc";
				if(isset($_GET['sort_by'])){
					switch($_GET['sort_by']){
						case "name": $sort = " order by file_path desc"; break;
						case "time": $sort = " order by timestamp desc"; break;
						case "type": $sort = " order by file_change_type desc"; break;
					}
				}
				$notifications = $wpdb->get_results('select * from ' . $wpdb->prefix . 'fewster_notifications where notification_sent=1 ' . $sort, OBJECT);
				if(count($notifications !=0)){
					echo "<p>" . __("Below is a list of notifications the site has detected. You should purge every now and then") . "</p>";
					echo "<form method='POST'>";
					echo "<input type='submit' value='" . __("Empty notification logs") . "'>";
					echo "<input type='hidden' name='" . __("purge") . "' value='on'>";
					echo "</form>";
					echo "<div class='fewster_notification_table'>";
					echo "<div class='fewster_header_row'>";
					echo "<div><a href='" . $_SERVER['REQUEST_URI'] . "&sort_by=name'>" . __("Name (click to sort by)") . "</a></div>";
					echo "<div><a href='" . $_SERVER['REQUEST_URI'] . "&sort_by=type'>" . __("Change (click to sort by)") . "</a></div>";
					echo "<div><a href='" . $_SERVER['REQUEST_URI'] . "&sort_by=time'>" . __("When (click to sort by)") . "</a></div>";
					echo "<div>" . __("Details") . "</div>";
					echo "</div>";
					foreach($notifications as $notification){
						echo "<div class='fewster_row'>";
						echo "<div>";
						echo $notification->file_path;
						echo "</div>";
						echo "<div>";
						echo $notification->file_change_type;
						echo "</div>";
						echo "<div>";
						echo date( "G:i:s l jS F" , $notification->timestamp);
						echo "</div>";
						echo "<div>";
						switch($notification->file_change_type){
							case "new": echo __("File was created"); break;
							case "time": echo __("File time stamp was changed. The previous timestamp was") . " " . date( "G:i:s l jS F" , $notification->file_m_time_prev); break;
							case "size": echo __("File size was changed. The size change is ") . " " . ($notification->file_size - $notification->file_size_prev); break;
						}
						echo "</div>";
						echo "</div>";
					}
					echo "</div>";
				}		
			}
		}
	
	}
	
	$fewster_scan_notify = new fewster_scan_notify();