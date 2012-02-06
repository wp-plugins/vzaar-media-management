<?php  
if(preg_match('#' . basename(__FILE__) . '#', $_SERVER['PHP_SELF'])) { die('You are not allowed to call this page directly.'); }

	function esa_vzaar_media_management( $msg = '' )  {	
		global $wpdb, $vzaar;
		
		//echo '<pre>';var_dump($_POST);echo '</pre>';
		$vmsg = '';
		
		//Vzaar API Info Block
		require_once(dirname (__FILE__) . '/vzaar/Vzaar.php');
		
		$options = get_option('vzaar_options');
		Vzaar::$token = $options['vzaar_apikey']; 
		Vzaar::$secret = $options['vzaar_apisecret'];
		
		//Set Return URL
		$redirect_url = site_url( "wp-admin/admin.php?page=vzaarMedia-add", 'admin');
		
		//Find API Signature
		$uploadSignature = Vzaar::getUploadSignature($redirect_url);
		$signature = $uploadSignature['vzaar-api'];
		
		//Set Media Title/Description
		$title = (isset($_POST['title'])) ? addslashes($_POST['title']) : '';
		$description = (isset($_POST['description'])) ? addslashes($_POST['description']) : '';
		
		//Video Add Block
		if (isset($_GET['guid'])) {
			$apireply = Vzaar::processVideo($_GET['guid'], $title, $description, 1);   //print_r('Video ID: '.$apireply); 
			$vmsg .= 'Uploaded Media ID: ' . $apireply . ', Vzaar is processing your media file so it will take few muments to show in your media list.';
		}
		
		//Video Update Block
		if( (isset($_POST['update_media'])) && ($_POST['update_media']== 'Update') ){
			$res = Vzaar::editVideo($_POST['id'], $title, $description);   //print_r($res);
			$vmsg .= 'Media ID: ' . $_POST['id'] . ' updated successfully.';
		}
		
		//Video Edit/Delete/Bulk Delete Block
		if( (isset($_GET['action'])) && ($_GET['action']== 'edit_video') ){
			esa_edit_vzaar_video_info($_GET['vid'], $redirect_url);
			die();
			
		}else if( (isset($_GET['action'])) && ($_GET['action']== 'delete_video') ){
			$res = Vzaar::deleteVideo($_GET['vid']); //print_r($res);
			$vmsg .= 'Media ID: ' . $_GET['vid'] . ' deleted successfully.';
			
		}else if( (isset($_POST['bulkaction'])) && ($_POST['bulkaction']== 'Delete') ){
			$video_ids = array();
			$video_ids = $_POST['bulkcheck'];
			foreach($video_ids as $id  ){
				$res = Vzaar::deleteVideo($id); //print_r($res);
			}
			$vmsg .= 'Media ID(s): ' . implode(', ', $video_ids) . ' deleted successfully.';
		}

		//Set Info to Retrive Media Info from Vzaar
		$title = (isset($_POST['s']) && $_POST['s'] != 'Title') ? $_POST['s'] : '';
		$labels = '';
		$count = 50;
		$page = 1;
		$sort = 'desc';
		//$status = 9;
		
		//Init Call to Vzaar for Data
		if($options['vzaar_apisecret'] != 'xxxx' && $options['vzaar_apisecret'] != 'yyyy'){
			$video_list = Vzaar::searchVideoList($options['vzaar_apisecret'], 'true', $title, $labels, $count, $page, $sort);
		}
		
		//$process_video_list = Vzaar::getVideoList($options['vzaar_apisecret'], false, $count, $labels, $status);
		//$video_detail = Vzaar::getVideoDetails($apireply); 
		//echo '<pre>';var_dump($video_list);echo '</pre>';
	?>
	<div class="wrap">
	<?php if(!empty($vmsg)){echo '<div class="updated fade" id="message"><p>'.$vmsg.'</p></div>';}?>
	<h2><?php _e('Upload Media', 'vzaarvideos') ;?></h2>
	
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

		<br /><span>Choose Media *</span><br />
		<input class="mendatory" name="file" type="file">
		
		<input type="submit" value="Upload Media" class="button"><br />
		( file supported by vzaar <i>asf, avi, flv, m4v, mov, mp4, m4a, 3gp, 3g2, mj2, wmv, mp3</i> )
	</form>
		<br />

	<hr style="color:#f7f7f7;" />
	
	
	
	<h2><?php _e('Media List', 'vzaarvideos') ;?></h2>
	<form id="record_form" method="post" action="<?php echo $_SERVER['PHP_SELF']; ?>?page=vzaarMedia-add">
        <div class="tablenav">
            <div class="alignleft actions">
                <input type="submit" name="bulkaction" value="Delete" onclick="return confirm('Are you sure you want to delete these records?');" class="button-secondary" />
                
                &nbsp;&nbsp;Search by:
                <input type="text" name="s" id="media-search-input" value="Title" onfocus="if(value=='Title') value = ''" onblur="if(value=='') value = 'Title'" />
            	<input type="submit" class="button" value="Search"  />
			</div>
		 </div>
		<table class="widefat">
            <thead>
				<tr>
					<th class="check-column"><input type="checkbox" onclick="record_form_checkAll(document.getElementById('record_form'));" /></th>
					<th>Media</th>
					<th>Media ID</th>
					<th>Title</th>
					<th width="30" align="center">Views</th>
					<!--<th>Description</th>-->
					<th width="30">Duration</th>
				</tr>
			</thead>
			<tfoot>
				<tr>
					<th class="check-column"><input type="checkbox" onclick="record_form_checkAll(document.getElementById('record_form'));" /></th>
					<th>Media</th>
					<th>Media ID</th>
					<th>Title</th>
					<th width="30" align="center">Views</th>
					<!--<th>Description</th>-->
					<th width="30">Duration</th>
				</tr>
			</tfoot>
            
            <tbody id="the-list">
			<?php $i = 0;
				if(!empty($video_list)){
					foreach((array)$video_list as $video){
			?>
				<tr>
					<th scope="row" class="check-column"><input type="checkbox" name="bulkcheck[]" value="<?php echo $video->id; ?>" /></th>
					<td><?php echo '<a href="'.$video->url.'" target="_blank"><img src="'.$video->thumbnail.'" title="'.$video->title.'" alt="'.$video->title.'" border="0"></a>'; ?></td>
					<td>
					<?php echo '<a href="'. $redirect_url .'&amp;action=edit_video&amp;vid='. $video->id .'" >'.$video->id.'</a><div class="row-actions"><span class="edit">
				<a href="'. $redirect_url .'&amp;action=edit_video&amp;vid='. $video->id .'" class="edit">Edit</a> | 
				<a href="'. $redirect_url .'&amp;action=delete_video&amp;vid='. $video->id .'" onclick="return confirm(\'Are you sure you want to delete this record?\');" class="delete">Delete</a> 
			</span></div>'; ?>
			
					</td>
					<td><?php echo $video->title; ?></td>
					<td align="center"><?php echo $video->playCount; ?></td>
					<?php /*?><td><?php echo $video->description; ?></td><?php */?>
					<td><?php echo $video->duration; ?></td>
				</tr>
			<?php
					}
				}else{
					echo '<tr><td colspan="5">No data found</td></tr>';
				}
			?>
			</tbody>
        </table>
		
	

	<?php
	
	}

	//Media Info Edit Form
	function esa_edit_vzaar_video_info($vid, $action_url){
		$video_info = Vzaar::getVideoDetails($vid, true);
		
	?>
	<div class="wrap">
		<h2><?php _e('Edit Media', 'vzaarvideos') ;?></h2>
		<form action="<?php echo $action_url;?>" name="media_upload_form" class="landing_page2_cont" enctype="multipart/form-data" method="post" onsubmit="return chkValidity(this);">
			<div class="form_action2">
				<br /><span>Media ID</span><br />
				<input class="mendatory" type="text" name="id" value="<?php echo $vid; ?>" size="64" readonly="true" />
				<br /><br /><span>Media Title *</span><br />
				<input class="mendatory" type="text" name="title" id="title" value="<?php echo $video_info->title; ?>" size="64" />
				<br /><br /><span>Media Description </span><br />
				<input class="" type="text" name="description" id="description" value="<?php echo $video_info->description; ?>" size="64" />
				<br /><br />
				<input type="submit" name="update_media" id="update_media" value="Update" />
				<br /><br />
			</div>
		</form>
	</div>
	
	<?php
	}
?>