<?PHP

class fewster_settings{

	function __construct(){
		add_action('admin_menu', array($this,'options'));
		add_action('admin_init', array($this,'cron_check') );
		add_action('admin_init', array($this,'settings_api_init') );
	}
	
	function cron_check(){
		if($_GET['page']=="fewster-settings"){
			$crons = _get_cron_array();
			$new = get_option("fewster_new_file");
			$size = get_option("fewster_size_file");
			$time = get_option("fewster_time_file");
			foreach($crons as $timestamp => $job){
	
				if(isset($job['fewster_new_scan'])){
					$details = array_pop($job['fewster_new_scan']);
					print_r($details);
					if($details['schedule']!=$new){
						wp_unschedule_event( $timestamp, 'fewster_new_scan');
						wp_schedule_event( time(), $new, 'fewster_new_scan');
					}
				}
			
				if(isset($job['fewster_size_scan'])){
					$details = array_pop($job['fewster_size_scan']);
					if($details['schedule']!=$size){
						wp_unschedule_event( $timestamp, 'fewster_size_scan');
						wp_schedule_event( time(), $size, 'fewster_size_scan');
					}
				}
			
				if(isset($job['fewster_time_scan'])){
					$details = array_pop($job['fewster_time_scan']);
					if($details['schedule']!=$time){
						wp_unschedule_event( $timestamp, 'fewster_time_scan');
						wp_schedule_event( time(), $time, 'fewster_time_scan');
					}
				}
				
			}
		}
	}

	function settings_api_init() {
		
		add_settings_section(
			'fewster_setting_section',
			__('Fewster settings'),
			array($this,'fewster_intro_function'),
			'fewster-settings'
		);
		
		add_settings_field(
			'fewster_email',
			'Email Address for notifications',
			array($this,'email_function'),
			'fewster-settings',
			'fewster_setting_section'
		);
		
		add_settings_field(
			'fewster_new_file',
			'New file scan frequency',
			array($this,'new_file_function'),
			'fewster-settings',
			'fewster_setting_section'
		);
		
		add_settings_field(
			'fewster_size_file',
			'File size change scan frequency',
			array($this,'size_file_function'),
			'fewster-settings',
			'fewster_setting_section'
		);
		
		add_settings_field(
			'fewster_time_file',
			'Timestamp change scan frequency',
			array($this,'time_file_function'),
			'fewster-settings',
			'fewster_setting_section'
		);
		
		register_setting( 'fewster-settings', 'fewster_email' );
		register_setting( 'fewster-settings', 'fewster_new_file' );
		register_setting( 'fewster-settings', 'fewster_size_file' );
		register_setting( 'fewster-settings', 'fewster_time_file' );
	}
 
	function fewster_intro_function() {
		echo '<p>' . __("This page is where you can configure Fewster") . '</p>';
	}
 
	function email_function() {
		echo "<p>" . __("This email address will receive notifications when Fewster detects changes") . "</p>";
		echo '<input name="fewster_email" id="fewster_email" size="100" type="text" value="' . get_option( 'fewster_email' ) . '" />';
	}
	
	function time_file_function() {
		echo "<p>" . __("How often should Fewster detect file time stamp changes") . "</p>";
		echo '<select name="fewster_time_file" id="fewster_time_file">';
		echo "<option value='hourly' ";
		if(get_option("fewster_time_file")=="hourly"){
			echo " selected ";
		}
		echo ">" . __("Hourly") . "</option>";
		echo "<option value='twohours' ";
		if(get_option("fewster_time_file")=="twohours"){
			echo " selected ";
		}
		echo ">" . __("Two hours") . "</option>";
		echo "<option value='fourhours' ";
		if(get_option("fewster_time_file")=="fourhours"){
			echo " selected ";
		}
		echo ">" . __("Four hours") . "</option>";
		echo "<option value='eighthours' ";
		if(get_option("fewster_time_file")=="eighthours"){
			echo " selected ";
		}
		echo ">" . __("Eight hours") . "</option>";
		echo "<option value='twicedaily' ";
		if(get_option("fewster_time_file")=="twicedaily"){
			echo " selected ";
		}
		echo ">" . __("Twice Daily") . "</option>";
		echo "<option value='daily' ";
		if(get_option("fewster_time_file")=="daily"){
			echo " selected ";
		}
		echo ">" . __("Daily") . "</option>";
		echo "<option value='never' ";
		if(get_option("fewster_time_file")=="never"){
			echo " selected ";
		}
		echo ">" . __("Never") . "</option>";
		echo "</select>";
	}
	
