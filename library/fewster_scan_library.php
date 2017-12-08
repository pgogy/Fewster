<?PHP
	
	require_once("fewster_library.php");
	
	class fewster_scan_library extends fewster_library{
	
		function __construct(){
			$this->whitelist = get_option("fewster_whitelist");
			if(!is_array($this->whitelist)){
				$this->whitelist = array();
			}
			$this->installatron_ignore = FALSE;
			if(get_option("fewster_installatron_ignore")=="on"){
				$this->installatron_ignore = TRUE;
			}
		}
		
		function all_files_list(){
		
			$this->dir = $this->get_config_path();

			$files = array();
			$this->counter = 0;
			
			$site_files = $this->all_recurse($this->dir, array($this, "get_data_all_files"), $files);
			
			return $site_files[1];
		
		}
		
		function suspicious_files_list(){
		
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			
			$files = $this->all_recurse($this->dir, array($this, "get_data_compare_strings"), $files);
			
			return $files[1];
			
		}
		
		function update_core(){
		
			$this->dir = $this->get_config_path();
			$files = array();
			$this->counter = 0;
			
			$site_files = $this->recurse($this->dir, array($this, "get_data_first_core"), $files);
			
			return $site_files;
		
		}

		function update_all(){
		
			$this->dir = $this->get_config_path();			
			$files = array();
			$changed_files = array();
			$new_files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$db_files = $wpdb->get_results("select * from " . $wpdb->prefix . "fewster_file_info");
			$site_files = $this->recurse($this->dir, array($this, "get_data_compare"), $files);
			
			foreach($site_files[1] as $file => $data){
				$file_output = "<div class='fewster_file'><div class='fewster_file_name'>" . __("File") . " : " . $data['name'] . "</div>";
				$issue = false;
				$row = $wpdb->get_row('select file_size, file_m_time from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $data['name'] . '"', OBJECT);
				if($row){
					if($row->file_size != $data['size']){
						array_push($changed_files, $data['name']);
					}else if($row->file_m_time != $data['time']){
						array_push($changed_files, $data['name']);
					}
				}else{
					array_push($new_files, $data['name']);
				}
			}
			
			return array($changed_files, $new_files);
		
		}

		function update_theme($file){
		
			$parts = explode("/", $file);
			array_pop($parts);
			$dir = implode("/", $parts) . "/";
			$files = array();
			$this->counter = 0;
			$site_files = $this->recurse($dir, array($this, "get_data_first"), $files);
			
			return $site_files;
		
		}
		
		function update_plugin($file){
		
			$parts = explode("/", $file);
			array_pop($parts);
			$dir = implode("/", $parts) . "/";
			$files = array();
			$this->counter = 0;
			$site_files = $this->recurse($dir, array($this, "get_data_first"), $files);
			
			return $site_files;
		
		}

		function single_plugin_files_list($dir){
		
			$files = array();
			$this->counter = 0;
			
			$site_files = $this->recurse($dir, array($this, "get_data_basic_plugins"), $files);
			
			return $site_files[1];
		
		}

		function plugin_files_list(){
		
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			$site_files = $this->recurse($this->dir, array($this, "get_data_basic_plugins"), $files);
			
			return $site_files[1];
		
		}
	
		function core_files_list(){
		
			$this->dir = $this->get_config_path();			$files = array();
			$this->counter = 0;
			
			$site_files = $this->recurse($this->dir, array($this, "get_data_basic_core"), $files);
			
			return $site_files[1];
		
		}
		
		function scan_eval_code(){
		
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			$site_files = $this->recurse($this->dir, array($this, "get_data_eval_code"), $files);
			$new_output = "";
			$new = 0;
			foreach($site_files[1] as $file => $data){
				$file_output = "<p>" . __("File") . " : " . $data['name'] . "</p>";
				$issue = false;
				$new++;
				$new_output .= "<p>" . $data['name'] . " " . __("has an eval command in. This is often a sign of a hacked site. The file was last updated on") . " " . date("Y-n-j G:i:s",$data['time']) . "</p>";
			}
			return array(count($site_files[1]),$new,$new_output);
			
		}
		
		function scan_error_log(){
		
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			$site_files = $this->all_recurse($this->dir, array($this, "get_error_log"), $files);
			$new_output = "";
			$new = 0;
			foreach($site_files[1] as $file => $data){
				$file_output = "<p>" . __("File") . " : " . $data['name'] . "</p>";
				$issue = false;
				$new++;
				$new_output .= "<p>" . $data['name'] . " " . __(" is an error file. This may mean the site has a problem. The file was last updated on") . " " . date("Y-n-j G:i:s",$data['time']) . "</p>";
			}
			return array(count($site_files[1]),$new,$new_output);
			
		}
		
		function scan_new_cron($paths){
		
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$site_files = $this->recurse($this->dir, array($this, "get_data_compare"), $files);
			$output = "";
			$new_output = "";
			$this->counter = 0;
			$new = 0;
			foreach($site_files[1] as $file => $data){
				$file_output = "<p>" . __("File") . " : " . $data['name'] . "</p>";
				$issue = false;
				$row = $wpdb->get_row('select file_size, file_m_time from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $data['name'] . '"', OBJECT);
				if(!$row){
					$new++;
					foreach($paths as $path){
						if(strpos($data['name'],$path[1])!==FALSE){
							$new_output .= "<p>" . __("Plugin") . " " . $path[0] . " " . __("has been updated and so the change below may be ok") . "</p>";
						}
					}
					$new_output .= "<p>" . $data['name'] . " " . __("is a new file") . " " . $data['size'] . " " . __("size") . " : " . date("Y-n-j G:i:s",$data['time']) . " " . __("timestamp") . "</p>";
				}
			}
			return array(count($site_files[1]),$new,$new_output,$this->counter);
		}

		function scan_size_cron($paths){
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$site_files = $this->recurse($this->dir, array($this, "get_data_compare"), $files);
			$output = "";
			$new_output = "";
			$this->counter = 0;
			$new = 0;
			foreach($site_files[1] as $file => $data){
				foreach($paths as $path){
					if(strpos($data['name'],$path[1])!==FALSE){
						$file_output .= "<p>" . __("Plugin") . " " . $path[0] . " " . __("has been updated and so the change below may be ok") . "</p>";
					}
				}
				$file_output = "<p>" . __("File") . " : " . $data['name'] . "</p>";
				$issue = false;
				$row = $wpdb->get_row('select file_size, file_m_time from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $data['name'] . '"', OBJECT);
				if($row){
					if($row->file_size != $data['size']){
						$issue = true;
						$file_output .= "<p>" . __("Has changed size") . " " . $data['size'] . " " . __("current size") . " : " . $row->file_size . " " . __("previous size") . "</p>";
					}
				}
				
				if($issue){
					$this->counter++;
					$output .= $file_output;
				}
			}
			return array(count($site_files[1]),$this->counter,$new_output,$this->counter,$output);
		}
	
		function scan_time_cron($paths){
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$site_files = $this->recurse($this->dir, array($this, "get_data_compare"), $files);
			$output = "";
			$new_output = "";
			$this->counter = 0;
			$new = 0;
			foreach($site_files[1] as $file => $data){
				foreach($paths as $path){
					if(strpos($data['name'],$path[1])!==FALSE){
						$file_output .= "<p>" . __("Plugin") . " " . $path[0] . " " . __("has been updated and so the change below may be ok") . "</p>";
					}
				}
				$file_output = "<p>" . __("File") . " : " . $data['name'] . "</p>";
				$issue = false;
				$row = $wpdb->get_row('select file_size, file_m_time from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $data['name'] . '"', OBJECT);
				if($row){
					if($row->file_m_time != $data['time']){
						$issue = true;
						$file_output .= "<p>" . __("Has a new timestamp") . " " . date("Y-n-j G:i:s",$data['time']) . " " . __("current timestamp") . " : " . date("Y-n-j G:i:s",$row->file_m_time) . " " . __("previous timestamp") . "</p>";
					}
				}
				if($issue){
					$this->counter++;
					$output .= $file_output;
				}
			}
			return array(count($site_files[1]),$this->counter,$new_output,$this->counter,$output);
		}
	
		function scan_notify_new(){
		
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$db_files = $wpdb->get_results("select * from " . $wpdb->prefix . "fewster_file_info");
			
			$site_files = $this->recurse($this->dir, array($this, "get_data_compare"), $files);
		
			foreach($site_files[1] as $file => $data){

				$row = $wpdb->get_row('select file_size, file_m_time from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $data['name'] . '"', OBJECT);

				if(!$row){
					$notification_row = $wpdb->get_row('select file_path from ' . $wpdb->prefix . 'fewster_notifications where file_change_type="new" and file_path="' . $data['name'] . '" and notification_sent = 1 ', OBJECT);
						
					if($notification_row==""){
						$query = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "fewster_notifications (file_path,file_change_type,timestamp,notification_sent)VALUES(%s,%s,%d,%d)",$data['name'], "new", time(), 0);
						$response = $wpdb->query($query);
					}
					
				}
			
			}
			
			return $site_files;
			
		}

		function scan_notify_time(){
		
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$db_files = $wpdb->get_results("select * from " . $wpdb->prefix . "fewster_file_info");
			
			$site_files = $this->recurse($this->dir, array($this, "get_data_compare"), $files);
		
			foreach($site_files[1] as $file => $data){

				$row = $wpdb->get_row('select file_size, file_m_time from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $data['name'] . '"', OBJECT);

				if($row){
					if($row->file_m_time != $data['time']){
						
						$notification_row = $wpdb->get_row('select file_path from ' . $wpdb->prefix . 'fewster_notifications where file_change_type="time" and file_path="' . $data['name'] . '" and file_m_time = ' . $data['time'] . ' and notification_sent =1 ', OBJECT);
						
						if($notification_row==""){
							$query = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "fewster_notifications (file_path,file_change_type,file_m_time,file_m_time_prev,timestamp,notification_sent)VALUES(%s,%s,%d,%d,%d,%d)",$data['name'], "time", $data['time'], (integer)$row->file_m_time, time(), 0);
							$response = $wpdb->query($query);
						}
						
					}
				}
			
			}
			
			return $site_files;
			
		}

		function scan_notify_size(){
		
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$db_files = $wpdb->get_results("select * from " . $wpdb->prefix . "fewster_file_info");
			
			$site_files = $this->recurse($this->dir, array($this, "get_data_compare"), $files);
		
			foreach($site_files[1] as $file => $data){

				$row = $wpdb->get_row('select file_size, file_m_time from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $data['name'] . '"', OBJECT);

				if($row){
					if($row->file_size != $data['size']){
						
						$notification_row = $wpdb->get_row('select file_path from ' . $wpdb->prefix . 'fewster_notifications where file_change_type="size" and file_path="' . $data['name'] . '" and file_size = ' . $data['size'] . ' and notification_sent=1 ', OBJECT);
						
						if($notification_row==""){
							$query = $wpdb->prepare("INSERT INTO " . $wpdb->prefix . "fewster_notifications (file_path,file_change_type,file_size,file_size_prev,timestamp,notification_sent)VALUES(%s,%s,%d,%d,%d,%d)",$data['name'], "size", $data['size'], (integer)$row->file_size, time(), 0);
							$response = $wpdb->query($query);
						}
						
					}
				}
			
			}
			
			return $site_files;
			
		}
	
		function scan_integrity(){
		
			echo "<h1>" . __("Fewster Scan new and changed files") . "</h2>";
		
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$db_files = $wpdb->get_results("select * from " . $wpdb->prefix . "fewster_file_info");
			
			$site_files = $this->recurse($this->dir, array($this, "get_data_compare"), $files);
			$output = "";
			$new_output = "";
			$this->counter = 0;
			$new = 0;
		
			echo "<div id='fewster_importProgress'><p><strong>" . __("Scan Progress") . " <span id='importTotal'></span></strong></p><div id='importProgressBar'></div></div>";
			echo '<form id="fewster_integrity_form" action="javascript:function connect(){return false;};">';
			echo "<input type='submit' id='fewster_integrity' value='" . __("Run Integrity Check") . "' />";	
			echo "<p id='fewster_select_options'><span><a href='javascript:fewster_select_all()'>" . __("Select All") . "</a></span> <span><a href='javascript:fewster_unselect_all()'>" . __("Unselect All") . "</a></span></p>"; 
			echo "<ul>";

			foreach($site_files[1] as $file => $data){

				$row = $wpdb->get_row('select file_size, file_m_time from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $data['name'] . '"', OBJECT);

				if($row){
					if($row->file_size != $data['size']){
						$issue = true;
					}
					if($row->file_m_time != $data['time']){
						$issue = true;
					}
				}else{
					$issue = true;
				}

				if($issue){
					echo "<li>";
					echo "<input delete_url='" . admin_url("admin.php?page=fewster-delete&file=" . $data['name']) . "' diff_url='" . admin_url("admin.php?page=fewster-r-diff&file=" . $data['name']) . "' repair_url='" . admin_url("admin.php?page=fewster-r-r&file=" . $data['name']) . "' id='fewster_file_" . $this->counter . "'  type='checkbox' checked file='" . $data['name'] . "'>" . $data['name'] . "<span class='fewster_integrity_response' id='fewster_file_" . $this->counter++ . "_status' ></span></li>";
				}
			}
			echo "</ul>";
			echo "</form>";
		
			echo "<h3>" . count($site_files[1]) . "  " . __('Files scanned') . "</h3>";
								
		}

		function scan(){
		
			echo "<h1>" . __("Fewster Scan") . "</h2>";
		
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$db_files = $wpdb->get_results("select * from " . $wpdb->prefix . "fewster_file_info");
			if(count($db_files)==0){
				$site_files = $this->recurse($this->dir, array($this, "get_data_first"), $files);
				foreach($site_files[1] as $file => $data){
					$response = $wpdb->query( 
						$wpdb->prepare( 
							"INSERT INTO " . $wpdb->prefix . "fewster_file_info (file_path,file_zip,file_size,file_m_time,timestamp)VALUES(%s,%s,%d,%d,%d)",$data['name'], $data['zip'], $data['size'], $data['time'], time()
						)
					);
				}
				echo "<p>" . count($site_files[1]) . "  " . __('Files scanned') . "</p>";	
			}else{
				$site_files = $this->recurse($this->dir, array($this, "get_data_compare"), $files);
				$output = "";
				$new_output = "";
				$this->counter = 0;
				$new = 0;
				foreach($site_files[1] as $file => $data){
					$file_output = "<div class='fewster_file'><div class='fewster_file_name'>" . __("File") . " : " . $data['name'] . "</div>";
					$issue = false;
					$row = $wpdb->get_row('select file_size, file_m_time from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $data['name'] . '"', OBJECT);
					if($row){
						if($row->file_size != $data['size']){
							$issue = true;
							$file_output .= "<div class='fewster_size'>" . __("New size") . " " . $data['size'] . " : " . __("previous size") . " " . $row->file_size . "</div>";
						}
						if($row->file_m_time != $data['time']){
							$issue = true;
							$file_output .= "<div class='fewster_time'>" . __("New timestamp") . " " . date("Y-n-j G:i:s",$data['time']) . " : " . __("previous timestamp") . " " . date("Y-n-j G:i:s",$row->file_m_time) . "</div>";
						}
					}else{
						$new++;
						$new_output .= "<div class='fewster_new'><div class='fewster_file_name'>" . __("File") . " : " . $data['name'] . "</div><div class='fewster_data'>" . __("size") . " " . $data['size'] . " : " . __("timestamp") . " " . date("Y-n-j G:i:s",$data['time']) . "</div>";
						$new_output .= "<div class='fewster_actions'><a href='" . admin_url("admin.php?page=fewster-see&file=" . $data['name']) . "'>" . __("See File") . "</a> | <a href='" . admin_url("admin.php?page=fewster-add&file=" . $data['name']) . "'>" . __("Add File") . "</a> | <a href='" . admin_url("admin.php?page=fewster-delete&file=" . $data['name']) . "'>" . __("Remove File") . "</a> | <a href='" . admin_url("admin.php?page=fewster-whitelist&file=" . $data['name']) . "'>" . __("Whitelist File") . "</a></div></div>";
						
					}
					if($issue){
						$this->counter++;
						$output .= $file_output . "<div class='fewster_actions'><a href='" . admin_url("admin.php?page=fewster-diff&file=" . $data['name']) . "'>" . __("See Differences") . "</a>" . " | <a href='" . admin_url("admin.php?page=fewster-l-r&file=" . $data['name']) . "'>" . __("Local repair") . "</a> | <a href='" . admin_url("admin.php?page=fewster-r-r&file=" . $data['name']) . "'>" . __("Remote repair") . "</a> | <a href='" . admin_url("admin.php?page=fewster-delete&file=" . $data['name']) . "'>" . __("Remove File") . "</a> | <a href='" . admin_url("admin.php?page=fewster-accept&file=" . $data['name']) . "'>" . __("Accept File") . "</a> | <a href='" . admin_url("admin.php?page=fewster-whitelist&file=" . $data['name']) . "'>" . __("Whitelist File") . "</a></div></div>";
					}
				}
				echo "<h3>" . count($site_files[1]) . "  " . __('Files scanned') . "</h3>";
								
				if($new!=1){
					echo "<h4>" . $new . "  " . __('new files detected') . "</h4>";
				}else{
					echo "<h4>" . $new . "  " . __('new file detected') . "</h4>";
				}
				echo $new_output;
				if($this->counter!=1){
					echo "<h4>" . $this->counter . "  " . __('previously scanned files have issues') . "</h4>";
				}else{
					echo "<h4>" . $this->counter . "  " . __('previously scanned file has an issue') . "</h4>";
				}
				$this->counter = $this->counter;
				echo $output;
			}
		
		}
		
		function get_data_all_files($file){
			return array(
							"name" => $file, 
							"size" => filesize($file),
							"time" => filemtime($file), 
							"zip" => $this->zip_data($file)
						);
		}
		
		
		function get_data_compare_strings($file){
			
			$content = file_get_contents($file);
		
			$unicontent = preg_replace_callback('/\\\\u([0-9a-fA-F]{4})/', function ($match) {
				return mb_convert_encoding(pack('H*', $match[1]), 'UTF-8', 'UCS-2BE');
			}, $content);

			$xcontent = preg_replace_callback("/(\\\\x)([0-9A-Fa-f]+)/u", function($matched) {
				return chr(hexdec($matched[2]));
			}, $content);
		
			if(strlen($content) != strlen($xcontent)){
				return array(
								"name" => $file,
								"reason", "hexdec"
							);
			}
			
			preg_match_all("/\'[A-Za-z\%0-9\?\*\/\=]+\'/",
			$content,
			$out, PREG_PATTERN_ORDER);

			foreach($out[0] as $line){
				
				$line = substr($line,1,strlen($line)-2);
				
				if ( base64_encode(base64_decode($line, true) ) === $line ){
				   return array(
								"name" => $file,
								"line" => $line,
								"64line" => base64_encode(base64_decode($line)),
								"reason", "base64"
							);
				} 
			}
			
			if(strpos($content,"eval(")!==FALSE){
				return array(
								"name" => $file,
								"reason", "eval"
							);
			}
			
		}
		
		function get_images($file){
			if(strpos($file,".jpg")!==FALSE||strpos($file,".gif")!==FALSE||strpos($file,".png")!==FALSE){
				return array(
							"name" => $file
						);
			}
		}

		function recurse($main, $command, &$files){
			$dirHandle = opendir($main);
			while($file = readdir($dirHandle)){
				if($main!=$this->dir){
					if(file_exists($main . "wp-config.php")){
						if(file_exists($main . "wp-content")){
							if(file_exists($main . "wp-admin")){
								if(file_exists($main . "wp-includes")){
									break;	
								}
							}
						}
					}
				}
				if(is_dir($main.$file."/") && $file != '.' && $file != '..'){
					$this->recurse($main.$file."/", $command, $files);
				}
				else{
					if(is_file($main . $file)){
						if(strpos($file,".php")!==FALSE){
							$this->counter++;
							if(is_callable($command)){
								if(!in_array($main . $file, $this->whitelist)){
									$data = $command[0]->$command[1]($main . $file);
									if(!is_array($data)){
										if(trim($data)!=""){
											array_push($files, trim($data));
										}
									}else{
										array_push($files, $data);
									}
								}
							}
						}
					}
				}
			}
			return array($this->counter, $files);
		}

		function all_recurse($main, $command, &$files){
			$dirHandle = opendir($main);
			while($file = readdir($dirHandle)){
				if($main!=$this->dir){
					if(file_exists($main . "wp-config.php")){
						if(file_exists($main . "wp-content")){
							if(file_exists($main . "wp-admin")){
								if(file_exists($main . "wp-includes")){
									break;	
								}
							}
						}
					}
				}
				if(is_dir($main.$file."/") && $file != '.' && $file != '..'){
					$this->all_recurse($main.$file."/", $command, $files);
				}
				else{
					if(is_file($main . $file)){
						if(strpos($file,".jpg") ===FALSE && strpos($file,".png") ===FALSE && strpos($file,".gif") ===FALSE){
							$this->counter++;
							if(is_callable($command)){
								if(!in_array($main . $file, $this->whitelist)){
									$data = $command[0]->$command[1]($main . $file);
									if(!is_array($data)){
										if(trim($data)!=""){
											array_push($files, trim($data));
										}
									}else{
										array_push($files, $data);
									}
								}
							}
						}
					}
				}
			}
			return array($this->counter, $files);
		}

		function image_recurse($main, $command, &$files){
			$dirHandle = opendir($main);
			while($file = readdir($dirHandle)){
				if($main!=$this->dir){
					if(file_exists($main . "wp-config.php")){
						if(file_exists($main . "wp-content")){
							if(file_exists($main . "wp-admin")){
								if(file_exists($main . "wp-includes")){
									break;	
								}
							}
						}
					}
				}
				if(is_dir($main.$file."/") && $file != '.' && $file != '..'){
					$this->image_recurse($main.$file."/", $command, $files);
				}
				else{
					if(is_file($main . $file)){
						if(strpos($file,".jpg")!==FALSE || strpos($file,".png")!==FALSE || strpos($file,".gif")!==FALSE){
							$this->counter++;
							if(is_callable($command)){
								if(!in_array($main . $file, $this->whitelist)){
									$data = $command[0]->$command[1]($main . $file);
									if(!is_array($data)){
										if(trim($data)!=""){
											array_push($files, trim($data));
										}
									}else{
										array_push($files, $data);
									}
								}
							}
						}
					}
				}
			}
			return array($this->counter, $files);
		}



		function image_files_list(){
		
			$this->dir = $this->get_config_path();			
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$site_files = $this->image_recurse($this->dir, array($this, "get_images"), $files);
			
			return $site_files;
			
		}
	
	
		function zip_data($file){
			$zip = new ZipArchive;
			$dir = wp_upload_dir();
			if($zip->open($dir['path'] . '/' . urlencode($file) . '.zip', ZipArchive::CREATE)){
				$content = "";
				if(is_readable($file)){
					if($zip->addFromString(str_replace($this->get_config_path(),"",$file), file_get_contents($file))){
						$zip->close();
						$content = @file_get_contents($dir['path'] . '/' . urlencode($file) . '.zip');
						@unlink($dir['path'] . '/' . urlencode($file) . '.zip');
					}
				}
				if($content==""){
					$content = file_get_contents($file);
				}
				return $content;
			}else{
				if($zip->open($dir['path'] . '/' . urlencode($file) . '.zip', ZipArchive::CREATE)){
					if(is_readable($file)){
						if($zip->addFromString(str_replace($this->get_config_path(),"",$file), file_get_contents($file))){
							$zip->close();
							$content = @file_get_contents($dir['path'] . '/' . urlencode($file) . '.zip');
							unlink($dir['path'] . '/' . urlencode($file) . '.zip');
						}
					}
					if($content==""){
						$content = file_get_contents($file);
					}
				}else{
					return "";	
				}
			}
		}
	
		function get_data_first($file){
			return array(
							"name" => $file, 
							"size" => filesize($file),
							"time" => filemtime($file), 
							"zip" => $this->zip_data($file)
						);
		}
		
		function get_data_first_core($file){
			if(strpos($file,"wp-content")==FALSE){
				return array(
							"name" => $file, 
							"size" => filesize($file),
							"time" => filemtime($file), 
							"zip" => $this->zip_data($file)
						);
			}
		}
		
		function get_error_log($file){
			if(strpos($file,"error_log")!==FALSE){
				return array(
							"name" => $file, 
							"size" => filesize($file),
							"time" => filemtime($file), 
						);
			}
		}
		
		function get_data_eval_code($file){
		
			$matches = array();
		
			$key = '/(?<![_|a-z0-9|\.])eval\([^\)"]/i';
			$content = file_get_contents($file);
			if(preg_match($key,$content,$matches)){
				$lines = explode("\n",$content);
				if(count($lines)==1){
					$lines = explode("\r",$content);
				}
				foreach($lines as $line){
					if(preg_match($key,$line,$matches)){
						if(!preg_match('/([\/]+)(.+)eval/i',$line,$matches)){
							return array(
										"name" => $file,
										"time" => filemtime($file)
									);
						}
					}
				}
			}
		}
		
		function get_data_basic_core($file){
			if(strpos($file,"wp-content")==FALSE){
				return array(
							"name" => $file
						);
			}
		}
		
		function get_data_basic_plugins($file){
			if(strpos($file,"wp-content")!==FALSE){
				return array(
							"name" => $file
						);
			}
		}
		
		function get_data_compare($file){
			if(!$this->installatron_ignore){
				return array(
								"name" => $file, 
								"size" => filesize($file),
								"time" => filemtime($file), 
							);
			}else{
				if(strpos($file,"deleteme.")===FALSE && strpos(file_get_contents($file),"license: http://installatron.com/plugin/eula")===FALSE){	
					return array(
								"name" => $file, 
								"size" => filesize($file),
								"time" => filemtime($file), 
							);
				}
			}
		}
	
	}
