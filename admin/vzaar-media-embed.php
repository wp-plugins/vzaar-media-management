<?php

/**
 * @title  Add action/filter for the upload tab 
 * @author Enamul
 */

function esa_vzaar_wp_upload_tabs ($tabs) {
	$newtab = array('vzaarmedia' => __('Vzaar Media','vzaarVIDEOS'));
    return array_merge($tabs,$newtab);
}
add_filter('media_upload_tabs', 'esa_vzaar_wp_upload_tabs');

function media_upload_vzaarmedia() {
    // Not in use
    $errors = false;
	// Generate TinyMCE HTML output
	if ( isset($_POST['send']) ) {
		$sendA	= array_keys($_POST['send']);
		$vidKey	= (string) array_shift($sendA);
		
		// Build output:
		$html = '[vzaarmedia vid="'.$vidKey.'" height="'.$_POST['video'][$vidKey]['height'].'"  width="'.$_POST['video'][$vidKey]['width'].'" color="'.$_POST['video'][$vidKey]['color'].'"]';
		
		// Return it to TinyMCE
		return media_send_to_editor($html);
	}
		
	return wp_iframe( 'media_upload_vzaarmedia_form', $errors );
}
add_action('media_upload_vzaarmedia', 'media_upload_vzaarmedia');


function media_upload_vzaarmedia_form($errors) {
	global $type, $tab, $vzaar;
	 
	media_upload_header();
	$post_id 	= intval($_REQUEST['post_id']);
	$picarray 	= array();
	$vmsg 		= '';

	//Vzaar API Info Block
	require_once(dirname (__FILE__) . '/vzaar/Vzaar.php');
		
	$options = get_option('vzaar_options');
	Vzaar::$token = $options['vzaar_apikey']; 
	Vzaar::$secret = $options['vzaar_apisecret'];
	
	//Set Return URL
	$redirect_url = site_url( "wp-admin/media-upload.php?post_id=1&tab=vzaarmedia", 'admin');
	
	//Find API Signature
	$uploadSignature = Vzaar::getUploadSignature($redirect_url);
	$signature = $uploadSignature['vzaar-api'];
	
	//Set Media Title/Description
	$title = (isset($_POST['title'])) ? addslashes($_POST['title']) : '';
	$description = (isset($_POST['description'])) ? addslashes($_POST['description']) : '';
	
	//Video Add Block
	if (isset($_GET['guid'])) {
		$apireply = Vzaar::processVideo($_GET['guid'], $title, $description, 1);   //print_r('Video ID: '.$apireply); 
		$vmsg .= 'Uploaded Media ID: ' . $apireply . ', Vzaar is processing your media file so it will take few minutes to show in your media list.';
	}

	
	?>
	<div style="padding-right:17px;">
	<?php if(!empty($vmsg)){echo '<div class="updated fade" id="message"><p>'.$vmsg.'</p></div>';}?>
	<h3>Upload a video</h3>
	<form action="https://<?php echo $signature['bucket'];?>.s3.amazonaws.com/" method="post" enctype="multipart/form-data" onsubmit="return chkValidity(this);">
		<!--<input name="content-type" type="hidden" value="binary/octet-stream" />-->
		<input type="hidden" name="acl" value="<?php echo $signature['acl']; ?>">
		<input type="hidden" name="bucket" value="<?php echo $signature['bucket']; ?>">
		<input type="hidden" name="policy" value="<?php echo $signature['policy']; ?>">
		<input type="hidden" name="AWSAccessKeyId" value="<?php echo $signature['accesskeyid']; ?>">
		<input type="hidden" name="signature" value="<?php echo $signature['signature']; ?>">
		<input type="hidden" name="success_action_status" value="201">
		<input type="hidden" name="success_action_redirect" value="<?php echo $redirect_url; ?>&guid=<?php echo $signature['guid']; ?>">
		<input type="hidden" name="key" value="<?php echo $signature['key']; ?>">

		<span>Choose Media *</span><br />
		<input class="mendatory" name="file" type="file">
		
		<input type="submit" value="Upload Media" class="button"><br />
		( file supported by vzaar <i>asf, avi, flv, m4v, mov, mp4, m4a, 3gp, 3g2, mj2, wmv, mp3</i> )
	</form>
	
    
    <hr />
    
	<form method="get" id="filter">
        <input type="hidden" value="video" name="type" />
        <input type="hidden" value="vzaarmedia" name="tab" />
        <input type="hidden" value="<?php echo $post_id; ?>" name="post_id" />
        <input type="hidden" value="video" name="post_mime_type" />
        <p class="search-box" id="media-search">
            
            <input type="text" name="s" id="media-search-input" value="Title" onfocus="if(value=='Title') value = ''" onblur="if(value=='') value = 'Title'" />
            <input type="submit" class="button" value="Search Media" />
        </p>
    </form>
    
	<p>&nbsp;</p>
	
    
    
	<?php wp_nonce_field('vzaar-media-form'); ?>

	<script type="text/javascript">
	<!--
	function chkValidity(form, location){
		var reqFields = jQuery(form).find(".mendatory");
		var err=false;
		
		//check for empty fields:
		for(i=0; i < reqFields.length; i++){ // ERR = NULL or ERR_MSG or DEFAULT_SELECT
			if( reqFields[i].value=='' || (reqFields[i].value==0 && reqFields[i].tagName.toUpperCase()=='SELECT') ){//SELECT TAG but has initial value to '0':
				err=true;
				if( jQuery(reqFields[i]).attr('type') == 'hidden' ){
					err = false; continue; //do nothing;
				}
				else if( jQuery(reqFields[i]).attr('type') == 'file' )
					alert("Please choose media file(s).");
				else if( reqFields[i].tagName.toUpperCase() == 'SELECT' ){//SELECT TAG but no initial value:
					alert('Please complete selecting [ '+jQuery(form).find("label[for='"+reqFields[i].name+"']").text()+' ]');
				}
				else{
					var alertMsg = (location=='sidebar')? 'Please complete all fields' : 'Please complete the required fields marked with (*)';
					alert(alertMsg);
				}
				break;
			}
		}
		return (err)? false : true;
	}


	jQuery(function($){
		var preloaded = $(".media-item.preloaded");
		if ( preloaded.length > 0 ) {
			preloaded.each(function(){prepareMediaItem({id:this.id.replace(/[^0-9]/g, '')},'');});
			//updateMediaForm();
		}
	});
	-->
	</script>

<form enctype="multipart/form-data" method="post" action="<?php echo esc_attr($form_action_url); ?>" class="media-upload-form validate" id="library-form">	
	<div id="media-items">
	<?php
	
	$title = ($_GET['s'] != 'Title') ? $_GET['s'] : ''; 
	$labels = '';
	$count = 20;
	$page = 1;
	$sort = 'desc';
	
	//$video_list = Vzaar::getVideoList($options['vzaar_apisecret'], false, 20);
	if($options['vzaar_apisecret'] != 'xxxx' && $options['vzaar_apisecret'] != 'yyyy'){
		$video_list = Vzaar::searchVideoList($options['vzaar_apisecret'], 'true', $title, $labels, $count, $page, $sort);
	}
	
	if( !empty($video_list) ) {
		foreach ($video_list as $i => $video){
			$video_detail = Vzaar::getVideoDetails($video->id); 
			
			?>
			<div id="media-item-<?php echo ($i+1); ?>" class="media-item preloaded">
			  <strong class='filename'><?php echo $video->title; ?></strong>
			  <a class='toggle describe-toggle-on' href='#'><?php esc_attr( _e('Show', "vzaarVIDEOS") ); ?></a>
			  <a class='toggle describe-toggle-off' href='#'><?php esc_attr( _e('Hide', "vzaarVIDEOS") );?></a>
			  <table class="slidetoggle describe startclosed"><tbody>
              	  <tr>
					<td rowspan='2'><?php echo '<a href="'.$video->url.'" target="_blank"><img src="'.$video->thumbnail.'" title="'.$video->title.'" alt="'.$video->title.'" border="0"></a>'; ?></td>
					<td><strong><?php esc_attr( _e('Key:', "vzaarVIDEOS") ); ?></strong> <?php echo $video->id; ?></td>
				  </tr>
				  <td><strong><?php esc_attr( _e('Duration:', "vzaarVIDEOS") ); ?></strong> <?php echo $video->duration; ?></td>
				  <?php if($video_detail->description != '' ){?>
				  <tr><td><strong><?php esc_attr( _e('Description:', "vzaarVIDEOS") ); ?></strong> <?php echo $video_detail->description; ?></td></tr>
				   <?php }else{ }?>
					<tr class="align">
						<td class="label"><label for="video[<?php echo $video->id ?>][height]"><strong><?php esc_attr_e("Height","vzaarVIDEOS"); ?></strong></label></td>
						<td class="field" style="text-align:left">
                        	<input type="text" name="video[<?php echo $video->id ?>][height]" id="video[<?php echo $video->id ?>][height]" value="<?php echo $video->height;?>" />
						</td>
					</tr>
					
					<tr class="align">
						<td class="label"><label for="video[<?php echo $video->id ?>][width]"><strong><?php esc_attr_e("Width","vzaarVIDEOS"); ?></strong></label></td>
						<td class="field" style="text-align:left">
                        	<input type="text" name="video[<?php echo $video->id ?>][width]" id="video[<?php echo $video->id ?>][width]" value="<?php echo $video->width;?>" />
						</td>
					</tr>
					
					<tr class="align">
						<td class="label"><label for="video[<?php echo $video->id ?>][color]"><strong><?php esc_attr_e("Player Color","vzaarVIDEOS"); ?></strong></label></td>
						<td class="field" style="text-align:left">
							<select name="video[<?php echo $video->id ?>][color]" id="video[<?php echo $video->id ?>][color]">
								<option value="black" <?php if($vzaar->options['vzaar_player_color'] == 'black'){echo ' selected="selected"';} ?>>Black</option>
								<option value="blue" <?php if($vzaar->options['vzaar_player_color'] == 'blue'){echo ' selected="selected"';} ?>>Blue</option>
								<option value="red" <?php if($vzaar->options['vzaar_player_color'] == 'red'){echo ' selected="selected"';} ?>>Red</option>
								<option value="green" <?php if($vzaar->options['vzaar_player_color'] == 'green'){echo ' selected="selected"';} ?>>Green</option>
								<option value="yellow" <?php if($vzaar->options['vzaar_player_color'] == 'yellow'){echo ' selected="selected"';} ?>>Yellow</option>
								<option value="pink" <?php if($vzaar->options['vzaar_player_color'] == 'pink'){echo ' selected="selected"';} ?>>Pink</option>
								<option value="orange" <?php if($vzaar->options['vzaar_player_color'] == 'orange'){echo ' selected="selected"';} ?>>Orange</option>
								<option value="brown" <?php if($vzaar->options['vzaar_player_color'] == 'brown'){echo ' selected="selected"';} ?>>Brown</option>
							</select>
						</td>
					</tr>
					
                    <tr class="align">
						<td class="label" valign="top"><label for="video[<?php echo $video->id ?>][embededcodeS]"><strong><?php esc_attr_e("Embed Code","vzaarVIDEOS"); ?></strong></label></td>
						<td class="field" style="text-align:left">
							<textarea cols="60" rows="7" readonly="readonly" name="video[<?php echo $video->id ?>][embededcodeS]" id="video[<?php echo $video->id ?>][embededcode]"><?php echo htmlspecialchars($video_detail->html);?></textarea>
                            <small>Copy-paste this code to your website to embed the video.</small>
						</td>
					</tr>
				   <tr class="submit">
						<td></td>
						<td class="savesend">
							<button type="submit" class="button" value="1" name="send[<?php echo $video->id ?>]"><?php esc_html_e( 'Insert into Post' ); ?></button>
						</td>
				   </tr>
			  </tbody></table>
			</div>
		<?php		  
		}
	}else{
		echo '<tr class="align"><td colspan="2">No data found</td></tr>';
	}
	?>
	</div>
	
    <input type="hidden" name="type" value="<?php echo esc_attr( $GLOBALS['type'] ); ?>" />
	<input type="hidden" name="tab" value="<?php echo esc_attr( $GLOBALS['tab'] ); ?>" />
	<input type="hidden" name="post_id" id="post_id" value="<?php echo (int) $post_id; ?>" />
</form>
</div>
<?php
}
?>