	function size_file_function() {
		echo "<p>" . __("How often should Fewster detect size changes") . "</p>";
		echo '<select name="fewster_size_file" id="fewster_size_file">';
		echo "<option value='hourly' ";
		if(get_option("fewster_size_file")=="hourly"){
			echo " selected ";
		}
		echo ">" . __("Hourly") . "</option>";
		echo "<option value='twohours' ";
		if(get_option("fewster_size_file")=="twohours"){
			echo " selected ";
		}
		echo ">" . __("Two hours") . "</option>";
		echo "<option value='fourhours' ";
		if(get_option("fewster_size_file")=="fourhours"){
			echo " selected ";
		}
		echo ">" . __("Four hours") . "</option>";
		echo "<option value='eighthours' ";
		if(get_option("fewster_size_file")=="eighthours"){
			echo " selected ";
		}
		echo ">" . __("Eight hours") . "</option>";
		echo "<option value='twicedaily' ";
		if(get_option("fewster_size_file")=="twicedaily"){
			echo " selected ";
		}
		echo ">" . __("Twice Daily") . "</option>";
		echo "<option value='daily' ";
		if(get_option("fewster_size_file")=="daily"){
			echo " selected ";
		}
		echo ">" . __("Daily") . "</option>";
		echo "<option value='never' ";
		if(get_option("fewster_size_file")=="never"){
			echo " selected ";
		}
		echo ">" . __("Never") . "</option>";
		echo "</select>";
	}
	
	function new_file_function() {
		echo "<p>" . __("How often should Fewster detects new files") . "</p>";
		echo '<select name="fewster_new_file" id="fewster_new_file">';
		echo "<option value='hourly' ";
		if(get_option("fewster_new_file")=="hourly"){
			echo " selected ";
		}
		echo ">" . __("Hourly") . "</option>";
		echo "<option value='twohours' ";
		if(get_option("fewster_new_file")=="twohours"){
			echo " selected ";
		}
		echo ">" . __("Two hours") . "</option>";
		echo "<option value='fourhours' ";
		if(get_option("fewster_new_file")=="fourhours"){
			echo " selected ";
		}
		echo ">" . __("Four hours") . "</option>";
		echo "<option value='eighthours' ";
		if(get_option("fewster_new_file")=="eighthours"){
			echo " selected ";
		}
		echo ">" . __("Eight hours") . "</option>";
		echo "<option value='twicedaily' ";
		if(get_option("fewster_new_file")=="twicedaily"){
			echo " selected ";
		}
		echo ">" . __("Twice Daily") . "</option>";
		echo "<option value='daily' ";
		if(get_option("fewster_new_file")=="daily"){
			echo " selected ";
		}
		echo ">" . __("Daily") . "</option>";
		echo "<option value='never' ";
		if(get_option("fewster_new_file")=="never"){
			echo " selected ";
		}
		echo ">" . __("Never") . "</option>";
		echo "</select>";
	}

	function options_page() {
		?><form method="POST" action="options.php">
		<?php 
			settings_fields("fewster-settings");	
			do_settings_sections("fewster-settings"); 	//pass slug name of page
			submit_button();
		?>
		</form><?PHP
	}
	
	function options() {
		add_submenu_page( "fewster-anti-bad", __("Settings"), __("Settings"), "manage_options", 'fewster-settings', array($this,"options_page"));
	}

}

$fewster_settings = new fewster_settings;
