<?PHP
	
	class fewster_diff_library{
	
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
