<?php
/**
 * User convo list template
 * */
 
 if( ! defined("ABSPATH") ) die("Not Allowed");
 
 ?>
 
 <div id="wpbody-content">
     
     <h2>Members Conversation List</h2>

     <table class="nmconvo-table wp-list-table widefat fixed striped pages">
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
		$convo_inline_content = '';
		
        foreach($user_convos as $convo) {
            
            $started_by = get_user_by('id', $convo -> started_by);
            $started_by = ( $started_by ) ? $started_by->display_name : 'Unknow User';

            $started_with = get_user_by('id', $convo -> started_with);
            $started_with = ( $started_with ) ? $started_with->display_name : 'Unknow User';
            
            
            $parties    = $started_by . ', ' . $started_with;
            
			$subject_detail  = NMCONVO()->convoTitle($convo -> subject, $convo -> convo_thread);
			
			$date   = date('M-d,y i:s', strtotime($convo->sent_on));
			
			$unread_class   = '';
			$envelope       = 'envelope';
			
			$attachment_css = '';
			if( $total_attachments = nmconvo_has_attachment( $convo ) ) {
			    
			    $attachment_css = '<span class="dashicons dashicons-admin-links"></span> ('.$total_attachments.')';
			}
			
			// Loading convo detail in thickboxed div
			$convo_div_id = 'nmconvo-detail-'.$convo->convo_id;
			
			$convo_inline_content .= '<div id="'.esc_attr($convo_div_id).'" style="display:none">';
			    $convo_inline_content .= '<div>';
			    
    			    $arrConvo = json_decode($convo->convo_thread);
    			    $template_vars = array('convo_detail'=>$arrConvo, 'convo_id'=>$convo->convo_id);
    			    ob_start();
    			    $template_path =  NMCONVO_PATH . "/templates/admin/single-convo.php";
                    if( file_exists( $template_path ) ){
                        
                        extract( $template_vars );
                    	include ( $template_path );
                    }
                    
    		        $convo_detail_html = ob_get_clean();
    		        
    		        $convo_inline_content .= $convo_detail_html;
    		        
		        $convo_inline_content .= '</div>';
		    $convo_inline_content .= '</div>';
        ?>
        
            <tr class="nmcovo-row-<?php echo esc_attr($convo->convo_id);?>">
                <td data-convoid="<?php echo esc_attr($convo->convo_id);?>" class="nmcnovo-msg-row"><?php echo $parties;?></td>
                <td data-convoid="<?php echo esc_attr($convo->convo_id);?>" class="nmcnovo-msg-row" class="<?php echo $unread_class;?>"><?php echo $subject_detail;?></td>
                <td data-convoid="<?php echo esc_attr($convo->convo_id);?>" class="nmcnovo-msg-row"><?php echo $date;?></td>
                <td data-convoid="<?php echo esc_attr($convo->convo_id);?>" class="nmcnovo-msg-row"><?php echo $attachment_css;?></td>
                
                <!-- Actions -->
                <td>
                    <a data-delnoce="<?php echo $del_nonce;?>" data-convoid="<?php echo esc_attr($convo->convo_id);?>" href="#" class="nmconvo-msg-item-del"><span class="dashicons dashicons-trash"></span></a>  
                    <a href="#TB_inline?width=600&height=550&inlineId=<?php echo esc_attr($convo_div_id);?>" class="thickbox"><span class="dashicons dashicons-admin-comments"></span></i></a>
                </td>
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
        
        <?php
        
        // Inline Thicbox content
        echo $convo_inline_content;
        ?>
 </div>