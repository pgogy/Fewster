<?PHP

class fewster_quiet_mode {
    function fewster_quiet_mode( ) {
        add_filter( 'admin_init' , array( &$this , 'register_fields' ) );
    }
    function register_fields() {
        register_setting( 'general', 'fewster_quiet_mode', 'esc_attr' );
        add_settings_field('fewster_quiet_mode', '<label for="fewster_quiet_mode">'.__('Fewster quiet mode' , 'fewster_quiet_mode' ).'</label>' , array(&$this, 'fields_html') , 'general' );
    }
    function fields_html() {
        $value = get_option( 'fewster_quiet_mode', '' );
		$checked = "";
		if($value!=""){
			$checked = " checked ";
		}
        echo '<input type="checkbox" id="fewster_quiet_mode" name="fewster_quiet_mode" ' . $checked . ' />';
    }
}

$fewster_quiet_mode = new fewster_quiet_mode();

