var scriptAr = new Array(); // initializing the javascript array

jQuery(function($) {
	

	if( convo_vars.load_datatable ) {
		// DataTable
		$(".nmconvo-table").DataTable();
	}

	$("#nmconvo-create").click(function() {

		/* hiding panel */
		$("#inbox-panel, #nmconvo-create-panel, #convo-history-panel").hide();

		/* what to show */
		$("#" + this.id + "-panel").slideDown();

	});
	
	// Cancel reply
	$("#convo-wrapper").on('click', ".nmconvo-cancel-reply, .nmconvo-cancel-new", function(e){
		
		e.preventDefault();
		/* hiding panel */
		$('#nmconvo-create-panel,#convo-history-panel').hide();
		
		$('#inbox-panel').show();
		
	});

	// select all
	$("#convo-select-all").click(
			function() {
				// alert(current_page);
				$("ul.nm-c-p-" + current_page).find(':checkbox').attr(
						'checked', this.checked);

			});

	/* auto complate */
	$("#tags").autocomplete({
		source : scriptAr
	});
	
	
	/*
	 * checking if user is allow to communicate
	 */
	
	$('input[name^="started_with"]').blur(function(){
		
		if(jQuery.inArray($(this).val(), scriptAr) == -1){
			
			$(this).val('');			
		}
	});
	
	// Send new
	$("#nmconvo-new-form").on('submit', function(e) {
		
		e.preventDefault();
		$(".nmconvo-sending-message").show();
		
		var data = $(this).serialize();
		
		$.post(convo_vars.ajaxurl, data, function(resp) {
			
			var msg_class = (resp.status === 'success') ? 'alert alert-success' : 'alert alert-danger';
			$(".nmconvo-sending-message").html(resp.message).addClass(msg_class);
			
		}, 'json');
	})
	
	// Submit reply
	$("#nmconvo-reply-form").on('submit', function(e) {
		
		e.preventDefault();
		$(".nmconvo-sending-message").show();
		
		var data = $(this).serialize();
		
		$.post(convo_vars.ajaxurl, data, function(resp) {
			
			var msg_class = (resp.status === 'success') ? 'alert alert-success' : 'alert alert-danger';
			
			$(".nmconvo-sending-message").html(resp.message).addClass(msg_class);
			
			
		}, 'json');
	});
	
	// Selecting users from popup form
	$("#nmconvo-user-form").on('submit', function(e) {
	    
	    e.preventDefault();
	    
	    var user_container = $("#nmconvo-selected-users-list");
	    	
    	// Reset container
    	user_container.html('');
	    	
	    var user_array = [];
	    
	    var users_selected = $(this).find('input[type="checkbox"]:checked');
	    $.each(users_selected, function(i, item){
	    	
	    	var user_id = $(item).val();
	    	var user_name = $(item).data('username');
	    	
	    	var user_item = $('<span/>')
	    					.addClass('nmconvo-user-name')
	    					.prop('id', 'nmconvo-user'+user_id)
	    					.html(user_name)
	    					.appendTo(user_container);
	    					
	    	user_array.push( user_id )
	    	
	    });
	    
	    $("#users_list").val( JSON.stringify(user_array) );
	});
	
	
	// Load convo detail
	$("#convo-wrapper").on('click', '.nmconvo-msg-item, .nmcnovo-msg-row', function(e) {
	    
	    e.preventDefault();
	    var convo_id = $(this).data('convoid');
	    nmconvo_load_convo_detail( convo_id );
	    
	    //Hiding convos
	    nmconvo_hide_inbox();
	});
	
	// Deleting message
	$(".nmconvo-msg-item-del").on('click', function(e) {
	    
	    e.preventDefault();
	    var convo_id = $(this).data('convoid');
	    var del_nonce = $(this).data('delnoce');
	    
	    var a = confirm('Are you sure?');
	    if ( !a ){
	    	return;
	    }
	    
	    var data = {action:'nmconvo_delete_convo',convo_id:convo_id,nonce_nmconvo_delete:del_nonce};
		
		$.post(convo_vars.ajaxurl, data, function(resp) {
			
			if(resp.status === 'success'){
				
				$(".nmcovo-row-"+convo_id).remove();
			}	
		}, 'json');
	});

});

function nmconvo_hide_inbox() {
	
	jQuery("#inbox-panel").hide();
}

function nmconvo_show_inbox() {
	
	jQuery("#inbox-panel").show();
}

function nmconvo_back_inbox() {
	
	nmconvo_show_inbox();
	
	// Clear current message detail
	jQuery(".nm-convo-detail").html('');
	jQuery("#convo-history-panel").hide();
}

/* reply validation */
function validateReply(){
		error=0;
		
		if(jQuery("#nm-reply").val()==""){
			jQuery("#nm-reply").css("border-color", "red");
			jQuery("#reply_err").show().fadeOut(12000);
			error=1;
		} 
		
		if(error==1) return false; else return true;
		
}

/* new message validation */
function validateCompose(){
		error=0;
		if(jQuery("#tags").val()==""){
			 jQuery("#tags").css("border-color", "red");
			jQuery("#start_with_err").show().fadeOut(10000);
			error=1;
		} 

		if(jQuery("#subject").val()==""){
			jQuery("#subject").css("border-color", "red");
			jQuery("#subject_err").show().fadeOut(11000);
			error=1;
		} 
		
		if(jQuery("#message").val()==""){
			jQuery("#message").css("border-color", "red");
			jQuery("#message_err").show().fadeOut(12000);
			error=1;
		} 
		
		if(error==1) return false; else return true;
		
}

/*
 * This is loading message history
 */
function nmconvo_load_convo_detail( c_id ) {
	
	// reset convo detail
	jQuery("#history-heading, #convo-detail-container").html('');

	// but showing me
	jQuery("#convo-history-panel, #nmconvo-loading-convo").show();

	// setting title of pop up
	var t = jQuery("#convo-" + c_id).find("li.title").html();

	// binding convo id value to reply form hidden id field
	jQuery("#reply-c-id").val(c_id);

	var data = {
		action : 'load_convo_detail',
		convo_id : c_id
	};
	
	jQuery.post(convo_vars.ajaxurl, data, function(response) {
		// alert('Got this from the server: ' + response);
		jQuery("#convo-detail-container").html(response);
		jQuery("#nmconvo-loading-convo").hide();
	});
	
	jQuery("#history-heading").html(t);
	
}

