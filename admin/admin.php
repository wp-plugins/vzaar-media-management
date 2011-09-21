<?php
/**
 * esa_vzaarAdminPanel - Admin Section for Vzaar Media
 * 
 * @package Vzaar Media
 * @author Enamul
 */
class esa_vzaarAdminPanel{
	//admin user level
	var $user_level = 8;
	
	// constructor
	function esa_vzaarAdminPanel() {
		// Add the admin menu
		add_action( 'admin_menu', array (&$this, 'esa_add_menu') );
		
		// Add the script and style files
		add_action('admin_print_scripts', array(&$this, 'esa_load_scripts') );
		add_action('admin_print_styles', array(&$this, 'esa_load_styles') );
		
	}

	// integrate the menu	
	function esa_add_menu()  {
		add_menu_page( _n( 'Vzaar Media Management', 'Vzaar Media Management', 1, 'vzaarVIDEOS' ), _n( 'VZAAR MEDIA', 'VZAAR MEDIA', 1, 'vzaarVIDEOS' ), $this->user_level, vzaarFOLDER, array (&$this, 'esa_show_menu')/*, 'div'*/ );
		
		add_submenu_page( vzaarFOLDER , __('Vzaar Media Settings', 'vzaarVIDEOS'), __('Settings', 'vzaarVIDEOS'), $this->user_level, vzaarFOLDER, array (&$this, 'esa_show_menu'));
		
		add_submenu_page( vzaarFOLDER , __('Add Media', 'vzaarVIDEOS'), __('Add Media', 'vzaarVIDEOS'), $this->user_level, 'vzaarMedia-add', array (&$this, 'esa_show_menu'));
		
	   // add_submenu_page( vzaarFOLDER , __('Reset / Uninstall', 'vzaarVIDEOS'), __('Reset / Uninstall', 'vzaarVIDEOS'), $this->user_level, 'vzaarVIDEOS-setup', array (&$this, 'esa_show_menu'));
		
	}

	// load the script for the defined page and load only this code	
	function esa_show_menu() {
		global $vzaar;
		
		// init PluginChecker
		$vzaarCheck 			= new esa_CheckPlugin();	
		$vzaarCheck->URL 		= vzaarURL;
		$vzaarCheck->version 	= vzaarVERSION;
		$vzaarCheck->name 		= 'vzaar';
		
		// Show update message
        if ( current_user_can('activate_plugins') )
    		if ( $vzaarCheck->esa_startCheck() && (!IS_WPMU) ) {
    			echo '<div class="plugin-update">' . __('A new version of Vzaar Videos is available !', 'vzaarVIDEOS') . ' <a href="http://wordpress.org/extend/plugins/vzaarmedia-videos/download/" target="_blank">' . __('Download here', 'vzaarVIDEOS') . '</a></div>' ."\n";
    		}
		
		// Set installation date
		if( empty($vzaar->options['installDate']) ) {
			$vzaar->options['installDate'] = time();
			update_option('vzaar_options', $vzaar->options);			
		}
		
  		switch ($_GET['page']){
			/*case "vzaarVIDEOS-setup" :
				include_once ( dirname (__FILE__) . '/setup.php' );		// vzaarVIDEOS_admin_setup
				vzaarvideos_admin_setup();
				break;*/
			case "vzaarMedia-add" :
				include_once ( dirname (__FILE__) . '/vzaar-media-upload.php' );		// vzaarVIDEOS_admin_setup
				esa_vzaar_media_management();
				break;
			case "vzaarVIDEOS" :
			default :
				include_once ( dirname (__FILE__) . '/settings.php' );	// vzaarVIDEOS_admin_options
				$vzaar->option_page = new esa_vzaarOptions ();
				$vzaar->option_page->esa_controller();
				break;
		}
	}
	
