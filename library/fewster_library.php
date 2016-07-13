<?PHP
	
	class fewster_library{
	
		function get_url($url){
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_HEADER, false);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_REFERER, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			$result = curl_exec($ch);
			$response = curl_getinfo($ch);
			curl_close($ch);
			return array($response, $result);
		}
		
		function get_config_path(){
			$base = dirname(__FILE__);
			$path = false;

			if (@file_exists(dirname(dirname(dirname(dirname($base))))."/wp-config.php"))
			{
				$path = dirname(dirname(dirname(dirname($base))))."/";
			}
			else
			if (@file_exists(dirname(dirname(dirname(dirname(dirname($base)))))."/wp-config.php"))
			{
				$path = dirname(dirname(dirname(dirname(dirname($base)))))."/";
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
