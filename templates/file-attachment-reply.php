<?php
/**
 * File attachment template
 * */
 
 if (! defined("ABSPATH") ) die("Not Allowed");
 
 ?>
 
<div class="nmconvo_upload_files_wrapper">

	
	<div class="form-group row">
	
		<div class="col-sm-2"></div>
		
		<div class="col-sm-10">
			
			<!-- Uploaded files container -->
			<div id="nmconvo-file-list-reply"></div>
			
			
			<span class="btn btn-success nmconvo-new-select-wrapper nmconvo-reply-attachment" >
				<label for="filechooser_reply" class="filechooser_lebel">
					<?php 
					$select_file_label = apply_filters('nmconvo_select_file_label', "Select File", 'nmconvo');
					printf(__('%s', 'nmconvo'), $select_file_label);
					?>
					<input style="display:none;" type="file" id="filechooser_reply" multiple accept="file_extension" />
				</label>
			</span>
		</div>
	
	</div>

</div>