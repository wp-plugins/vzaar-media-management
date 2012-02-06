<?php
/*
Plugin Name: Vzaar Media Management
Plugin URI: http://www.esoftarena.com/
Description: This is a Vzaar Media (audio & video) Management plugin developed by <a href="http://www.esoftarena.com">eSoftArena Ltd.</a>
Author: Enamul
Version: 1.2

Author URI: http://esoftarena.co.uk/

Copyright 2007-2012 by Enamul & Esoftarena Development Team.

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA

Please note:

The ESA WORDPRESS TCPDF is not part of this license and is available
under a Creative Commons License, which allowing you to use, modify and redistribute 
them for noncommercial purposes.

*/ 


// Stop direct call
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

//ini_set('display_errors', '1');
//ini_set('error_reporting', E_ALL);
if (!class_exists('esa_vzaarLoader')) {
class esa_vzaarLoader {
	var $version     = '1.2';
	var $minium_WP   = '2.9';
	var $options     = '';
	
	/**
	 * PHP 4 Compatible Constructor
	 */
	function esa_vzaarLoader() {$this->__construct();}

	/**
	 * PHP 5 Constructor
	 */
	function __construct() {
		// Stop the plugin if we missed the requirements
		if ( ( !$this->esa_required_version() ) || ( !$this->esa_check_memory_limit() ) )
			return;
			
		// Get some constants first
		$this->esa_load_options();
		$this->esa_define_constant();
		//$this->esa_define_tables();
		$this->esa_load_dependencies();
		//$this->start_rewrite_module();
		
		$this->plugin_name = plugin_basename(__FILE__);
		
		// Init options & tables during activation & deregister init option
		register_activation_hook( $this->plugin_name, array(&$this, 'esa_activate') );
		register_deactivation_hook( $this->plugin_name, array(&$this, 'esa_deactivate') );	

		// Register a uninstall hook to remove all tables & option automatic
		register_uninstall_hook( $this->plugin_name, array('esa_vzaarLoader', 'uninstall') );

		// Start this plugin once all other plugins are fully loaded
		add_action( 'plugins_loaded', array(&$this, 'esa_start_plugin') );
		
		//Add some links on the plugin page
		add_filter('plugin_row_meta', array(&$this, 'esa_add_plugin_links'), 10, 2);	
	}
	
	function esa_start_plugin() {
		global $vzaarRewrite;
				
		// Content Filters
		//add_filter('vzaar_videos_name', 'sanitize_title');

		add_action('wp_print_styles', array(&$this, 'esa_load_styles') );
		// Check if we are in the admin area
		if ( is_admin() ) {
			// Pass the init check or show a message
			if (get_option( 'vzaar_init_check' ) != false )
				add_action( 'admin_notices', create_function('', 'echo \'<div id="message" class="error"><p><strong>' . get_option( "vzaar_init_check" ) . '</strong></p></div>\';') );
				
		} else {
			// Add the script and style files
			add_action('wp_print_scripts', array(&$this, 'esa_load_scripts') );
			//add_action('wp_print_styles', array(&$this, 'esa_load_styles') );
			
			// Add a version number to the header
			add_action('wp_head', create_function('', 'echo "\n<meta name=\'Vzaar\' content=\'' . $this->version . '\' />\n";') );
		}
	}

    function esa_required_version() {
		global $wp_version, $wpmu_version;
			
		// Check for WP version installation
		$wp_ok  =  version_compare($wp_version, $this->minium_WP, '>=');
		
		if ( ($wp_ok == FALSE) and (IS_WPMU == FALSE) ) {
			add_action('admin_notices', create_function(
				'',
				'global $vzaar; printf (\'<div id="message" class="error"><p><strong>\' . __(\'Sorry, Vzaar Videos works only under WordPress %s or higher\', "vzaarVIDEOS" ) . \'</strong></p></div>\', $vzaar->minium_WP );')
			);
			return false;
		}
		return true;
	}
	
	function esa_check_memory_limit() {
		$memory_limit = (int) substr( ini_get('memory_limit'), 0, -1);
		//This works only with enough memory, 12MB is silly, wordpress requires already 16MB :-)
		if ( ($memory_limit != 0) && ($memory_limit < 12 ) ) {
			add_action('admin_notices', create_function(
					'',
					'echo \'<div id="message" class="error"><p><strong>' . __('Sorry, Vzaar Videos works only with a Memory Limit of 16 MB higher', 'vzaarVIDEOS') . '</strong></p></div>\';')
			);
			return false;
		}
		return true;
	}
	
	function esa_define_tables() {		
		//global $wpdb;
	}
	
	function esa_define_constant() {
		define('vzaarVERSION', $this->version);
		
		// required for Windows & XAMPP
		define('WINABSPATH', str_replace("\\", "/", ABSPATH) );
			
		// define URL
		define('vzaarFOLDER', plugin_basename( dirname(__FILE__)) );
		
		define('vzaarVIDEOS_ABSPATH', trailingslashit( str_replace("\\","/", WP_PLUGIN_DIR . '/' . plugin_basename( dirname(__FILE__) ) ) ) );
		define('vzaarVIDEOS_URLPATH', trailingslashit( WP_PLUGIN_URL . '/' . plugin_basename( dirname(__FILE__) ) ) );
	}
	
	function esa_load_dependencies() {
		global $vzaardb;
	
		// Load global libraries
		require_once (dirname (__FILE__) . '/admin/core.php');
			
		// Load backend libraries
		if ( is_admin() ) {	
			require_once (dirname (__FILE__) . '/admin/admin.php');
			require_once (dirname (__FILE__) . '/admin/vzaar-media-embed.php');
			$this->esa_vzaarAdminPanel = new esa_vzaarAdminPanel();
			
		// Load frontend libraries							
		} else {
			require_once (dirname (__FILE__) . '/admin/vzaar-shortcodes.php');
		}	
	}
	
	function esa_load_scripts() {
	}
	
	function esa_load_thickbox_images() {
	}
	
	function esa_load_styles() {
	}
	
	function esa_load_options() {
		// Load the options
		$this->options = get_option('vzaar_options');
	}
		
	function esa_activate() {
		include_once (dirname (__FILE__) . '/admin/install.php');
		esa_vzaarVIDEOS_install();
	}
	
	function esa_deactivate() {
	}

	function esa_uninstall() {
        include_once (dirname (__FILE__) . '/admin/install.php');
        esa_vzaarVIDEOS_uninstall();
	}
	
	function esa_add_plugin_links($links, $file) {
		if ( $file == plugin_basename(__FILE__) ) {
			//$links[] = '<a href="admin.php?page=vzaarmedia-videos">' . __('Overview', 'vzaarVIDEOS') . '</a>';
			//$links[] = '<a href="http://wordpress.org/tags/vzaarmedia-videos?forum_id=10">' . __('Get help', 'vzaarVIDEOS') . '</a>';
			//$links[] = '<a href="http://www.esoftarena.com/donation/">' . __('Donate', 'vzaarVIDEOS') . '</a>';
		}
		return $links;
	}
}
	// Let's start the holy plugin
	global $vzaar;
	$vzaar = new esa_vzaarLoader();
}
?>