<?php
/**
 * User convo list template
 * */
 
 if( ! defined("ABSPATH") ) die("Not Allowed");
 
 ?>
 
 <div id="nmconvo-user-convo-list-wrapper">

     <table class="nmconvo-table table table-striped table-hover">
        <thead>
            <tr>
                <th><?php _e('User', 'nmconvo');?></th>
                <th><?php _e('Subject & Detail', 'nmconvo');?></th>
                <th><?php _e('Date', 'nmconvo');?></th>
                <th><?php _e('Attachments', 'nmconvo');?></th>
                <th><?php _e('Action', 'nmconvo');?></th>
            </tr>
        </thead>
        
        <tbody>
        <?php
        
        $del_nonce = wp_create_nonce( 'nmconvo-del-nonce' );
        foreach($user_convos as $convo) {
            
            $parties= NMCONVO()->convoParties( $convo -> convo_thread, $current_user->user_login);
			$title  = NMCONVO()->convoTitle($convo -> subject, $convo -> convo_thread);
			$date   = date('M-d,y i:s', strtotime($convo->sent_on));
			
			$unread_class   = '';
			if($convo->last_sent_by != $current_user->ID and $convo->read_by != $current_user->ID) {
			    
				$unread_class   = 'unread';
			}
			
			$attachment_css = '';
			if( $total_attachments = nmconvo_has_attachment( $convo ) ) {
			    
			    $attachment_css = '<span class="dashicons dashicons-paperclip"></span> ('.$total_attachments.')';
			}
        ?>
        
            <tr class="nmcovo-row-<?php echo esc_attr($convo->convo_id);?>">
                <td data-convoid="<?php echo esc_attr($convo->convo_id);?>" class="nmcnovo-msg-row"><?php echo $parties;?></td>
                <td data-convoid="<?php echo esc_attr($convo->convo_id);?>" class="nmcnovo-msg-row" class="<?php echo $unread_class;?>"><?php echo $title;?></td>
                <td data-convoid="<?php echo esc_attr($convo->convo_id);?>" class="nmcnovo-msg-row"><?php echo $date;?></td>
                <td data-convoid="<?php echo esc_attr($convo->convo_id);?>" class="nmcnovo-msg-row"><?php echo $attachment_css;?></td>
                
                <!-- Actions -->
                <td>
                    <a data-delnoce="<?php echo $del_nonce;?>" data-convoid="<?php echo esc_attr($convo->convo_id);?>" href="#" class="nmconvo-msg-item-del"><i title="<?php _e("Delete",'nmconv');?>" class="dashicons dashicons-trash"></i></a>  
                <a href="#" data-convoid="<?php echo esc_attr($convo->convo_id);?>" class="nmconvo-msg-item"><span title="<?php _e("Open",'nmconv');?>" class="dashicons dashicons-admin-comments"></span></a></td>
            </tr>
            
        <?php
        }
        ?>
        
        </tbody>
        
        <tfoot>
            <tr>
                <th><?php _e('User', 'nmconvo');?></th>
                <th><?php _e('Subject & Detail', 'nmconvo');?></th>
                <th><?php _e('Date', 'nmconvo');?></th>
                <th><?php _e('Attachments', 'nmconvo');?></th>
                <th><?php _e('Action', 'nmconvo');?></th>
            </tr>
        </tfoot>
        
        </table>
 </div>