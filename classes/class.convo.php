<?php

class nmMemberConvo {
	
	var $nmconvo_db_version = "1.0";
	
	/*
	**data vars
	*/
	
	public $started_by;
	public $started_with;
	public $subject;
	public $message;
	public $files;
	
	
	/*
	** pagination vars
	*/
	
	public $convo_row_count;
	public $convo_per_page = 5;
	public $total_pages;
	public $total_convos;
	
	
	/*
	** file attachment setting vars
	*/
	public $pathUploads;
	public $multiAllow = 'false';
	public $fileLimit;
	public $fileExt;
	public $fileSize;
	
	/*
	** plugin short name
	*/
	public $short_name = 'nmconvo';
		
	
	/*
	** plugin table name
	*/
	public $tblName = 'nm_convo';
	static $static_tblName = 'nm_convo';
	
	/**
	 * the static object instace
	 */
	private static $ins = null;
	
	
	public static function get_instance()
	{
		// create a new object if it doesn't exist.
		is_null(self::$ins) && self::$ins = new self;
		return self::$ins;
	}
	
	function __construct () {
		
		// shortocde support in widget
		add_filter('widget_text', 'do_shortcode');
		
		// activate textdomain for translations
		add_action('init', array($this, 'wpp_textdomain'));
		add_shortcode( 'nmconvo', array($this, 'renderUserArea'));
		//unread box alert
		add_shortcode( 'nm-convo-alertbox', array($this, 'renderAlertBox'));
		
	
		// Ajax Actions
		add_action('wp_ajax_load_convo_detail', array($this, 'load_convo_detail'));
		add_action('wp_ajax_nmconvo_upload_file', array($this, 'upload_file'));
		
		add_action('wp_ajax_nmconvo_new_convo', array($this, 'new_convo'));
		add_action('wp_ajax_nmconvo_reply_convo', array($this, 'reply_convo'));
		add_action('wp_ajax_nmconvo_delete_convo', array($this, 'delete_convo'));
		
		// Local Hooks
		add_action('nmconvo_before_new_convo', array($this, 'render_file_attachment_new'));
		add_action('nmconvo_before_reply_convo', array($this, 'render_file_attachment_reply'));
		add_action('nmconvo_after_convo_message_sent', array($this, 'move_new_files_under_convo'), 10, 2);
	}
	
	
	function load_nmconvo_script() {
		
		//loading tempalte style
		$current_template = 'default';		//get_option('nm_convo_template');
		wp_register_style('_template_stylesheet', NMCONVO_URL.'/templates/'.$current_template.'/css/styles.css');
		wp_enqueue_style( '_template_stylesheet');
	 
	     wp_enqueue_style( 'nmconvo-css', NMCONVO_URL.'/css/nmconvo.css');
	     wp_enqueue_style( 'dashicons' );
	     
	     // Loading boostrap
	     if( nmconvo_load_bootstrap_from_plugin() ) {
	     	
	     	wp_enqueue_style('nmconvo-bootstrap', NMCONVO_URL."/css/bootstrap/bootstrap.min.css");
	    	wp_enqueue_script( 'nmconvo-bs', NMCONVO_URL.'/css/bootstrap/bootstrap.min.js', true);
	     }
	     
	     // Loading dataTable
	     if( nmconvo_load_datatables_api() ) {
	     	
	     	wp_enqueue_script( 'nmconvo-databale', NMCONVO_URL.'/js/dataTable/jquery.dataTables.min.js', true);
	    	wp_enqueue_style( 'load-dt-bs', '//cdn.datatables.net/1.10.19/css/dataTables.bootstrap4.min.css', true );
    		wp_enqueue_script( 'nmconvo-dt-bs', '//cdn.datatables.net/1.10.19/js/dataTables.bootstrap4.min.js', true);
	     }
	     
		 
		 if( get_option('nmconvo_allow_attachment') ) {
		 	
	    	wp_enqueue_script( 'nmconvo_fileapi', NMCONVO_URL.'/js/fileapi/dist/FileAPI.min.js', true);
	    	wp_enqueue_script( 'nmconvo_upload', NMCONVO_URL.'/js/nmconvo-upload.js', true, array('jquery', 'nmconvo_fileapi'));
		 }
		 
		 global $user_login;
		 wp_get_current_user();
		 
		 $nonce= wp_create_nonce  ('convo-nonce');
		 
		 wp_enqueue_script( 'nmconvo_script', NMCONVO_URL . '/js/nmconvo.js', array( 'jquery-ui-autocomplete' ) );
		 wp_localize_script( 'nmconvo_script', 'convo_vars', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ),
		 		'convo_plugin_url' => NMCONVO_URL,
		 		'convo_plugin_loading'	=> NMCONVO_URL.'/images/loading.gif',
		 		'convo_token'	=> $nonce,
		 		'current_user'	=> $user_login,
		 		'file_attached_message'	=> __(' attached successfully', 'nmconvo'),
		 		'file_size_limit'	=> get_option('nmconvo_size_limit'),
		 		'file_types'	=> get_option('nmconvo_file_ext'),
		 		'file_limit'	=> get_option('nmconvo_file_limit'),
		 		'file_limit_msg'	=> __("Max file limit reached", 'nmconvo'),
		 		'image_view_size'	=> 75,
		 		'load_datatable'	=> nmconvo_load_datatables_api(),
		 ) );
		 
	}

	function renderUserArea()
	{
		
		if ( is_user_logged_in() ) { 
		
			$this->load_nmconvo_script();
			
			nmconvo_setup_convo_page();
			
			add_action('wp_footer', function(){ 
				
				nmconvo_load_templates('modal/user-modal.php');
			} );
			
			global $current_user;
			wp_get_current_user();
			
	
			$this->applyFileAttachmentSettings();
			$this->makeUploadDirectory($current_user -> user_login);
		
			$template_vars = array('current_user'=>$current_user);
			ob_start();
			nmconvo_load_templates('_template_convo.php', $template_vars);
			$output_string = ob_get_contents();
			ob_end_clean();
			return $output_string;
		}
		else
		{
		
			/*wp_redirect( home_url() ); exit;*/
			echo '<script type="text/javascript">
			window.location = "'.wp_login_url( get_permalink() ).'"
			</script>';
		}
		
	}
	
	
	/*
	 * rendering unread messages
	*/
	
	function renderAlertBox($atts){
	
		extract(shortcode_atts(array(
				'pageurl' 		=> ''
		), $atts));
	
	
		ob_start();
		$unread = $this->unreadConvo();
		$unread = '<a href="'."{$pageurl}".'">'.$unread.'</a>';
		echo $unread;
		$output_string = ob_get_contents();
		ob_end_clean();
		return $output_string;
	
	
	}
	
	
	/*
	** this function setting is setting file attachment vars
	*/
	function applyFileAttachmentSettings()
	{
		
		$this->fileLimit = get_option('nmconvo_file_limit');
		
		if($this->fileLimit > 1)
			$this->multiAllow = "true";
			
		$this->fileExt = get_option('nmconvo_file_ext');
		
		$this->fileSize = get_option('nmconvo_size_limit');		
	}
	
	// plugin localization
	function wpp_textdomain() {
		load_plugin_textdomain('nmconvo', false, dirname(plugin_basename( __FILE__ )) . '/locale/');
	}
	
	
	/*
	** This function is making directory in follownig path
	** wp-content/uploads/user_uploads
	*/
	
	function makeUploadDirectory($user_dir)
	{
		
		$this->pathUploads = self::setup_base_dir() .'/' . $user_dir;
		if(!is_dir($this->pathUploads))
		{
			if(mkdir($this->pathUploads, 0777))
				return true;
			else
				return false;
		}
		else
		{
			return true;
		}
	}
	
	function setup_base_dir()
	{
		$upload_dir = wp_upload_dir();
	
		$base_dir = $upload_dir['basedir'] . '/user_uploads';
		if(!is_dir($base_dir))
		{
			if(mkdir($base_dir, 0777))
				return $base_dir;
			else
				die('Error while create director '.$base_dir);
		}
		else
		{
			return $base_dir;
		}
	}
	
	
	/*
	** Installing database table for this plugin: nm_convo
	*/
	public static function nmconvo_install() {
		global $wpdb;
		global $nmconvo_db_version;
	
		$table_name = $wpdb->prefix . self::$static_tblName;
		  
		$sql = "CREATE TABLE `$table_name` (
				`convo_id` INT( 8 ) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
				`started_by` INT( 7 ) NOT NULL ,
				`started_with` INT( 7 ) NOT NULL ,
				`subject` VARCHAR( 150 ) NOT NULL,
				`convo_thread` MEDIUMTEXT NOT NULL ,
				`read_by` VARCHAR( 150 ) DEFAULT '0',
				`deleted_by` VARCHAR( 150 ) DEFAULT '0',
				`last_sent_by` INT( 7 ) NOT NULL ,
				`sent_on` DATETIME NOT NULL
				);";
	
	   require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
	   dbDelta($sql);
	 
	   add_option("nmconvo_db_version", $nmconvo_db_version);
	}
	
	
	/*
	** It is making title with suject and latest message excerpt
	*/
	public function convoTitle($subject, $thread)
	{
		$thread = json_decode($thread);
		
		//Getting last message array
		$lastChunk = end($thread);
		$lastMessage = stripslashes($this->fixLengthWords($lastChunk -> message, 6));
		//print_r($lastMessage);
		
		$html = "<strong>".stripslashes($subject)."</strong>";
		$html .= "<span style=\"color:#999\"> - $lastMessage</span>";
		return $html;
	}
	
	
	/*
	** It is making Parties (buddies names)
	*/
	public function convoParties($thread, $current_user_name)
	{
		$thread = json_decode($thread);
		
		//Getting last message array
		$lastChunk = end($thread);
				
		//check it is first convo
		if($lastChunk -> username == $current_user_name)
		{
			$html = "<strong>".__('me', 'nmconvo')."</strong>, ".$lastChunk -> sentto;
		}
		else
		{
			$html = __('me', 'nmconvo') . ", <strong>".$lastChunk -> username."</strong>";
		}
			
		return $html;
	}
	
	
	function getUsersByRole() {
		
		$by_roles = get_option('nmconvo_roles');
		
		$args = array('orderby'=>'nicename');
		
		if( $by_roles ) {
			
			$args['role__in'] = $by_roles;
		}
		
		$wp_users = get_users($args);
		
		return apply_filters('nmconvo_users_list', $wp_users);
	}
	
  	/*
	** sending convo to user, NEW convo
	*/
	public function sendConvo( $send_to )
	{
		global $current_user;
		wp_get_current_user();
		
		global $wpdb;
		
		$thread[] = array( 	'username'	=> $current_user -> user_login,
							'sentto'	=> $send_to -> user_login,
							'userid'	=> $current_user -> ID,
							'message'	=> $this->message,
							'files'		=> $this->files,
							'senton'	=> time(),
						);
						
		
		$dt = array('started_by'	=> $current_user -> ID,
					'started_with'	=> $send_to->ID,
					'subject'		=> $this->subject,
					'convo_thread'	=> json_encode($thread),
					'last_sent_by'	=> $current_user -> ID,
					'sent_on'		=> current_time('mysql')
					);
					 
					 
		/*var_dump($dt);
		exit;*/
		
		$wpdb -> insert($wpdb->prefix . $this->tblName,
						$dt		
						);
		
		if($wpdb->insert_id)
			return $wpdb->insert_id;
		else
			return false;
						
		//$wpdb->print_error(); 
	}
	
	
	/*
	 * upload file
	*/
	
	function uploadFile($username){
	
	
		$upload_dir = wp_upload_dir();
		$path_folder = $upload_dir['basedir'].'/user_uploads/'.$username.'/';
	
		if (!empty($_FILES)) {
			$tempFile = $_FILES['Filedata']['tmp_name'];
			$targetPath = $path_folder;
			$targetFile = rtrim($targetPath,'/') . '/' . $_FILES['Filedata']['name'];
	
			// Validate the file type
			$fileTypes = array('jpg','jpeg','gif','png'); // File extensions
			$fileParts = pathinfo($_FILES['Filedata']['name']);
	
			if (in_array($fileParts['extension'],$fileTypes)) {
				move_uploaded_file($tempFile,$targetFile);
				echo '1';
			} else {
				echo 'Invalid file type.';
			}
		}
	}
	
	/*
	** this function getting other buddy id
	*/
	
	function getOtherBuddyID($convoID, $userID)
	{
		global $wpdb;
		
		$convo = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . $this->tblName."
								WHERE convo_id = $convoID");
								
		if($userID == $convo -> started_by)
			return $convo -> started_with;
		else
			return $convo -> started_by;
		
	}
	
	/*
	**replying convo
	*/
	
	function replyConvo($convoID, $sentTo)
	{
		global $wpdb;
		global $current_user;
		wp_get_current_user();
		
		$thread = array();
		
		$convo = $wpdb->get_row("SELECT * FROM ".$wpdb->prefix . $this->tblName."
								WHERE convo_id = $convoID");
								
		//getting chunk
		$thread = json_decode($convo -> convo_thread, true);
								
		$userinfo = get_userdata($sentTo);
		//updating chunk
		$thread[] = array( 	'username'	=> $current_user -> user_login,
							'sentto'	=> $userinfo -> user_login,
							'userid'	=> $current_user -> ID,
							'message'	=> $this->message,
							'files'		=> $this->files,
							'senton'	=> time(),
						);
		
		$dt = array('convo_thread'	=> json_encode($thread),
					'last_sent_by'	=> $current_user -> ID,
					'read_by'		=> 0,
					'sent_on'		=> current_time('mysql')
					);
		
		
		$where = array('convo_id'	=> $convoID);
		
		$res = $wpdb -> update($wpdb->prefix . $this->tblName,$dt, $where);
		
		//Now update delete_by if other user is deleted the message
		//this bug is identified by Albert Br�ckmann <mail@albertbrueckmann.de>
		//Jan 6, 2013
			
		$res_del = $wpdb->update($wpdb->prefix . $this->tblName,
				array('deleted_by'	=> 0),
				array('convo_id' 	=> $convoID)
		);
		
					
		return $res;
	}
	
	
	/*
	** Get Current User Conversations
	*/
	
	function getUserConvos()
	{
		//echo "hello";
		global $wpdb;
		global $user_ID;
		
		$myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix . $this->tblName."
						  			   WHERE (started_by = $user_ID
									   OR started_with = $user_ID)
									   AND (deleted_by != $user_ID
									   AND deleted_by NOT LIKE '$user_ID,%'
									   AND deleted_by NOT LIKE '%,$user_ID')
									   ORDER BY sent_on DESC");
		
		/*$wpdb->show_errors();
		$wpdb->print_error(); */
		
		$this->total_convos = $this->inboxCount(count($myrows));
		
		
	   	return $myrows;
	}
	
	/*
	** Get all conversations for admin view
	*/
	
	function getAllConvos()
	{
		//echo "hello";
		global $wpdb;
		global $user_ID;
		
		$myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix . $this->tblName."
						  			   ORDER BY sent_on DESC");
		
		/*$wpdb->show_errors();
		$wpdb->print_error(); */
		
		$this->total_convos = $this->inboxCount(count($myrows));
		
		
	   	return $myrows;
	}
	
	
	/*
	** Deleting convo,
	*/
	
	function deleteConvo($cid)
	{
		//echo "hello";
		global $wpdb;
		global $user_ID;
		
		/*$del = $wpdb->get_col( "SELECT deleted_by FROM ".$wpdb->prefix . $this->tblName."
						  		WHERE convo_id = $cid");*/
								
		$convo = $wpdb -> get_row("SELECT convo_thread, deleted_by 
								FROM ".$wpdb->prefix . $this->tblName."
								WHERE convo_id = $cid");
		
		//var_dump($convo);						
		//getting chunk
		$thread = json_decode($convo -> convo_thread);
		
		if($convo -> deleted_by == 0 && ! current_user_can('administrator'))
		{
			$deleted_by = $user_ID;
			
			$res = $wpdb->update($wpdb->prefix . $this->tblName,
						array('deleted_by'	=> $deleted_by),
						array('convo_id' 	=> $cid)
			);
		}
		else
		{
			// so, both buddies deleted this convo
			$res = $wpdb->query("DELETE FROM ".$wpdb->prefix . $this->tblName."
								WHERE convo_id = $cid");
								
								
			//deleting attachments
			$this->delete_convo_files($cid);
		}
		
		/*$wpdb->show_errors();
		$wpdb->print_error(); */
		
	   return $res;
	}
	
	
	
	/*
	** Get Convo Detail
	*/
	
	function getConvoDetail($convoID)
	{
		//echo "hello";
		global $wpdb;
		
		$myrows = $wpdb->get_results( "SELECT * FROM ".$wpdb->prefix . $this->tblName."
						  			   WHERE convo_id= $convoID
									   ORDER BY sent_on DESC");
	   return $myrows[0];
	}
	
	
	/*
	** Helper: getting fix lenght of string
	*/
	function fixLengthWords($pStr,$pLength)
	{
		$length = $pLength; // The number of words you want
	
		$text = strip_tags($pStr);
		/*echo $text;
		exit;*/
		$words = explode(' ', $text); // Creates an array of words	
		$words = array_slice($words, 0, $length);
		$str = implode(' ', $words);
		
		$str .= (count($words) < $pLength) ? '' : '...';
		
		return $str;
	}
	
	
	/*
	** inbox counter rendering
	*/
	
	function inboxCount($total)
	{
		if($total <= 0)
		{
			return '';
		}
		else
		{
			return "($total)";
		}
		
	}
	
	
	/*
	** getting unread messages
	*/
	
	function unreadConvo()
	{
		//getting new message counter
		global $wpdb;
		global $user_ID;
		
		$myrows = $wpdb->get_results( "SELECT COUNT(*) AS UNREAD FROM ".$wpdb->prefix . $this->tblName."
						  			   WHERE (started_by = $user_ID
									   OR started_with = $user_ID)
									   AND last_sent_by != $user_ID
									   AND read_by = 0
										AND deleted_by = 0");
		
		/*$wpdb->show_errors();
		$wpdb->print_error(); 
		print_r($myrows);*/
		
		if($myrows[0] -> UNREAD > 0)
		{
			return '<div class="nm-unread-alert">'.__('You have '.$myrows[0] -> UNREAD.' unread conversations', 'nmconvo').'</div>';
		}
	}
	
	/*
	** marking conversation as read
	*/
	function markAsRead($convo_id)
	{
		global $wpdb;
		$user = wp_get_current_user();
		
		$dt = array('read_by'	=> $user->ID);
		
		
		$where = array('convo_id'	=> $convo_id);
		/*$where = "WHERE convo_id = $convo_id
					AND last_sent_by != $user_ID";*/
		
		$res = $wpdb -> update($wpdb->prefix . $this->tblName,$dt, $where);
		
		/*$wpdb->show_errors();
		$wpdb->print_error();*/
	}
	
	
	/*
	** this is setting users array based on IDs so easily can get user info in_array index
	*/
	function setUsersByID($arrUsers)
	{
		$arr = array();
		foreach($arrUsers as $user)
		{
			$arr[$user -> ID] = $user;
		}
		
		/*echo "<pre>";
		print_r($arr);
		echo "</pre>";*/
		return $arr;
		
	}
	
	/*
	** sending user an email with instructino/attachment
	*/
	
	function send_email_notification($toEmail, $toName, $senderName, $message )
	{
		
		if( ! get_option('nmconvo_notify_user') ) 
			return '';
			
		
		$subject    = get_option('nmconvo_email_subject');
		$body       = get_option('nmconvo_email_message');
		
		$site_link	= '<a href="'.esc_url(get_bloginfo('url')).'">'.get_bloginfo('name').'</a>';
		
		$subject	= empty($subject) ? "Message received from {$senderName}" : $subject;
		$subject	= str_replace("%username%",$senderName,$subject);
		$subject	= str_replace("%site_name%",get_bloginfo('name'),$subject);
		
		$body		= empty($body) ? "Hi {$toName}, You got a new message via {$site_link}." : $body;
		
		$body	= nl2br( $body );
		
		$reply_link	= '<a href="'.esc_url(nmconvo_get_convo_page_link()).'">'.get_bloginfo('name').'</a>';
		
		$body	= str_replace("%sendername%",$senderName,$body);
		$body	= str_replace("%receivername%",$toName,$body);
		$body	= str_replace("%subject%",$this->subject ,$body);
		$body	= str_replace("%convourl%", $reply_link , $body);
		$body 	= str_replace("%message%", $message, $body);
		
		
		
		$toEmail = apply_filters('nmconvo_notification_email', array($toEmail));
		
		
		wp_mail($toEmail, $subject, $body, nmconvo_get_email_header());
	}
	

	function load_convo_detail() {
	
		$convo_id = $_REQUEST['convo_id'];
		
		$convoDetail = $this -> getConvoDetail($convo_id);

		$convo_thread = json_decode($convoDetail -> convo_thread);
		
		// Mark as read
		$this->markAsRead(intval($convo_id));
		
		ob_start();
		nmconvo_load_templates('convo-detail.php', array('convo_thread'=>$convo_thread,'convo_detail'=>$convoDetail, 'convo_id'=>$convo_id));
		$convo_detail_html = ob_get_clean();
		
		echo $convo_detail_html;
		die(0);
	}
	
	// Upload file
	function upload_file() {
			
		header ( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
		header ( "Last-Modified: " . gmdate ( "D, d M Y H:i:s" ) . " GMT" );
		header ( "Cache-Control: no-store, no-cache, must-revalidate" );
		header ( "Cache-Control: post-check=0, pre-check=0", false );
		header ( "Pragma: no-cache" );
		
		// setting up some variables
		$convo_id = isset($_REQUEST['convo_id']) ? $_REQUEST['convo_id'] : 0;
		$file_dir_path = nmconvo_get_dir_path( $convo_id );
		$response = array ();
		if ($file_dir_path == 'errDirectory') {
			
			$response ['status'] = 'error';
			$response ['message'] = __ ( 'Error while creating directory', 'nmconvo' );
			die ( 0 );
		}
		
		$file_name = '';
	
		if( isset($_REQUEST['name']) && $_REQUEST['name'] != '') {
			$file_name = sanitize_file_name( $_REQUEST['name'] );
		}elseif( isset($_REQUEST['_file']) && $_REQUEST['_file'] != '') {
			$file_name = sanitize_file_name( $_REQUEST['_file'] );
		}
	
		// Clean the fileName for security reasons
		$file_name = preg_replace ( '/[^\w\._]+/', '_', $file_name );
		$file_name = strtolower($file_name);
		
		$file_name = apply_filters('nmconvo_uploaded_filename', $file_name);
		
		
		/* ========== Invalid File type checking ========== */
		$file_type = wp_check_filetype_and_ext($file_dir_path, $file_name);
		$extension = $file_type['ext'];
	
		// for some files if above function fails to check extension we need to check otherway
		if( ! $extension ) {
			$extension = pathinfo($file_name, PATHINFO_EXTENSION);
		}
		
		// towercase 
		$extension = strtolower($extension);
		
		$allowed_types = get_option('nmconvo_file_ext');
		if( ! $allowed_types ) {
			$good_types = apply_filters('nmconvo_allowed_file_types', array('jpg', 'png', 'gif', 'zip','pdf') );
		}else {
			$good_types = explode(",", $allowed_types );
		}
		if( ! in_array($extension, $good_types ) ){
			$response ['status'] = 'error';
			$response ['message'] = __ ( 'File type not valid', 'nmconvo' );
			die ( json_encode($response) );
		}
		/* ========== Invalid File type checking ========== */
		
		$cleanupTargetDir = true; // Remove old files
	$maxFileAge = 5 * 3600; // Temp file age in seconds

	// 5 minutes execution time
	@set_time_limit ( 5 * 60 );

	// Uncomment this one to fake upload time
	// usleep(5000);

	// Get parameters
	$chunk = isset ( $_REQUEST ["chunk"] ) ? intval ( $_REQUEST ["chunk"] ) : 0;
	$chunks = isset ( $_REQUEST ["chunks"] ) ? intval ( $_REQUEST ["chunks"] ) : 0;

	

	// Make sure the fileName is unique but only if chunking is disabled
	if ($chunks < 2 && file_exists ( $file_dir_path . $file_name )) {
		$ext = strrpos ( $file_name, '.' );
		$file_name_a = substr ( $file_name, 0, $ext );
		$file_name_b = substr ( $file_name, $ext );
			
		$count = 1;
		while ( file_exists ( $file_dir_path . $file_name_a . '_' . $count . $file_name_b ) )
			$count ++;
			
		$file_name = $file_name_a . '_' . $count . $file_name_b;
	}

	// Remove old temp files
	if ($cleanupTargetDir && is_dir ( $file_dir_path ) && ($dir = opendir ( $file_dir_path ))) {
		while ( ($file = readdir ( $dir )) !== false ) {
			$tmpfilePath = $file_dir_path . $file;

			// Remove temp file if it is older than the max age and is not the current file
			if (preg_match ( '/\.part$/', $file ) && (filemtime ( $tmpfilePath ) < time () - $maxFileAge) && ($tmpfilePath != "{$file_path}.part")) {
				@unlink ( $tmpfilePath );
			}
		}
			
		closedir ( $dir );
	} else
		die ( '{"jsonrpc" : "2.0", "error" : {"code": 100, "message": "Failed to open temp directory."}, "id" : "id"}' );

	$file_path = $file_dir_path . $file_name;

	// Look for the content type header
	if (isset ( $_SERVER ["HTTP_CONTENT_TYPE"] ))
		$contentType = $_SERVER ["HTTP_CONTENT_TYPE"];

	if (isset ( $_SERVER ["CONTENT_TYPE"] ))
		$contentType = $_SERVER ["CONTENT_TYPE"];
		
	// Handle non multipart uploads older WebKit versions didn't support multipart in HTML5
	if (strpos ( $contentType, "multipart" ) !== false) {
		if (isset ( $_FILES ['file'] ['tmp_name'] ) && is_uploaded_file ( $_FILES ['file'] ['tmp_name'] )) {
			// Open temp file
			$out = fopen ( "{$file_path}.part", $chunk == 0 ? "wb" : "ab" );
			if ($out) {
				// Read binary input stream and append it to temp file
				$in = fopen ( $_FILES ['file'] ['tmp_name'], "rb" );
					
				if ($in) {
					while ( $buff = fread ( $in, 4096 ) )
						fwrite ( $out, $buff );
				} else
					die ( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}' );
				fclose ( $in );
				fclose ( $out );
				@unlink ( $_FILES ['file'] ['tmp_name'] );
			} else
				die ( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}' );
		} else
			die ( '{"jsonrpc" : "2.0", "error" : {"code": 103, "message": "Failed to move uploaded file."}, "id" : "id"}' );
	} else {
		// Open temp file
		$out = fopen ( "{$file_path}.part", $chunk == 0 ? "wb" : "ab" );
		if ($out) {
			// Read binary input stream and append it to temp file
			$in = fopen ( "php://input", "rb" );

			if ($in) {
				while ( $buff = fread ( $in, 4096 ) )
					fwrite ( $out, $buff );
			} else
				die ( '{"jsonrpc" : "2.0", "error" : {"code": 101, "message": "Failed to open input stream."}, "id" : "id"}' );

			fclose ( $in );
			fclose ( $out );
		} else
			die ( '{"jsonrpc" : "2.0", "error" : {"code": 102, "message": "Failed to open output stream."}, "id" : "id"}' );
	}

	// Check if file has been uploaded
	if (! $chunks || $chunk == $chunks - 1) {
			// Strip the temp .part suffix off
			rename ( "{$file_path}.part", $file_path );
			
			// making thumb if images
			if( nmconvo_is_file_image($file_name) ) {
			    
			    $thumb_size = apply_filters('nmconvo_image_thumb_size', 75);
				$thumb_dir_path = nmconvo_create_image_thumb($file_dir_path, $file_name, $thumb_size);
				if(file_exists($thumb_dir_path)){
					list($fw, $fh) = getimagesize( $file_path );
					$response = array(
							'file_name'			=> $file_name,
							'file_w'			=> $fw,
							'file_h'			=> $fh,
							'nocache'			=> time(),
					);
				}else{
					$response = array(
						'file_name'			=> 'ThumbNotFound',
					);
				}
			}else{
				$response = array(
						'file_name'			=> $file_name,
						'file_w'			=> 'na',
						'file_h'			=> 'na',
				);
			}
		}
		// Return JSON-RPC response
		//die ( '{"jsonrpc" : "2.0", "result" : '. json_encode($response) .', "id" : "id"}' );
		die ( json_encode($response) );
	}
	
	// Reply convo
	function new_convo() {
		
		if ( 
		    ! isset( $_POST['nonce_nmconvo_new'] ) 
		    || ! wp_verify_nonce( $_POST['nonce_nmconvo_new'], 'action_new' ) 
		) {
		
		  	$error = array('status'=>'error','message'=>__("Error while saving, try again", 'nmconvo'));
			wp_send_json($error);
		}
		
		$current_user = wp_get_current_user();
		
		$users_list = $_POST['users_list'];
		$users_list = json_decode( stripslashes($users_list) );
		
		if( count($users_list) < 1 ) {
			
			$error = array('status'=>'error','message'=>__("Please select at least one user.", 'nmconvo'));
			wp_send_json($error);
		}
		
		foreach( $users_list as $send_to ) {
			
			$other_user = get_user_by('id', $send_to);
	
			$toEmail = $started_with->user_email;
			$toName = $started_with->user_login;
		
			$this->message	= isset($_POST['message']) ? $_POST['message'] : '';
			$this->subject	= isset($_POST['subject']) ? $_POST['subject'] : '';
			$this->files	= isset($_POST['nmconvo_file']) ? $_POST['nmconvo_file'] : '';
			
		
			// sanitizing data before saving
			$this->message	= sanitize_textarea_field($this->message);
			$this->subject	= sanitize_text_field($this->subject);
			$this->files	= array_map('sanitize_file_name', $this->files);
			$convo_id		= 0;
	
			$convo_sent = 0;		
			if( $convo_id = $this->sendConvo($other_user))
			{
				$this->send_email_notification($other_user->user_email, $other_user->user_login, $current_user->user_login, $this->message);
				$convo_sent++;
				
				do_action('nmconvo_after_convo_message_sent', $convo_id, $this);
			}
		
		}
		
		if( $convo_sent > 0 ) {
			
			$message = get_option('nmconvo_sent_message');
			$message = empty($message) ? __("{$convo_sent} message(s) sent successfully", 'nmconvo') : $message;
			
			$response = array('status'=>'success','message'=>$message);
			
			wp_send_json($response);
				
		}else {
		
			$error = array('status'=>'error','message'=>__("Error return, try again", 'nmconvo'));
			wp_send_json($error);
		}
	}
	
	// Reply convo
	function reply_convo() {
		
		if ( 
		    ! isset( $_POST['nonce_nmconvo_reply'] ) 
		    || ! wp_verify_nonce( $_POST['nonce_nmconvo_reply'], 'action_reply' ) 
		) {
		
		  	$error = array('status'=>'error','message'=>__("Error while saving, try again", 'nmconvo'));
			wp_send_json($error);
		}
		
		$current_user = wp_get_current_user();
		
		
		$this->message =  isset($_POST['reply_message']) ? $_POST['reply_message'] : '';
		$this->files 	= isset($_POST['nmconvo_file']) ? $_POST['nmconvo_file'] : '';
		
		
		// sanitizing data before saving
		$this->message = sanitize_textarea_field($this->message);
		$this->files = array_map('sanitize_file_name', $this->files);
		$convo_id	= intval($_POST['convo_id']);
		
		$receiverID = $this->getOtherBuddyID($convo_id, $current_user->ID);
		
		if($this->replyConvo($convo_id, $receiverID))
		{
			
			$receiver_info = get_userdata($receiverID);
			//var_dump($receiver_info);
			
			$this->send_email_notification($receiver_info -> user_email, 
												 $receiver_info -> user_login,
												 $current_user -> user_login,
												 $this->message);
			
			// echo "<div class=\"green\">". get_option($this->short_name.'_sent_message') ."</div>";
			
			$message = get_option('nmconvo_sent_message');
			$message = empty($message) ? __("Message sent successfully", 'nmconvo') : $message;
			
			$response = array('status'=>'success','message'=>$message);
			
			wp_send_json($response);
		} else {
			
			$error = array('status'=>'error','message'=>__("Error return, try again", 'nmconvo'));
			wp_send_json($error);
		}
	}
	
	function delete_convo() {
		
		if ( 
		    ! isset( $_POST['nonce_nmconvo_delete'] )
		    || ! wp_verify_nonce( $_POST['nonce_nmconvo_delete'], 'nmconvo-del-nonce' ) 
		) {
		
		  	$error = array('status'=>'error','message'=>__("Error while saving, try again", 'nmconvo'));
			wp_send_json($error);
		}
		
		$convo_id	= intval($_POST['convo_id']);
		
		$response = array();
		
		if($res = $this->deleteConvo($convo_id)) {
			
			$message = get_option('nmconvo_delete_message');
			$message = empty($message) ? __("Message deleted successfully", 'nmconvo') : $message;
			
			$response = array('status'=>'success','message'=>$message);
		}
		
		wp_send_json($response);
	}
	
	
	// Render file attachment area in convo front end
	function render_file_attachment_new() {
		
		if( ! get_option('nmconvo_allow_attachment') )
			return '';
			
		nmconvo_load_templates('file-attachment-new.php');
	}
	
	function render_file_attachment_reply() {
		
		if( ! get_option('nmconvo_allow_attachment') )
			return '';
			
		nmconvo_load_templates('file-attachment-reply.php');
	}
	
	// Moving new files from 0/ directory to relevant convo directory
	function move_new_files_under_convo( $convo_id, $convo ) {
		
		if( count($convo->files) < 1 ) return '';
		
		foreach($convo->files as $file_name) {
			
			// source path
			$source_path = nmconvo_get_dir_path( 0 ) . $file_name;
			
			if ( ! file_exists($source_path) ) continue;
			
			$destination_path = nmconvo_get_dir_path( $convo_id ) . $file_name;
			
			
			if( rename( $source_path, $destination_path) ) {
				
				// now moving it's thumb
				$thumb_src_path = nmconvo_get_dir_path( 0 ) . "thumbs/{$file_name}";
				
				if( file_exists( $thumb_src_path) ) {
					
					$thumb_dest_path = nmconvo_get_dir_path( $convo_id ) . "thumbs/{$file_name}";
					rename( $thumb_src_path, $thumb_dest_path);	
				}
			}
			
		}
	}
	
	
	// Delete files against convo
	function delete_convo_files( $convo_id ) {
		
		$source_path = nmconvo_get_dir_path( $convo_id );
		$it = new RecursiveDirectoryIterator($source_path, RecursiveDirectoryIterator::SKIP_DOTS);
		$files = new RecursiveIteratorIterator($it,
		             RecursiveIteratorIterator::CHILD_FIRST);
		foreach($files as $file) {
		    if ($file->isDir()){
		        rmdir($file->getRealPath());
		    } else {
		        unlink($file->getRealPath());
		    }
		}
		
		rmdir($source_path);
	}
   
}


// ==================== INITIALIZE PLUGIN CLASS =======================
//
add_action('plugins_loaded', 'NMCONVO');
//
// ==================== INITIALIZE PLUGIN CLASS =======================

function NMCONVO(){
	return nmMemberConvo::get_instance();
}