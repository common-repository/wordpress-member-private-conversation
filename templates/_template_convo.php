<?php
/*
** This is main temlate for loading covosersation, 
do not change until you are like me (ceo@najeebmedia.com)
*/

//rendering box if unread convo
echo NMCONVO()->unreadConvo();
$user_convo_arr = NMCONVO()->getUserConvos();

$convo_limit = get_option('nmconvo_convo_limit');
$convo_per_page = !empty($convo_limit) ? 3 : intval($convo_limit);
$convo_total_pages = ceil(count($user_convo_arr) / $convo_per_page);

// nmconvo_pa($user_convo_arr);

?>

<div id="convo-wrapper">
  
  
  <div id="inbox-panel">
      <header class="nmconvo-inbox-header">
        
        <span id="nmconvo-create" class="nmconvo-create-btn">
          <span class="dashicons dashicons-plus-alt"> </span> <?php _e('Create', 'nmconvo');?>
        </span>
        
        <h2><?php _e('Inbox', 'nmconvo')?></h2>
        
      </header>
      
      <?php
      if(count($user_convo_arr) == 0):
          echo '<p class="nm-notification">'.__("Inbox is empty", 'nmconvo').'</p>';
      else:
      ?>
      
        <?php
        /**
         * user convo list
         * */
        nmconvo_load_templates('user-convos-list.php', array('user_convos'=>$user_convo_arr,'current_user'=>$current_user) );
        
        endif;
        ?>
    
    </div>  <!-- inobox-panel -->
 
 
  <div id="nmconvo-create-panel" style="display:none">
  
  <form id="nmconvo-new-form">
        
        <input type="hidden" name="action" value="nmconvo_new_convo">
        
        <input type="hidden" name="users_list" id="users_list">
        
        <?php wp_nonce_field('action_new', 'nonce_nmconvo_new');?>
       
        <div class="form-group row">
          
          <label for="send_to" class="col-sm-2 col-form-label"><?php _e('Send to:', 'nmconvo')?></label>
          
          <div class="col-sm-10">
            <a href="#" class="nmconvo-select-users nmconvo-btn" data-target="#nmconvo-user-modal" data-toggle="modal">
              <?php _e('Select Users', 'nmconvo');?>
            </a>
          <div id="nmconvo-selected-users-list"></div>
          
          </div>
          
        </div>
        
        <div class="form-group row">
          
          <label for="subject" class="col-sm-2 col-form-label"><?php _e('Subject', 'nmconvo')?></label>
          
          <div class="col-sm-10">
            <input type="text" class="form-control" name="subject" id="subject" />
            <span class="error" id="subject_err"><?php _e('Required', 'nmconvo')?></span>
          </div>
          
        </div>

        <div class="form-group row">
          
          <label for="message" class="col-sm-2 col-form-label"><?php _e('Message', 'nmconvo')?></label>
          
          <div class="col-sm-10">
            <textarea class="form-control" name="message" id="message" cols="45" rows="5"></textarea>
            <span class="error" id="message_err"><?php _e('Required', 'nmconvo')?></span>
          </div>
          
        </div>

          
          <?php do_action('nmconvo_before_new_convo');?>
          
          <div class="form-group row">
            
              <div class="col-sm-2"></div>
              
              <div class="col-sm-10">
                <input type="submit" name="nm-new-convo" value="<?php _e('Send', 'nmconvo')?>" onclick="return validateCompose();" />
                <a href="#" class="nmconvo-btn nmconvo-cancel-reply"> 
                    <?php _e('Back to Inbox', 'nmconvo');?>
                </a>
                
                <div class="nmconvo-sending-message" style="display:none"><?php _e("Pleas waite ...", 'nmconvo');?></div>
              </div>
              
           </div>
           
    </form>
  </div>    <!-- compose-panel -->
  
  
  <!-- convo detail -->
  <div id="convo-history-panel" style="display:none">
    <span id="nmconvo-loading-convo">
      <img src="<?php echo NMCONVO_URL.'/images/loading.gif'?>" alt="Wait..." />
    </span>
    <h2 id="history-heading"></h2>
    <p id="convo-detail-container">
    </p>
    
    <div id="nmconvo-reply-form-div"> 
    
      <form id="nmconvo-reply-form" method="post">
      <?php wp_nonce_field('action_reply', 'nonce_nmconvo_reply');?>
      
      <input type="hidden" name="action" value="nmconvo_reply_convo">
      <input type="hidden" name="convo_id" id="reply-c-id" value="" />
      
      <div class="form-group row">
          
        <label for="message" class="col-sm-2 col-form-label"><?php _e('Reply Message', 'nmconvo')?></label>
        
        <div class="col-sm-10">
          <textarea required class="form-control" name="reply_message" id="nm-reply" rows="4" cols="60"></textarea>
          <span class="error" id="reply_err"><?php _e('Required', 'nmconvo')?></span>
        </div>
        
        
      </div>
      
       <?php do_action('nmconvo_before_reply_convo');?>  
       
       <div class="form-group row">
            
          <div class="col-sm-2"></div>
          
          <div class="col-sm-10">
            <input type="submit" value="<?php _e('Send', 'nmconvo')?>" name="reply-convo" onclick="return validateReply();" />
            <a href="#" class="nmconvo-btn nmconvo-cancel-reply">
                <?php _e('Back to Inbox', 'nmconvo');?>
            </a>
            
            <div class="nmconvo-sending-message" style="display:none"><?php _e("Pleas waite ...", 'nmconvo');?></div>
          </div>
          
          
          
       </div>
    
      
      </form>
    </div>
  </div>
  <!-- convo detail -->
    
<div class="fix_height"></div>
</div>