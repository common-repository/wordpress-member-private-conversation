<?php
/**
 * Admin related stuff
 **/

if( ! defined('ABSPATH') ) die( "Not Allowed" );

add_action('admin_menu' , 'nmconvo_add_admin');
function nmconvo_add_admin() {

    $nmconvo_setting_page_title = "N-Media Member Conversation";
    
    $main_page = add_menu_page( 	$nmconvo_setting_page_title, 	
					"WP Convo", 
					'edit_plugins', 
					'nmconvo', 
					'nmconvo_option_page', 
					NMCONVO_URL.'/images/option.png');
					
	$listconvo_menu = add_submenu_page( 'nmconvo',
					  'Convo List', 
					  'Convo List', 
					  'manage_options', 
					  'nmconvo-list', 
					  'nmconvo_list_all_convos');
					  
	// Hooking the scripts
	add_action( 'admin_print_styles-' . $main_page, 'nmconvo_option_add_css' );
    add_action( 'admin_print_scripts-' . $main_page, 'nmconvo_option_add_js' );

	add_action( 'admin_print_styles-' . $listconvo_menu, 'nmconvo_listconvo_css' );
}

function nmconvo_option_add_css() {
    
    $option_css = NMCONVO_URL.'/css/options.css';
  	wp_enqueue_style('nm_convo_option_style', $option_css);
	
}

function nmconvo_listconvo_css() {
    
    $option_css = NMCONVO_URL.'/css/admin.css';
  	wp_enqueue_style('nm_convo_option_style', $option_css);
	
}

function nmconvo_option_add_js() {
    
    $option_js  = NMCONVO_URL.'/js/nmconvo-options.js';
	wp_enqueue_script("nm_convo_script", $option_js, false, "1.0"); 
}

/*
** User conversation list
*/
function nmconvo_list_all_convos()
{
    
	wp_enqueue_style( 'nmconvo-css', NMCONVO_URL.'/css/nmconvo.css');
	
	if( nmconvo_load_datatables_api() ) {
     	
     	wp_enqueue_style('nmconvo-databale', NMCONVO_URL."/js/dataTable/datatables.min.css");
    	wp_enqueue_script( 'nmconvo-databale', NMCONVO_URL.'/js/dataTable/jquery.dataTables.min.js', true);
     }
     
     $nonce= wp_create_nonce  ('convo-nonce');
		 
	 wp_enqueue_script( 'nmconvo_script', NMCONVO_URL . '/js/nmconvo.js', array( 'jquery-ui-autocomplete' ) );
	 wp_localize_script( 'nmconvo_script', 'convo_vars', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),
	 		'convo_plugin_url' => NMCONVO_URL,
	 		'convo_plugin_loading'	=> NMCONVO_URL.'/images/loading.gif',
	 		'convo_token'	=> $nonce,
	 		'file_attached_message'	=> __(' attached successfully', 'nmconvo'),
	 		'file_size_limit'	=> get_option('nmconvo_size_limit'),
	 		'file_types'	=> get_option('nmconvo_file_ext'),
	 		'file_limit'	=> get_option('nmconvo_file_limit'),
	 		'file_limit_msg'	=> __("Max file limit reached", 'nmconvo'),
	 		'image_view_size'	=> 75,
	 		'load_datatable'	=> nmconvo_load_datatables_api(),
	 ) );
	 
	 add_thickbox();

	$user_convo_arr = NMCONVO()->getAllConvos();
	nmconvo_load_templates('admin/all-convos.php', array('user_convos'=>$user_convo_arr) );
}

// Save settings posted
add_action( 'admin_post_nmconvo_save_settings', 'nmconvo_save_settings' );
function nmconvo_save_settings() {

    $nmconvo_shortname  = 'nmconvo';
    $nm_convo_options   = nmconvo_options_array();

    // nmconvo_pa($_REQUEST); exit;
    if ( current_user_can('administrator') ) {
    
        if ( isset($_REQUEST['nmconvo_action']) &&  'save' == $_REQUEST['nmconvo_action'] ) {
    
        	
                foreach ($nm_convo_options as $value) {
                    
                    if( ! isset($value['id']) ) continue;
                    
                    $option_name    = $value['id'];
                    $option_value   = isset($_REQUEST[ $option_name ]) ? $_REQUEST[ $option_name ] : '';
                    
                    //Sanitization
                    
                    switch( $option_name ) {
                        
                        case 'nmconvo_roles':
                            $option_value = array_map('sanitize_text_field', $option_value);
                        break;
                        
                        case 'nmconvo_email_message':
                            $option_value   = sanitize_textarea_field($option_value);
                        break;
                        
                        default:
                            $option_value   = sanitize_text_field($option_value);
                        break;
                        
                    }
                
                    update_option( $option_name, $option_value ); 
                    
                }
                
                $setting_page = admin_url("admin.php?page={$nmconvo_shortname}&saved=true");
                wp_redirect( $setting_page );
                exit;

        } else if( isset($_REQUEST['nmconvo_action']) && 'reset' == $_REQUEST['nmconvo_action'] ) {

            foreach ($nm_convo_options as $value) {
                delete_option( $option_name ); }

            $setting_page = admin_url("admin.php?page={$nmconvo_shortname}&reset=true");
            wp_redirect( $setting_page );
            exit;

        } 
    }

}



function nmconvo_option_page() {
    
    nmconvo_load_templates('admin/options.php');
}

// Admin options array
function nmconvo_options_array() {
    
    $nmconvo_shortname = 'nmconvo';
    $nmconvo_setting_page_title = "N-Media Member Conversation";
    
    $get_pro    = '<a class="button button-primary" href="https://najeebmedia.com/wordpress-plugin/wordpress-front-end-member-private-message-with-file-attachment/">Get Pro Version</a>';
    
    $nm_convo_options = array (

    array( "name" => $nmconvo_setting_page_title." Options",
    	"type" => "title"),
    
    	array( 	"name" => __("Convo Settings", 'nmconvo'),	
    		"type" => "section"),	
    		array( "type" => "open"),
    		
    		array( 	"name" => __("Conversation Sent ", 'nmconvo'),
    		  		"desc" => __("A message after conversations is delivered", 'nmconvo'),
    				"id" => $nmconvo_shortname."_sent_message",
    				"type" => "text",
    				"std" => "Conversation is sent successfully"),
    				
    	   	 array( 	"name" => __("Conversation Deleted", 'nmconvo'),
    		  		"desc" => __("A message after conversations is deleted", 'nmconvo'),
    				"id" => $nmconvo_shortname."_delete_message",
    				"type" => "text",
    				"std" => "Conversation(s) deleted successfully"),
    				
    		array(  "name" => __("Notify User?", 'nmconvo'),
        				"desc" => __("Send notification user when he receive message", 'nmconvo'),
        				"id" => $nmconvo_shortname."_notify_user",
        				"type" => "checkbox",
        				"std"	=> ''),
        				
        				
        	array(  "name" => __("Disable Bootsrap", 'nmconvo'),
        				"desc" => __("If your theme already has Bootsrap loaded, please turn it off", 'nmconvo'),
        				"id" => $nmconvo_shortname."_off_bootstrap",
        				"type" => "checkbox",
        				"std"	=> ''),
    	  			
        	array(  "name" => __("More Features?", 'nmconvo'),
        				"desc" => $get_pro,
        				"id" => $nmconvo_shortname."_notify_user",
        				"type" => "html",
        				"std"	=> ''),
        				
		array( "type" => "close"),
    		
    
    );	//end of nm_convo_options array
    
    return apply_filters( 'nmconvo_options_array', $nm_convo_options);
}
