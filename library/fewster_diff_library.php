<?PHP
	
	class fewster_diff_library{
	
		function remote_diff($file){
			
			require_once("fewster_scan_library.php");
			$library = new fewster_scan_library;
			
			require_once("fewster_remote_library.php");
			$remote_library = new fewster_remote_library;
			
			$remote_file_content = "";
			
			echo "<h2>" . __("Fewster Remote File Difference for") . " " . str_replace($library->get_config_path(), "", $file) . "<h2>";
			
			if(strpos($_GET['file'],"wp-content/plugins")!==FALSE){
				$remote_file = $remote_library->get_plugin($file);
				if($remote_file){
					$remote_file_content = $remote_file[1];
				}
			}else if(strpos($_GET['file'],"wp-content/themes")!==FALSE){
				$remote_file = $remote_library->get_theme($file);
				if($remote_file){
					$remote_file_content = $remote_file[1];
				}
			}else{
				$remote_file = $remote_library->get_core($file);
				$remote_file_content = $remote_file[1];
			
			}

			echo "<p>" . __("Comparing") . " " . str_replace($library->get_config_path(), "", $file) . " " . __("and") . " " . $remote_file[0]['url'] . "</p>";

			echo "<p>" . __("File size of") . " " . str_replace($library->get_config_path(), "", $file) . " " . filesize($file) . "</p>";
			echo "<p>" . __("File size of") . " " . $remote_file[0]['url'] . " " . trim(strlen($remote_file[1])) . "</p>";
			
			if(file_exists($file)){
				$current = file_get_contents($file);
			}else{
				$current = $file;
			}		

			require_once("class.Diff.php");
			$diff = new FileDiff();					
			$data = $diff->compare($remote_file_content, $current);
			$string = "";
			$counter = 0;
			foreach ($data as $line){

				switch ($line[1]){
					case 0 : $string .= "<p class='fewster_diff fewster_same'>" . htmlspecialchars($line[0]) . "</p>"; break;
					case 1 : $string .= "<p class='fewster_diff fewster_removed'> - " . htmlspecialchars($line[0]) . "</p>"; $counter++; break;
					case 2 : $string .= "<p class='fewster_diff fewster_added'> + " . htmlspecialchars($line[0]) . "</p>"; $counter++; break;
				}

			}	

			if($counter!=0){

				echo "<p><span class='fewster_difference'>" . __("White lines are the same") . "</span></p>";
				echo "<p><span class='fewster_removed'>" . __("Pink lines have been removed") . "</span></p>";
				echo "<p><span class='fewster_added'>" . __("Red lines have been added") . "</span></p>";
				echo "<div class='fewster_difference'>";
				echo "<pre>". $string . "</pre></div>";

			}else{

				$current_lines = explode("\n", str_replace("\r", "\n", $current));
				$remote_lines = explode("\n", $remote_file_content);

				echo "<h3><strong>" . __("No diffs found - commencing deeper scan") . "</strong></h3>";

				echo "<p>" . __("Number of lines in") . " " . str_replace($library->get_config_path(), "", $file) . " " . count($current_lines) . "</p>";
				echo "<p>" . __("Number of lines in") . " " . $remote_file[0]['url'] . " " . count($remote_lines) . "</p>";
				
				$counter = 0;

				for($x=0;$x<=count($current_lines);$x++){
					if(strcmp(trim($current_lines[$x]),trim($remote_lines[$x]))!=0){
						echo "<p>" . __("Line number") . " " . $x . " " . strcmp($current_lines[$x],$remote_lines[$x]) . "</p>";
						echo "<p class=''>" . __("Current file is") . " <pre>" . $current_lines[$x] . "</pre></p>";
						echo "<p class=''>" . __("Remote file is") . " <pre>" . $remote_lines[$x] . "</pre></p>";
						echo "<br />";
						$counter++;
					}
				}

				if($counter==0){
					echo "<h3>" . __("Test complete") . "</h3>";
					echo "<p>" . __("No issues found") . "</p>";
				}
			}
			
		}
	
	
		function diff($file){
			
			require_once("fewster_scan_library.php");
			$library = new fewster_scan_library;
			
			echo "<h2>" . __("Fewster File Difference for") . " " . str_replace($library->get_config_path(), "", $file) . "<h2>";
			
			$current = file_get_contents($file);
			
			global $wpdb;
			$row = $wpdb->get_row('select file_path, file_zip from ' . $wpdb->prefix . 'fewster_file_info where file_path="' . $file . '"', OBJECT);
			$dir = wp_upload_dir();
			file_put_contents($dir['path'] . '/fewster.zip', $row->file_zip);
			$zip = new ZipArchive;
			if($zip->open($dir['path'] . '/fewster.zip')){
				$file_data = $zip->getFromName(str_replace($library->get_config_path(), "", $row->file_path));
				$zip->close();
				unlink($dir['path'] . '/fewster.zip');
				if($file_data){
					require_once("class.Diff.php");
					$diff = new FileDiff();					
					echo "<p><span class='fewster_difference'>" . __("White lines are the same") . "</span></p>";
					echo "<p><span class='fewster_removed'>" . __("Pink lines have been removed") . "</span></p>";
					echo "<p><span class='fewster_added'>" . __("Red lines have been added") . "</span></p>";
					echo "<div class='fewster_difference'>";
					echo "<pre>";
					$data = $diff->compare($file_data, $current);
					$string = "";
					foreach ($data as $line){

						switch ($line[1]){
							case 0 : $string .= "<p class='fewster_diff fewster_same'>" . htmlspecialchars($line[0]) . "</p>"; break;
							case 1 : $string .= "<p class='fewster_diff fewster_removed'> - " . htmlspecialchars($line[0]) . "</p>";break;
							case 2 : $string .= "<p class='fewster_diff fewster_added'> + " . htmlspecialchars($line[0]) . "</p>";break;
						}

					}
					echo $string . "</pre></div>";
				}
			}
			
		}
	
	}
