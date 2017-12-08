<?PHP

	class fewster_hide_plugin{
	
		function __construct(){
			add_filter( 'all_plugins', array( $this, 'hide_self' ) );
		}
	
		public function hide_self( $plugins ) {
			if(get_option( 'fewster_super_quiet_mode')=="on"){
				unset($plugins['fewster/fewster.php']);
			}
			return $plugins;
		}
		
	}
	
	$fewster_hide_plugin = new fewster_hide_plugin();