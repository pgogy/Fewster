<?PHP
	
	class jenner_library{
	
		function recurse($main, $command, &$files, &$counter){
			$dirHandle = opendir($main);
			while($file = readdir($dirHandle)){
				if(is_dir($main.$file."/") && $file != '.' && $file != '..'){
					$this->recurse($main.$file."/", $command, $files, $counter);
				}
				else{
					if(is_file($main . $file)){
						if(strpos($file,".php")!=FALSE){
							$counter++;
							if(is_callable($command)){
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
			return array($counter, $files);
		}
		
		function get_config_path(){
			$base = dirname(__FILE__);
			$path = false;

			if (@file_exists(dirname(dirname($base))."/wp-config.php"))
			{
				$path = dirname(dirname($base))."/";
			}
			else
			if (@file_exists(dirname(dirname(dirname($base)))."/wp-config.php"))
			{
				$path = dirname(dirname(dirname($base)))."/";
			}
			else
			$path = false;

			if ($path != false)
			{
				$path = str_replace("\\", "/", $path);
			}
			return $path;
		}
	
	}
