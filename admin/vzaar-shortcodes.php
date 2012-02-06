<?php
/**
 * @author Enamul
 * @description Use WordPress Shortcode API for more features
 * @Docs http://codex.wordpress.org/Shortcode_API
 */

class esa_Vzaar_shortcodes {
    // register the new shortcodes
    function esa_Vzaar_shortcodes() {
		//Long posts should require a higher limit, see http://core.trac.wordpress.org/ticket/8553
		@ini_set('pcre.backtrack_limit', 500000);
        
        add_shortcode('vzaarmedia', array(&$this, 'vzaarmedia_shortcode_form') );
    }
	
	/**
     * Shortcode for the Image tag cloud
     * Usage : [vzaarmedia vid="xxxx" pid="yyyy"color="black" /]
     * @param array $atts
     * @return the content
     */
    function vzaarmedia_shortcode_form( $atts ) {
		$options = get_option('vzaar_options');
		
		extract(shortcode_atts(array(
			'vid' 				=> 'xxxx',
			'height' 			=> 'yyyy',
			'width' 			=> 'zzzz',
			'color' 			=> $options['vzaar_player_color'],
			'border' 			=> isset($options['vzaar_player_border'])? $options['vzaar_player_border']:'',
			'autoplay' 			=> isset($options['vzaar_player_autoplay'])? $options['vzaar_player_autoplay']:'',
			'looping' 			=> isset($options['vzaar_player_looping'])? $options['vzaar_player_looping']:'',
			'showplaybutton' 	=> isset($options['vzaar_player_showplaybutton'])? $options['vzaar_player_showplaybutton']:''
		), $atts));
		
		
		$flashvars = '';
		$flashvars .= ($border) ? '' : 'border=none&amp;';
		$flashvars .= ($autoplay) ? 'autoplay=true&amp;' : '';
		$flashvars .= ($looping) ? 'looping=true&amp;' : '';
		$flashvars .= ($showplaybutton) ? 'showplaybutton=true&amp;' : '';
		$flashvars .= ($color) ? 'colourSet='.$color : '';
		
		
		/*return '<!-- VZAAR START -->
		<div class="vzaar_media_player">
			  <object id="video" height="'.$height.'" width="'.$width.'" type="application/x-shockwave-flash" data="http://view.vzaar.com/'.$vid.'.flashplayer">
				<param name="movie" value="http://view.vzaar.com/'.$vid.'.flashplayer">	
				<param name="allowScriptAccess" value="always">
				<param name="allowFullScreen" value="true">
				<param name="wmode" value="transparent">
				<param name="flashvars" value="'.$flashvars.'">
				<embed src="http://view.vzaar.com/'.$vid.'.flashplayer" type="application/x-shockwave-flash" wmode="transparent" height="'.$height.'" width="'.$width.'" allowScriptAccess="always" allowFullScreen="true" flashvars="'.$flashvars.'">
				<video height="'.$height.'" width="'.$width.'" src="http://view.vzaar.com/'.$vid.'.mobile" poster="http://view.vzaar.com/'.$vid.'.image" controls onclick="this.play();"></video>
				</object></div>
		<!-- VZAAR END -->';*/
		
		/*return '<div class="vzaar_media_player">
				<object data="http://view.vzaar.com/'.$vid.'/flashplayer" height="'.$height.'" id="video" type="application/x-shockwave-flash" width="'.$width.'">
					<param name="allowFullScreen" value="true" />
					<param name="allowScriptAccess" value="always" />
					<param name="wmode" value="transparent" />
					<param name="movie" value="http://view.vzaar.com/'.$vid.'/flashplayer" />
					<param name="flashvars" value="showplaybutton=rollover&amp;autoplay=true&amp;looping=true&amp;colourSet=red" />
					<video controls height="'.$height.'" id="vzvid" onclick="this.play();" poster="http://view.vzaar.com/'.$vid.'/image" preload="none" src="http://view.vzaar.com/'.$vid.'/video" width="'.$width.'"></video>
				</object>
			</div>';*/
			//$vid = 914363;
			return '<div class="vzaar_media_player">
			<object height="'.$height.'" width="'.$width.'" type="application/x-shockwave-flash" id="video" data="http://view.vzaar.com/'.$vid.'/flashplayer">
			<param value="true" name="allowFullScreen" />
			<param value="always" name="allowScriptAccess" />
			<param value="transparent" name="wmode" />
			<param value="http://view.vzaar.com/'.$vid.'/flashplayer" name="movie" />
			<param value="'.$flashvars.'" name="flashvars" />
			<video height="'.$height.'" width="'.$width.'" src="http://view.vzaar.com/'.$vid.'/video" preload="none" poster="http://view.vzaar.com/'.$vid.'/image" onclick="this.play();" id="vzvid" controls=""></video>
			</object>
			</div>';//showplaybutton=rollover&amp;autoplay=true&amp;looping=true&amp;colourSet=red
			
    }
}

// let's use it
$vzaarShortcodes = new esa_Vzaar_shortcodes;    

?>