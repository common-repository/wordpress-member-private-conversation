<?php
/**
 * NMCONVO Helper Functions
 **/

if( ! defined('ABSPATH') ) die( "Not Allowed" );

function nmconvo_pa( $arr ) {
    
    echo '<pre>';
    print_r($arr);
    echo '<pre>';
}

// loading template files
function nmconvo_load_templates( $template_name, $vars = null) {
    if( $vars != null && is_array($vars) ){
    extract( $vars );
    }

    $template_path =  NMCONVO_PATH . "/templates/{$template_name}";
    if( file_exists( $template_path ) ){
    	include_once( $template_path );
    } else {
     die( "Error while loading file {$template_path}" );
    }
}

// Return upload file dir path
function nmconvo_get_dir_path($convo_id) {
	
	$nmconvo_upload_dir = nmconvo_files_setup_get_directory( $convo_id );
	return apply_filters('nmconvo_dir_path', $nmconvo_upload_dir);
}

// Return upload file dir url
function nmconvo_get_dir_url( $convo_id, $thumb=false ) {
	
	$upload_dir = wp_upload_dir ();		
	$return_url = '';
	if ( $thumb ) {
		$return_url = $upload_dir ['baseurl'] . '/' . NMCONVO_UPLOAD_DIR_NAME . '/' . $convo_id .'/'. '/thumbs/';
	}	else {
		$return_url = $upload_dir ['baseurl'] . '/' . NMCONVO_UPLOAD_DIR_NAME . '/' . $convo_id .'/';
	}
		
	return apply_filters('nmconvo_dir_url', set_url_scheme( $return_url ));
}

// Check if given filenameis image
function nmconvo_is_file_image( $file_name ){
	
	$type = strtolower ( substr ( strrchr ( $file_name , '.' ), 1 ) );
	if (($type == "gif") || ($type == "jpeg") || ($type == "png") || ($type == "pjpeg") || ($type == "jpg"))
		return true;
	else
		return false;
}

// Creating thumb for image
function nmconvo_create_image_thumb( $file_path, $image_name, $thumb_size ) {
    
    $wp_image = wp_get_image_editor ( $file_path . $image_name );
    $image_destination = $file_path . 'thumbs/' . $image_name;
    if (! is_wp_error ( $wp_image )) {
    	$wp_image -> resize ( $thumb_size, $thumb_size, true );
    	$wp_image -> save ( $image_destination );
    }
    
    return $image_destination;
}

// Set/create directory and return path
function nmconvo_files_setup_get_directory( $convo_id ) {
    
    $upload_dir = wp_upload_dir ();
		
	global $user_login;
    wp_get_current_user();
    
	$parent_dir = $upload_dir ['basedir'] . '/' . NMCONVO_UPLOAD_DIR_NAME . '/' . $convo_id .'/';
	$thumb_dir  = $parent_dir . 'thumbs/';
	
	if(wp_mkdir_p($parent_dir)){
    	if(wp_mkdir_p($thumb_dir)){
    		return $parent_dir;
    	}
	}
}

// check if convo has attachment
function nmconvo_has_attachment( $convo ) {
	
	$convo_thread = json_decode($convo->convo_thread);
	
	$total_attachments = 0;
	foreach( $convo_thread as $convo ) {
		
		$total_attachments += count($convo->files);
	}
	
	return ($total_attachments > 0) ? $total_attachments : false;
}

// load datatable
function nmconvo_load_datatables_api() {
	
	return apply_filters('nmconvo_load_datatable', false);
}

// load bootstrap
function nmconvo_load_bootstrap_from_plugin() {
	
	$allow = true;
	
	if( get_option('nmconvo_off_bootstrap') )
    	$allow = false;
    	
	return apply_filters('nmconvo_load_bootstrap', $allow);
}

function nmconvo_get_email_header($from_email='') {
    
    $site_title = get_bloginfo('name');
	$admin_email = $from_email == '' ? get_bloginfo('admin_email') : $from_email;
	
	$headers[] = "From: {$site_title} <{$admin_email}>";
	$headers[] = "Content-Type: text/html";
	$headers[] = "MIME-Version: 1.0\r\n";
	
	return $headers;
}

function nmconvo_setup_convo_page() {
	
	global $post;
	
	if( $post )
		update_option('nmconvo_convo_page', $post->ID);
}


function nmconvo_get_convo_page_link() {
	
	$convo_page_url = get_bloginfo('url');
	
	$convo_page_id = get_option('nmconvo_convo_page');
	if( $convo_page_id ) {
		
		$convo_page_url = get_permalink( $convo_page_id );
	}
	
	return apply_filters('nmconvo_convo_page_url', $convo_page_url);
}