<?php  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

class esa_vzaarOptions {

    /**
     * PHP4 compatibility layer for calling the PHP5 constructor.
     */
    function esa_vzaarOptions() {
        return $this->__construct();        
    }
    
    /**
     * esa_vzaarOptions::__construct() 
     * @return void
     */
    function __construct() {
        
       	// same as $_SERVER['REQUEST_URI'], but should work under IIS 6.0
	   $this->filepath    = admin_url() . 'admin.php?page=' . $_GET['page'];
        
  		//Look for POST updates
		if ( !empty($_POST) )
			$this->esa_processor();
    }

	/**
	 * Save/Load options and add a new hook for plugins
	 */
	function esa_processor() {
    	global $vzaar, $vzaarRewrite;
    	
    	//$old_state = $vzaar->options['usePermalinks'];
    
    	if ( isset($_POST['updateoption']) ) {	
    		check_admin_referer('vzaar_settings');
    		// get the hidden option fields, taken from WP core
    		if ( $_POST['page_options'] )	
    			$options = explode(',', stripslashes($_POST['page_options']));

    		if ($options) {
    			foreach ($options as $option) {
    				$option	= trim($option);
    				$value	= isset($_POST[$option]) ? trim($_POST[$option]) : false;
    				//$value = sanitize_option($option, $value); // This does stripslashes on those that need it
    				$vzaar->options[$option] = $value;
    			}
				
				// the path should always end with a slash	
        		//$vzaar->options['vzaarvideospath'] = trailingslashit($vzaar->options['vzaarvideospath']);
    		}
    		// Save options
    		update_option('vzaar_options', $vzaar->options);
    		
    	 	esa_vzaarVideos::esa_show_message(__('Update Successfully','vzaarVIDEOS'));
    	}		
    	
        do_action( 'vzaar_update_options_page' );
        
    }

    /**
     * Render the page content
     */
    function esa_controller() {

        // get list of tabs
        $tabs = $this->esa_tabs_order();

	?>
	<script type="text/javascript">
		jQuery(document).ready(function(){
			jQuery("a.switch-expert").hide();
            /*
            jQuery(".expert").hide();
			jQuery("a.switch-expert").click(function(e) {
				jQuery(".expert").toggle();
				return false;
			});
            */
			jQuery('#slider').tabs({ fxFade: true, fxSpeed: 'fast' });
		});
	</script>
	
	<div id="slider" class="wrap">
        <ul id="tabs">
            <?php    
        	foreach($tabs as $tab_key => $tab_name) {
        	   echo "\n\t\t<li><a href='#$tab_key'>$tab_name</a></li>";
            } 
            ?>
		</ul>
        <?php    
        foreach($tabs as $tab_key => $tab_name) {
            echo "\n\t<div id='$tab_key'>\n";
            // Looks for the internal class function, otherwise enable a hook for plugins
            if ( method_exists( $this, "tab_$tab_key" ))
                call_user_func( array( &$this , "tab_$tab_key") );
            else
                do_action( 'vzaar_tab_content_' . $tab_key );
             echo "\n\t</div>";
        }
        ?>

    </div>
    <?php
        
    }

    /**
     * Create array for tabs and add a filter for other plugins to inject more tabs
     * @return array $tabs
     */
    function esa_tabs_order() {
    	$tabs = array();
    	
    	$tabs['account']		= __('Account',		'vzaarVIDEOS');
		$tabs['esa_player']		= __('Player Settings',		'vzaarVIDEOS');
    	
    	$tabs = apply_filters('vzaar_settings_tabs', $tabs);
    
    	return $tabs;
    }

