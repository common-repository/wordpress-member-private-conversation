"use strict"
var nmconvoFiles = [];
jQuery(function($) {
  // console.log(FileAPI);
	if( FileAPI != undefined ) {
	    
    	FileAPI.event.on(filechooser_reply, 'change', function (evt){
          
            nmconvo_file_selected( evt, 'reply' );
        });
        
        FileAPI.event.on(filechooser_new, 'change', function (evt){
          
            nmconvo_file_selected( evt, 'new' );
        });
        
        // setting up uploader
        // console.log(convo_vars.file_drag_drop);
        var dnd_el = document.getElementById('upload_files_btn_area');
        FileAPI.event.dnd(dnd_el, function (over){
            dnd_el.style.backgroundColor = over ? '#f60': '';
        }, function (files){
            if( files.length ){
                nmconvo_selected_images_preview(files);
                nmconvo_upload_files(files);
            }
        });
    
	}

});

function nmconvo_file_selected( evt, area ) {
    
    var files = FileAPI.getFiles(evt); // Retrieve file list
          
    FileAPI.filterFiles(files, function (file, info/**Object*/){
      
    var size_in_bytes = parseInt(convo_vars.file_size_limit) * 1024 * 1024;
    
    if( file.size <= size_in_bytes){
        
        return true;
        
    }else{
        var msg_local = convo_vars.messages.file_settings_error +
        ' Max Filesize: ' + convo_vars.max_file_size;
        
        alert(msg_local);
        return false;
    }
    
    
    return true;
    }, function (files/**Array*/, rejected/**Array*/){
    
    console.log(files);
    
    if( files.length && files.length <= convo_vars.file_limit){
        
      nmconvo_selected_images_preview(files, area);
      
      nmconvo_upload_files(files, area);
    }else{
        
        alert(convo_vars.file_limit_msg);
        return false;
    }
    });
}

/** 
 * Display Images Preview
 */
 function nmconvo_selected_images_preview(files, area){
     
      var fileList = jQuery("#nmconvo-file-list-"+area);
    
      FileAPI.each(files, function (file){
          fileList.css({
            "margin-top" : "10px",
            "margin-bottom" : "10px"
          })
          var class_name = file.name.replace(/[^a-zA-Z0-9]/g, "");
          //div with 3 column to hold image
          var new_row = jQuery('<div/>', { 
            class: 'row '+class_name
          }).appendTo(fileList);
          
          console.log(new_row);
          
        if( ! /^image/.test(file.type) ){
            
            var thumb_holder = jQuery('<div/>', { 
                    class: 'col-sm-3 text-center'
                }).appendTo(new_row).append(file.name);
                
            //progressbar holder
            var fileprogress_holder = jQuery('<div/>', {
               class: 'progress',
            }).appendTo(thumb_holder)
            .append('<div class="progress-bar"></div>');
            
        }else{
            
            FileAPI.Image(file).preview(convo_vars.image_view_size).get(function (err, img){
            
                var thumb_holder = jQuery('<div/>', { 
                    class: 'col-sm-3 text-center'
                }).appendTo(new_row).append(img);
                
                //progressbar holder
                var fileprogress_holder = jQuery('<div/>', {
                   class: 'progress',
                }).appendTo(thumb_holder)
                .append('<div class="progress-bar"></div>');
                
            });
        console.log(thumb_holder);
        
        }
        nmconvoFiles.push(file);
      });
}

/**
 * upload all files to local server
 * 
 * @since 10.5
 */
function nmconvo_upload_files( uploadFiles, area ) {
        
    // Uploading Files
    var convo_id = (area == 'new') ? 0 : jQuery("#reply-c-id").val();
    
      FileAPI.upload({
        url: convo_vars.ajaxurl + '?action=nmconvo_upload_file&convo_id='+convo_id,
        files: {file: uploadFiles},
        fileprogress: function (evt, file){ 
            var class_name = file.name.replace(/[^a-zA-Z0-9]/g, "");
            jQuery('.'+class_name+' .progress').show();
            var percent = parseInt((evt.loaded / evt.total * 100));
            jQuery('.'+class_name+' .progress .progress-bar').css('width', percent+'%');
            jQuery('.'+class_name+' .progress .progress-bar').text(percent+'%');
        },
        complete: function (err, xhr){
            if(err == false){
                
                var response = jQuery.parseJSON(xhr.response);
                if( response.status === 'error' ) {
					alert( response.message );
					window.location.reload();
					
				}
                jQuery('.file_upload_button').hide();
                jQuery('.new_file_upload').show();
                jQuery('#nmconvo-save-file-btn').show();
                jQuery('#nmconvo-files-wrapper').hide();

                
            } else {
                alert('There is some Error...');
            }               
        },
        
        filecomplete: function (err/**String*/, xhr/**Object*/, file/**Object/, options/**Object*/){
            
            if( !err ){
              // File successfully uploaded
                //console.log(file);
                var class_name = file.name.replace(/[^a-zA-Z0-9]/g, "");
                jQuery('.'+class_name+' .progress .progress-bar').text('Uploaded!');
            
                
              var append_to = (area == 'new') ? 'nmconvo-new-form' : 'nmconvo-reply-form';    
              
              var result = JSON.parse(xhr.responseText);
              var file_upload = jQuery("<input/>")
                                .attr('type','hidden')
                                .attr('name','nmconvo_file[]')
                                .val(result.file_name)
                                .appendTo("#"+append_to);
              
            }
          }
      });
}

/* pagination */
function loadConvoPageNext()
{
	jQuery("#nm-convo-container ul li.nm-c-p-"+current_page++).hide();
	jQuery("#nm-convo-container ul li.nm-c-p-"+current_page).show();
	setPagination();
}

function loadConvoPagePrev()
{
	jQuery("#nm-convo-container ul li.nm-c-p-"+current_page--).hide();
	jQuery("#nm-convo-container ul li.nm-c-p-"+current_page).show();
	setPagination();
}


function loadConvoCurrentPage()
{
	
	//showing inbox panel
  	jQuery("#inbox-panel").show();
	
	//hiding history/detail panel me
	jQuery("#convo-history-panel").hide();
	
	//loading current page
	
	jQuery("#nm-convo-container ul li.nm-c-p-"+current_page).show();
	setPagination();
}
