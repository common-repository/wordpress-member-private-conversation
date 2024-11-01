<?php
/**
 * Load convo detail 
 * 
 **/
 
 if( ! defined("ABSPATH") ) die("Not Allowed");

?>

<ul class="nm-convo-detail">
<?php 
	foreach($convo_detail as $c):
	$title = $c -> username . __(' wrote on ').date('M-d,Y', $c->senton);
	
?>
    	<li class="convo-head"><?php echo stripslashes($title)?></li>
        <li class="convo-text"><?php echo stripslashes($c -> message)?></li>
        
        
        <?php
		if(count($c -> files) > 0)
		{
			echo '<li class="convo-attachment">
				<strong>['.count($c -> files).'] Files Attachment:</strong><br />';
			foreach($c -> files as $file_name):
				
				$file_path = nmconvo_get_dir_path($convo_id).$file_name;
				if( ! file_exists($file_path) ) continue;
				
				$thumb_url = nmconvo_get_dir_url($convo_id, true).$file_name;
				$download_url = nmconvo_get_dir_url($convo_id).$file_name;
				
				echo '<p class="nmconvo-file-preview">';
				if( nmconvo_is_file_image($file_name) ){
					echo '<img src="'.esc_url($thumb_url).'">';
				}
				echo '<a class="nmconvo-file-name" href="'.esc_url($download_url).'" target="_blank">'.$file_name.'</a>';
				echo '</p>';
				
			endforeach;
			echo '</li>';
			
			echo '<span class="nmconvo-clear"></span>';
		}
		?>
        	
<?php 
	endforeach;
?>  
</ul>
 