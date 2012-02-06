<?php
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

/**
 * called during register_activation hook
 * 
 * @access internal
 */
function esa_vzaarVIDEOS_install () {
   	global $wpdb , $wp_roles, $wp_version;
   	
	// Check for capability
	if ( !current_user_can('activate_plugins') ) 
		return;
	
	$options = get_option('vzaar_options');
	// set the default settings, if we didn't upgrade
	if ( empty( $options ) )	
 		esa_vzaar_default_options();
}

/**
 * Setup the default option array for the videos
 * 
 * @access internal
 */
function esa_vzaar_default_options() {
	global $blog_id, $vzaar;
	
	//account
	$vzaar_options['vzaar_apikey']					= 'xxxx';				// set vzaarmedia user 'API Key'
	$vzaar_options['vzaar_apisecret']				= 'yyyy';				// set vzaarmedia user 'API Secret'
	$vzaar_options['vzaar_player_color']			= 'black';				// set player color
	$vzaar_options['vzaar_player_border']			= 0;					// set player border
	$vzaar_options['vzaar_player_autoplay']			= 0;					// set autoplay
	$vzaar_options['vzaar_player_looping']			= 0;					// set looping (autoreply)
	$vzaar_options['vzaar_player_showplaybutton']	= 0;					// set play button
	
	update_option('vzaar_options', $vzaar_options);
}

/**
 * Uninstall all settings
 * 
 * @access internal
 */
function esa_vzaarVIDEOS_uninstall() {
	global $wpdb;
	
	//remove all options
	delete_option( 'vzaar_options' );
}
?>