    function tab_account() {
        global $vzaar;
    ?>
        <!-- Account -->
		<h2><?php _e('Account','vzaarVIDEOS'); ?></h2>
        <p><?php _e('Please wirte your Vzaar API Key and API Secret in respective fields.','vzaarVIDEOS') ?></p>
		<form name="account" method="post">
		<?php wp_nonce_field('vzaar_settings') ?>
        <input type="hidden" name="page_options" value="vzaar_apikey,vzaar_apisecret" />
			<table class="form-table vzaar-options">
				<tr valign="top">
					<th align="left"><?php _e('API Key *','vzaarVIDEOS'); ?></th>
					<td><input type="text" size="35" name="vzaar_apikey" value="<?php echo $vzaar->options['vzaar_apikey']; ?>" /> ( Collect Application Token from Vzaar Account >> Dashboard >> API )</td>
				</tr>
				<tr class="expert" valign="top">
					<th align="left"><?php _e('API Secret *','vzaarVIDEOS'); ?></th>
					<td><input type="text" size="35" name="vzaar_apisecret" value="<?php echo $vzaar->options['vzaar_apisecret']; ?>" /> ( Vzaar Account Username)</td>
				</tr>
				
			</table>
		<div class="submit"><input class="button-primary" type="submit" name="updateoption" value="<?php _e('Save Changes'); ?>"/></div>
		</form>
    <?php        
    }
	
	
	function tab_esa_player() {
        global $vzaar;
    ?>
        <!-- Account -->
		<h2><?php _e('Player Settings','vzaarVIDEOS'); ?></h2>
		<form name="esa_player" method="post">
		<?php wp_nonce_field('vzaar_settings') ?>
        <input type="hidden" name="page_options" value="vzaar_player_color,vzaar_player_border,vzaar_player_autoplay,vzaar_player_looping,vzaar_player_showplaybutton" />
			<table class="form-table vzaar-options">
				
				<tr class="expert" valign="top">
					<th align="left"><?php _e('Player Color *','vzaarVIDEOS'); ?></th>
					<td>
						<select name="vzaar_player_color" style="width:110px;">
							<option value="black" <?php if($vzaar->options['vzaar_player_color'] == 'black'){echo ' selected="selected"';} ?>>Black</option>
							<option value="blue" <?php if($vzaar->options['vzaar_player_color'] == 'blue'){echo ' selected="selected"';} ?>>Blue</option>
							<option value="red" <?php if($vzaar->options['vzaar_player_color'] == 'red'){echo ' selected="selected"';} ?>>Red</option>
							<option value="green" <?php if($vzaar->options['vzaar_player_color'] == 'green'){echo ' selected="selected"';} ?>>Green</option>
							<option value="yellow" <?php if($vzaar->options['vzaar_player_color'] == 'yellow'){echo ' selected="selected"';} ?>>Yellow</option>
							<option value="pink" <?php if($vzaar->options['vzaar_player_color'] == 'pink'){echo ' selected="selected"';} ?>>Pink</option>
							<option value="orange" <?php if($vzaar->options['vzaar_player_color'] == 'orange'){echo ' selected="selected"';} ?>>Orange</option>
							<option value="brown" <?php if($vzaar->options['vzaar_player_color'] == 'brown'){echo ' selected="selected"';} ?>>Brown</option>
						</select> ( Choose Your Media Player Color )
					</td>
				</tr>
				<tr class="expert" valign="top">
					<th align="left"><?php _e('Player Border *','vzaarVIDEOS'); ?></th>
					<td>
						<select name="vzaar_player_border" style="width:110px;">
							<option value="0" <?php if($vzaar->options['vzaar_player_border'] == '0'){echo ' selected="selected"';} ?>>Hide</option>
							<option value="1" <?php if($vzaar->options['vzaar_player_border'] == '1'){echo ' selected="selected"';} ?>>Show</option>
						</select> 
					</td>
				</tr>
				
				<tr class="expert" valign="top">
					<th align="left"><?php _e('Autoplay *','vzaarVIDEOS'); ?></th>
					<td>
						<select name="vzaar_player_autoplay" style="width:110px;">
							<option value="0" <?php if($vzaar->options['vzaar_player_autoplay'] == '0'){echo ' selected="selected"';} ?>>No</option>
							<option value="1" <?php if($vzaar->options['vzaar_player_autoplay'] == '1'){echo ' selected="selected"';} ?>>Yes</option>
						</select> 
					</td>
				</tr>
				
				<tr class="expert" valign="top">
					<th align="left"><?php _e('Looping (Autoreply) *','vzaarVIDEOS'); ?></th>
					<td>
						<select name="vzaar_player_looping" style="width:110px;">
							<option value="0" <?php if($vzaar->options['vzaar_player_looping'] == '0'){echo ' selected="selected"';} ?>>No</option>
							<option value="1" <?php if($vzaar->options['vzaar_player_looping'] == '1'){echo ' selected="selected"';} ?>>Yes</option>
						</select> 
					</td>
				</tr>
				
				<tr class="expert" valign="top">
					<th align="left"><?php _e('Show Play Button *','vzaarVIDEOS'); ?></th>
					<td>
						<select name="vzaar_player_showplaybutton" style="width:110px;">
							<option value="0" <?php if($vzaar->options['vzaar_player_showplaybutton'] == '0'){echo ' selected="selected"';} ?>>Hide</option>
							<option value="1" <?php if($vzaar->options['vzaar_player_showplaybutton'] == '1'){echo ' selected="selected"';} ?>>Show</option>
						</select> 
					</td>
				</tr>
			</table>
		<div class="submit"><input class="button-primary" type="submit" name="updateoption" value="<?php _e('Save Changes'); ?>"/></div>
		</form>
    <?php        
    }
}
?>