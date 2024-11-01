<?php
/**
 * NMCONVO Options/Settings
 * */

if( ! defined('ABSPATH') ) die( "Not Allowed" );

$nmconvo_setting_age_title = "N-Media Member Conversation";
$convo_shortname = 'nmconvo';

$nm_convo_options = nmconvo_options_array();
	

if ( isset($_REQUEST['saved']) ) echo '<div id="message" class="updated fade"><p><strong>'.$nmconvo_setting_age_title.' '.__('Settings saved.', 'nmconvo').'</strong></p></div>';
if ( isset($_REQUEST['reset']) ) echo '<div id="message" class="updated fade"><p><strong>'.$nmconvo_setting_age_title.' '.__('Settings reset.', 'nmconvo').'</strong></p></div>';
if ( isset($_REQUEST['reset_widgets']) ) echo '<div id="message" class="updated fade"><p><strong>'.$nmconvo_setting_age_title.' '.__('Widgets reset.', 'nmconvo').'</strong></p></div>';

$post_admin = admin_url('admin-post.php?action=nmconvo_save_settings');
?>

<div class="wrap rm_wrap">
<h2><?php echo $nmconvo_setting_age_title; ?> Settings</h2>

<div class="rm_opts">
<form method="post" action="<?php echo esc_url($post_admin);?>">

<?php 
foreach ($nm_convo_options as $value) {
	
	
	$type	= isset( $value['type'] ) ? $value['type'] : '';
	$id 	= isset( $value['id'] ) ? $value['id'] : '';
	$name	= isset( $value['name'] ) ? $value['name'] : '';
	$desc	= isset( $value['desc'] ) ? $value['desc'] : '';
	
	$options= isset( $value['desc'] ) ? $value['desc'] : '';
	
	$value = isset( $value['std'] ) ? $value['std'] : '';
	
	$saved_value = get_option( $id, true );
	
	if ( $saved_value != "" ) { 
			$value = ! is_array($saved_value) ? stripslashes( $saved_value ) : $saved_value;
	}
	
	switch ( $type ) {
	
	case "open":
	?>
	
	<?php break;
	
	case "close":
	?>
	
	</div>
	</div>
	<br />
	
	<?php break;
	
	case "title":
	?>
	
	<?php break;
	
	case 'text':
	?>
	
	<div class="rm_input rm_text">
		<label for="<?php echo esc_attr($id); ?>"><?php _e($name,  'nmconvo') ?></label>
	 	<input name="<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id); ?>" type="<?php echo esc_attr($type); ?>" value="<?php echo esc_attr($value) ?>" />
	 <small><?php _e($desc,  'nmconvo') ?></small><div class="clearfix"></div>
	
	 </div>
	<?php
	break;
	
	case 'number':
	?>
	
	<div class="rm_input rm_text">
		<label for="<?php echo esc_attr($id); ?>"><?php _e($name,  'nmconvo') ?></label>
	 	<input name="<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id); ?>" type="<?php echo esc_attr($type); ?>" value="<?php echo esc_attr($value) ?>" />
	 <small><?php _e($desc,  'nmconvo') ?></small><div class="clearfix"></div>
	
	 </div>
	<?php
	break;
	
	case 'textarea':
	?>
	
	<div class="rm_input rm_textarea">
		<label for="<?php echo esc_attr($id); ?>"><?php _e($name,  'nmconvo') ?></label>
	 	<textarea name="<?php echo esc_attr($id); ?>" type="<?php echo esc_attr($type); ?>"><?php echo $value;?></textarea>
	 <small><?php _e($desc,  'nmconvo') ?></small><div class="clearfix"></div>
	 </div>
	
	<?php
	break;
	
	case 'html':
	?>
	
	<div class="rm_input">
		<label for="<?php echo esc_attr($id); ?>"><?php _e($name,  'nmconvo') ?></label>
	 	<?php echo $desc;?>
	 <div class="clearfix"></div>
	 </div>
	
	<?php
	break;
	
	case 'select':
	?>
	
	<div class="rm_input rm_select">
		<label for="<?php echo esc_attr($id); ?>"><?php _e($name,  'nmconvo') ?></label>
	
	<select name="<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id); ?>">
	<?php foreach ($value['options'] as $option) { ?>
			<option <?php selected( $value, $option); ?>><?php echo $option; ?></option>
	<?php } ?>
	</select>
	
		<small><?php _e($desc,  'nmconvo') ?></small><div class="clearfix"></div>
	</div>
	<?php
	break;
	
	case "checkbox":
	?>
	
	<div class="rm_input rm_checkbox">
		<label for="<?php echo esc_attr($id); ?>"><?php _e($name,  'nmconvo') ?></label>
	
	<?php 
	if( $value ){ 
		$checked = "checked=\"checked\""; 
	}else{ 
		$checked = "";
	} ?>
	
	<input type="checkbox" name="<?php echo esc_attr($id); ?>" id="<?php echo esc_attr($id); ?>" value="true" <?php echo $checked; ?> />
		<small><?php _e($desc,  'nmconvo') ?></small><div class="clearfix"></div>
	 </div>
	<?php break;
	
	case "roles":
		
		$existnig_roles = empty($value) ? array() : $value;
		?>
	
	<div class="rm_input rm_checkbox user-roles">
		<label for="<?php echo esc_attr($id); ?>"><?php _e($name,  'nmconvo') ?></label>
	
		
	<?php 
		if(in_array('all', $existnig_roles)){ 
			$checked = "checked=\"checked\""; 
		}else{ 
			$checked = "";
		}
		?>
	<input type="checkbox" name="<?php echo esc_attr($id); ?>[]" id="r-all" value="all" <?php echo $checked; ?> /><?php _e('All',  'nmconvo')?>
	
	<?php foreach (get_editable_roles() as $role_name => $role_info) { 
		if(in_array($role_name, $existnig_roles)){
			$checked = "checked=\"checked\"";
		}else{ $checked = "";
		}
		?>
		<input type="checkbox" name="<?php echo esc_attr($id); ?>[]" id="<?php echo esc_attr($id); ?>" value="<?php echo esc_attr($role_name)?>" <?php echo $checked; ?> /> <?php echo $role_info['name']?>
	<?php }?>
	
	
		<small><?php _e($desc,  'nmconvo') ?></small><div class="clearfix"></div>
	 </div>
	<?php break;
	case "section":
	
	?>
	
	<div class="nmconvo_section">
	<div class="rm_title">
		<h3>
			<img src="<?php echo NMCONVO_URL.'/css/images/trans.png';?>" class="inactive"><?php _e($name,  'nmconvo') ?>
		</h3>
		<span class="submit"><input class="button button-primary" name="save" type="submit" value="<?php _e('Save Changes',  'nmconvo')?>" />
		
	</span><div class="clearfix"></div></div>
	<div class="rm_options">
	
	<?php break;
	
	}
}
?>

<input type="hidden" name="nmconvo_action" value="save" />
</form>
<form method="post">
<p class="submit">
<input class="button" name="nmconvo_reset" type="submit" value="<?php _e('Reset',  'nmconvo')?>" />
<input type="hidden" name="nmconvo_action" value="reset" />
</p>
</form>
<div style="font-size:9px; margin-bottom:10px;">2018 © <a href="http://www.najeebmedia.com">N-Media</a></div>
</div>

<div class="nm-about-us">
	<a href="https://jetpack.com/pricing/?aff=8683"><img src="<?php echo esc_url(NMCONVO_URL.'/images/inline-rectangle.png');?>" alt="Jetpack" title="Jetpack" /></a>
</div>