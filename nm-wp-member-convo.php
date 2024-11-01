<?php
/*
Plugin Name: Nmedia Member Private Conversation Plugin
Plugin URI: http://www.najeebmedia.com
Description: Enable members to send and receive messages with file attachments in a WordPress site.
Version: 2.1
Author: Najeeb Ahmad
Author URI: http://www.najeebmedia.com/
*/

define('NMCONVO_PATH', untrailingslashit(plugin_dir_path( __FILE__ )) );
define('NMCONVO_URL', untrailingslashit(plugin_dir_url( __FILE__ )) );
define('NMCONVO_UPLOAD_DIR_NAME', 'nmconvo');

if( file_exists( dirname(__FILE__).'/inc/helpers.php' )) include_once dirname(__FILE__).'/inc/helpers.php';
if( file_exists( dirname(__FILE__).'/classes/class.convo.php' )) include_once dirname(__FILE__).'/classes/class.convo.php';


if( is_admin() ) {
	if( file_exists( dirname(__FILE__).'/inc/admin.php' )) include_once dirname(__FILE__).'/inc/admin.php';
}

// Creating table
register_activation_hook(__FILE__, array('nmMemberConvo','nmconvo_install'));


function convo_post_file(){

	nmMemberConvo::uploadFile($_REQUEST['username']);

	die(0);
}

add_action( 'wp_ajax_nopriv_convo_file', 'convo_post_file' );
add_action( 'wp_ajax_convo_file', 'convo_post_file' );