	function esa_load_scripts() {
		
		// no need to go on if it's not a plugin page
		if( !isset($_GET['page']) )
			return;
		
		wp_register_script('swfupload_f10', vzaarVIDEOS_URLPATH .'admin/js/swfupload.js', array('jquery'), '2.2.0');
				
		switch ($_GET['page']) {
			case "vzaarVIDEOS-setup" :
				break;
			case "vzaarMedia-add" :
				wp_enqueue_script('vzaaradmin', vzaarVIDEOS_URLPATH.'admin/js/js.js', false, false);
				break;
			case "vzaarVIDEOS" :
			case vzaarFOLDER :
				wp_enqueue_script( 'jquery-ui-tabs' );
				//wp_enqueue_script('swfupload-handlers', ABSPATH.'/wp-includes/js/swfupload/handlers.js', false, false);
				break;
		}
	}		
	
	function esa_load_styles() {
		
		// no need to go on if it's not a plugin page
		if( !isset($_GET['page']) )
			return;
		
		//global
		//wp_enqueue_style( 'vzaaradmin', vzaarVIDEOS_URLPATH .'admin/css/vzaaradmin.css', false, false, 'screen' );
		
		switch ($_GET['page']) {
			case "vzaarVIDEOS-setup" :
				break;
			case "vzaarMedia-add" :
				break;
			case "vzaarVIDEOS" :
			case vzaarFOLDER :
				//wp_enqueue_style( 'thickbox');
				wp_enqueue_style( 'vzaartabs', vzaarVIDEOS_URLPATH .'admin/css/jquery.ui.tabs.css', false, false, 'screen' );
				break;
		}	
	}
}


// Dashboard update notification example
function esa_myPlugin_update_dashboard() {
  $Check = new esa_CheckPlugin();	
  $Check->URL 	= "YOUR URL";
  $Check->version = "1.00";
  $Check->name 	= "myPlugin";
  if ($Check->esa_startCheck()) {
	echo '<h3>Update Information</h3>';
	echo '<p>A new version is available</p>';
  } 
}

add_action('activity_box_end', 'esa_myPlugin_update_dashboard', '0');

if ( !class_exists( "esa_CheckPlugin" ) ) {  
	class esa_CheckPlugin {
		/**
		 * URL with the version of the plugin
		 * @var string
		 */
		var $URL = 'myURL';
		/**
		 * Version of thsi programm or plugin
		 * @var string
		 */
		var $version = '1.1';
		/**
		 * Name of the plugin (will be used in the options table)
		 * @var string
		 */
		var $name = 'myPlugin';
		/**
		 * Waiting period until the next check in seconds
		 * @var int
		 */
		var $period = 86400;					
					
		/**
		 * check for a new version, returns true if a version is avaiable
		 */
		function esa_startCheck() {

			// If we know that a update exists, don't check it again
			if (get_option( $this->name . '_update_exists' ) == 'true' )
				return true;

			$check_intervall = get_option( $this->name . '_next_update' );

			if ( ($check_intervall < time() ) or (empty($check_intervall)) ) {
				
				// Do not bother the server to often
				$check_intervall = time() + $this->period;
				update_option( $this->name . '_next_update', $check_intervall );
				
				if ( function_exists('wp_remote_request') ) {
					
					$options = array();
					$options['headers'] = array(
						'User-Agent' => 'Vzaar Media Version Checker V' . VZAARVERSION . '; (' . get_bloginfo('url') .')'
					 );
					$response = wp_remote_request($this->URL, $options);
					
					if ( is_wp_error( $response ) )
						return false;
				
					if ( 200 != $response['response']['code'] )
						return false;
				   	
					$server_version = unserialize($response['body']);

					if (is_array($server_version)) {
						if ( version_compare($server_version[$this->name], $this->version, '>') ) {
							update_option( $this->name . '_update_exists', 'true' );
							return true;
						}
					} 
						
					delete_option( $this->name . '_update_exists' );					
					return false;
				}				
			}
		}
	}
}
?>