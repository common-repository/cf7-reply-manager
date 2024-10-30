<?php 

   if ( ! defined( 'ABSPATH' ) ) {
      exit;
   }

   global $wpdb;
   $args = array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1);
   $cf7Forms = get_posts( $args );
   $fid = sanitize_text_field($_GET['formId'] ?: $cf7Forms[0]->ID);
   
   $fields = $wpdb->get_results(
      $wpdb->prepare("SELECT `value` FROM `".RM_CF7_REPLIES_TABLE_NAME."` WHERE cf7_id = %s", $fid)
   );
   $formData = (count($fields) > 0 ? json_decode($fields[0]->value) : new stdClass());
   if(null === $formData){
      $formData = new stdClass();
   }
   $uploaddir = WP_CONTENT_DIR.'/cf7-rm-uploads/';
   $uploadUrl = site_url().'/wp-content/cf7-rm-uploads/';
   if(isset($_POST['approval_subject']) && isset($_POST['rejection_subject'])) {
      $approval_attachment_path = $formData->approval_attachment ?: '';
      if($_FILES['approval_attachment']['name'] ){
         $approval_attachment_tmp = $_FILES['approval_attachment']['tmp_name'];
         $approval_attachment_name = $_FILES['approval_attachment']['name'];
         if( move_uploaded_file($approval_attachment_tmp, $uploaddir.$approval_attachment_name)) { 
            $approval_attachment_path = $approval_attachment_name;
         }
      }
      if(isset($_POST['approval_attachment_removed']) && isset($_POST['approval_attachment_removed'])) {
         if($_POST['approval_attachment_removed'] === 'true'){
            $approval_attachment_path = '';
         }
      }
      $rejection_attachment_path = $formData->rejection_attachment ?: '';
      if($_FILES['rejection_attachment']['name'] ){
         $rejection_attachment_tmp = $_FILES['rejection_attachment']['tmp_name'];
         $rejection_attachment_name = $_FILES['rejection_attachment']['name'];
         if( move_uploaded_file($rejection_attachment_tmp, $uploaddir.$rejection_attachment_name)) { 
            $rejection_attachment_path = $rejection_attachment_name;
         }
      }
      if(isset($_POST['rejection_attachment_removed']) && isset($_POST['rejection_attachment_removed'])) {
         if($_POST['rejection_attachment_removed'] === 'true'){
            $rejection_attachment_path = '';
         }
      }

      $update = (!(array)$formData ? false : true);
      $formData->email_field = sanitize_text_field($_POST['email_field']);
      $formData->from_name = sanitize_text_field($_POST['from_name']);
      $formData->from_mail = sanitize_email($_POST['from_mail']);
      $formData->reply_to = sanitize_email($_POST['reply_to']);
      $formData->cc = sanitize_email($_POST['cc']);
      $formData->bcc = sanitize_email($_POST['bcc']);
      $formData->approval_subject = sanitize_text_field($_POST['approval_subject']);
      $formData->approval_message = wp_kses_post($_POST['approval_message']);
      $formData->approval_attachment = sanitize_file_name($approval_attachment_path);
      $formData->rejection_subject = sanitize_text_field($_POST['rejection_subject']);
      $formData->rejection_message = wp_kses_post($_POST['rejection_message']);
      $formData->rejection_attachment = sanitize_file_name($rejection_attachment_path);
      
      $query = $wpdb->prepare('INSERT INTO '.RM_CF7_REPLIES_TABLE_NAME.'(`cf7_id`, `value`) VALUES (%s,%s)', $fid, json_encode($formData));
      if($update) {
        $query = $wpdb->prepare('UPDATE '.RM_CF7_REPLIES_TABLE_NAME.' SET `value` = %s WHERE `cf7_id` = %s', json_encode($formData), $fid);
        echo  '<div class="cf7-rm-popup cf7-rm-success-popup">Email settings saved</div>';
      } 

      $formDataInsert = $wpdb->query($query);

   }
?>

