<?php
/**
 * Share modal
 **/
 
 $to_users = NMCONVO()->getUsersByRole();
 
if ( ! defined('ABSPATH') ) die ('Not Allowed');
?>
<div class="modal fade" id="nmconvo-user-modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="nmconvo-user-form">
                <input type="hidden" name="action" value="nmconvo_share_file"/>
                <input type="hidden" name="nmconvo_file_id" id="nmconvo_file_id">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title"><?php _e('Selec Users', 'nmconvo');?></h4>
            </div>

            <div class="modal-body">

            <table class="nmconvo-table table table-striped">
                <thead>
                    <tr>
                        <th><?php _e('Select', 'nmconvo');?></th>
                        <th><?php _e('User Name', 'nmconvo');?></th>
                    </tr>
                </thead>
                
                <tbody>
                <?php
                foreach($to_users as $user) {
                
                $user_fullname = apply_filters('nmconvo_username_list', $user->display_name, $user);
                ?>    
                
                    <tr>
                        <td><input type="checkbox" id="nmconvo-list-<?php echo esc_attr($user->ID);?>" class="form-checkbox" data-username="<?php echo esc_attr($user_fullname);?>" value="<?php echo esc_attr($user->ID);?>"></td>
                        <td><label for="nmconvo-list-<?php echo esc_attr($user->ID);?>"><?php echo $user_fullname;?></label></td>
                    </tr>
                
                <?php
                }
                ?>
                </tbody>
                
                <tfoot>
                    <tr>
                        <th><?php _e('Select', 'nmconvo');?></th>
                        <th><?php _e('User Name', 'nmconvo');?></th>
                    </tr>
                </tfoot>
                
            </table>
            <!-- Dynamic contents -->
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-white" data-dismiss="modal"><?php _e('Close', 'nmconvo');?></button>
                <button type="submit" class="btn btn-info"><?php _e('Select Users', 'nmconvo'); ?></button>
            </div>
            </form>
        </div>
    </div>
</div>