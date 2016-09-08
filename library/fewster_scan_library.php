<?PHP
	
	require_once("fewster_library.php");
	
	class fewster_scan_library extends fewster_library{
	
		function __construct(){
			$this->whitelist = get_option("fewster_whitelist");
			if(!is_array($this->whitelist)){
				$this->whitelist = array();
			}
		}
	
		function update_core(){
		
			$dir = $this->get_config_path();
			$files = array();
			$this->counter = 0;
			
			$site_files = $this->recurse($dir, array($this, "get_data_first_core"), $files);
			
			return $site_files;
		
		}

		function update_all(){
		
			$dir = $this->get_config_path();
			$files = array();
			$changed_files = array();
			$new_files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$db_files = $wpdb->get_results("select * from " . $wpdb->prefix . "fewster_file_info");
			$site_files = $this->recurse($dir, array($this, "get_data_compare"), $files);
			
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
	
		function plugin_files_list(){
		
			$dir = $this->get_config_path();
			$files = array();
			$this->counter = 0;
			
			$site_files = $this->recurse($dir, array($this, "get_data_basic_plugins"), $files);
			
			return $site_files[1];
		
		}
	
		function core_files_list(){
		
			$dir = $this->get_config_path();
			$files = array();
			$this->counter = 0;
			
			$site_files = $this->recurse($dir, array($this, "get_data_basic_core"), $files);
			
			return $site_files[1];
		
		}
		
		function scan_new_cron(){
		
			$dir = $this->get_config_path();
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$site_files = $this->recurse($dir, array($this, "get_data_compare"), $files);
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
					$new_output .= "<p>" . $data['name'] . " " . __("is a new file") . " " . $data['size'] . " " . __("size") . " : " . date("Y-n-j G:i:s",$data['time']) . " " . __("timestamp") . "</p>";
				}
			}
			return array(count($site_files[1]),$new,$new_output,$this->counter);
		}

		function scan_size_cron(){
			$dir = $this->get_config_path();
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$site_files = $this->recurse($dir, array($this, "get_data_compare"), $files);
			$output = "";
			$new_output = "";
			$this->counter = 0;
			$new = 0;
			foreach($site_files[1] as $file => $data){
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
			return array(count($site_files[1]),$new,$new_output,$this->counter,$output);
		}
	
		function scan_time_cron(){
			$dir = $this->get_config_path();
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$site_files = $this->recurse($dir, array($this, "get_data_compare"), $files);
			$output = "";
			$new_output = "";
			$this->counter = 0;
			$new = 0;
			foreach($site_files[1] as $file => $data){
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
			return array(count($site_files[1]),$new,$new_output,$this->counter,$output);
		}
	
		function scan(){
		
			echo "<h1>" . __("Fewster Scan") . "</h2>";
		
			$dir = $this->get_config_path();
			$files = array();
			$this->counter = 0;
			
			global $wpdb;
			
			$db_files = $wpdb->get_results("select * from " . $wpdb->prefix . "fewster_file_info");
			if(count($db_files)==0){
				$site_files = $this->recurse($dir, array($this, "get_data_first"), $files);
				foreach($site_files[1] as $file => $data){
					$response = $wpdb->query( 
						$wpdb->prepare( 
							"INSERT INTO " . $wpdb->prefix . "fewster_file_info (file_path,file_zip,file_size,file_m_time,timestamp)VALUES(%s,%s,%d,%d,%d)",$data['name'], $data['zip'], $data['size'], $data['time'], time()
						)
					);
				}
				echo "<p>" . count($site_files[1]) . "  " . __('Files scanned') . "</p>";	
			}else{
				$site_files = $this->recurse($dir, array($this, "get_data_compare"), $files);
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
			return array(
							"name" => $file, 
							"size" => filesize($file),
							"time" => filemtime($file), 
						);
		}
	
		function recurse($main, $command, &$files){
			$dirHandle = opendir($main);
			while($file = readdir($dirHandle)){
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
	
	}