<div class="wrap">
   <?php CF7_RM_SelectForms($cf7Forms, $fid) ?>
   <form enctype="multipart/form-data" action="" method="post">
      <div class="sender-settings">
         <h2>Form Settings</h2>
         <label for="">E-mail field name in CF7</label>
         <input name="email_field" type="text" required value="<?= $formData->email_field ?: 'your-email' ?>">

         <h2>Sender Settings</h2>
         <label for="">Name</label>
         <input name="from_name" type="text" required value="<?= $formData->from_name ?>">
         <label for="">From Mail</label>
         <input name="from_mail" type="email" required value="<?= $formData->from_mail ?>">
         <label for="">Reply To</label>
         <input name="reply_to" type="email" value="<?= $formData->reply_to ?>">
         <label for="">CC</label>
         <input name="cc" type="email" value="<?= $formData->cc ?>">
         <label for="">BCC</label>
         <input name="bcc" type="email" value="<?= $formData->bcc ?>">
      </div>
      <div class="approval">
         <h2>Approval Email</h2>
         <label for="">Subject</label>
         <input name="approval_subject" type="text" required value="<?= $formData->approval_subject ?>">
         <label for="">Message</label>
         <?php wp_editor( $formData->approval_message, "approval_message", [] ) ?>
         <label for="">Attachment</label>
         <div class="attachment-section">
            <?php 
               $approval_file_uploader_class = '';
               $approval_file_attachment_class = 'hidden';
               if(!empty($formData->approval_attachment)){
                  $file_info = pathinfo($formData->approval_attachment);
                  $approval_file_uploader_class = 'hidden';
                  $approval_file_attachment_class = '';
               }
            ?>
            <span class="file-attachment <?= $approval_file_attachment_class ?>">
               <div class="preview-box">
                  <a class="preview-link" href="<?= $uploadUrl.$file_info['basename']; ?>" target="_blank">
                     <svg width="30px" aria-hidden="true" focusable="false" data-prefix="far" data-icon="file-alt" class="svg-inline--fa fa-file-alt fa-w-12" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" stroke-width="5" d="M288 248v28c0 6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-28c0-6.6 5.4-12 12-12h168c6.6 0 12 5.4 12 12zm-12 72H108c-6.6 0-12 5.4-12 12v28c0 6.6 5.4 12 12 12h168c6.6 0 12-5.4 12-12v-28c0-6.6-5.4-12-12-12zm108-188.1V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V48C0 21.5 21.5 0 48 0h204.1C264.8 0 277 5.1 286 14.1L369.9 98c9 8.9 14.1 21.2 14.1 33.9zm-128-80V128h76.1L256 51.9zM336 464V176H232c-13.3 0-24-10.7-24-24V48H48v416h288z"></path></svg>
                  </a>
                  <strong><?=  $file_info['basename']; ?></strong>
                  <a class="clear-attachment" id="clean-approval-attachment" href="">&times;</a>
               </div>
            </span> 
            <input class="<?= $approval_file_uploader_class ?>" name="approval_attachment" type="file">
            <input name="approval_attachment_removed" type="hidden" value="false">
         </div>
      </div>
      <div class="approval">
         <h2>Rejection Email</h2>
         <label for="">Subject</label>
         <input name="rejection_subject" type="text" required value="<?= $formData->rejection_subject ?>">
         <label for="">Message</label>
         <?php wp_editor( $formData->rejection_message, "rejection_message", [] ) ?>
         <label for="">Attachment</label>
         <div class="attachment-section">
            <?php 
               $rejection_file_uploader_class = '';
               $rejection_file_attachment_class = 'hidden';
               if(!empty($formData->rejection_attachment)){
                  $file_info = pathinfo($formData->rejection_attachment);
                  $rejection_file_uploader_class = 'hidden';
                  $rejection_file_attachment_class = '';
               }
            ?>
            <span class="file-attachment <?= $rejection_file_attachment_class ?>">
               <div class="preview-box">
                  <a class="preview-link" href="<?= $uploadUrl.$file_info['basename']; ?>" target="_blank">
                     <svg width="30px" aria-hidden="true" focusable="false" data-prefix="far" data-icon="file-alt" class="svg-inline--fa fa-file-alt fa-w-12" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><path fill="currentColor" stroke-width="5" d="M288 248v28c0 6.6-5.4 12-12 12H108c-6.6 0-12-5.4-12-12v-28c0-6.6 5.4-12 12-12h168c6.6 0 12 5.4 12 12zm-12 72H108c-6.6 0-12 5.4-12 12v28c0 6.6 5.4 12 12 12h168c6.6 0 12-5.4 12-12v-28c0-6.6-5.4-12-12-12zm108-188.1V464c0 26.5-21.5 48-48 48H48c-26.5 0-48-21.5-48-48V48C0 21.5 21.5 0 48 0h204.1C264.8 0 277 5.1 286 14.1L369.9 98c9 8.9 14.1 21.2 14.1 33.9zm-128-80V128h76.1L256 51.9zM336 464V176H232c-13.3 0-24-10.7-24-24V48H48v416h288z"></path></svg>
                  </a>
                  <strong><?=  $file_info['basename']; ?></strong>
                  <a class="clear-attachment" id="clean-rejection-attachment" href="">&times;</a>
               </div>
            </span> 
            <input class="<?= $rejection_file_uploader_class ?>" name="rejection_attachment" type="file">
            <input name="rejection_attachment_removed" type="hidden" value="false">
         </div>
      </div>
      <input type="submit" value="Save">
   </form>
</div>