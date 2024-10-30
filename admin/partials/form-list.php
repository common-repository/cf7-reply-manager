<?php    

if ( ! defined( 'ABSPATH' ) ) {
   exit;
}
global $wpdb;

$args = array('post_type' => 'wpcf7_contact_form', 'posts_per_page' => -1);
$cf7Forms = get_posts( $args );
$fid = sanitize_text_field($_GET['formId'] ?: $cf7Forms[0]->ID);
$uploaddir = WP_CONTENT_DIR.'/cf7-rm-uploads/';

$status_filter = isset($_GET['filter']) ? sanitize_text_field($_GET['filter']) : '';

if(isset($_POST['ids']) && count($_POST['ids']) >=  1){ 
   $ids = array_map( 'sanitize_text_field', wp_unslash( $_POST['ids'] ) );
   $preparedIds = implode(', ', array_fill(0, count($ids), '%s'));

   if($_POST['mode'] === 'remove') {
      $wpdb->query(
         $wpdb->prepare("DELETE FROM " . RM_CF7_DATA_ENTRY_TABLE_NAME . " WHERE data_id IN ($preparedIds)", ...$ids)
      );
      $wpdb->query(
         $wpdb->prepare("DELETE FROM " . RM_CF7_DATA_TABLE_NAME . " WHERE id IN ($preparedIds)", ...$ids)
      );
      echo  '<div class="cf7-rm-popup cf7-rm-success-popup">Resource deleted</div>';
   }
   else {
      $fields = $wpdb->get_results(
         $wpdb->prepare("SELECT `value` FROM `".RM_CF7_REPLIES_TABLE_NAME."` WHERE cf7_id = %s", $fid)
      );
      $formData = json_decode($fields[0]->value);
      
      $from_mail = sanitize_email($formData->from_mail);
      $reply_to =  sanitize_email($formData->reply_to);
      $cc =  sanitize_email($formData->cc);
      $bcc =  sanitize_email($formData->bcc);
      $from_name = sanitize_text_field($formData->from_name);

      $status = 'rejected';   
      $subject = sanitize_text_field($formData->rejection_subject);
      $body = nl2br(wp_kses_post($formData->rejection_message));
      if($formData->rejection_attachment) {
         $attachment = $uploaddir . $formData->approval_attachment;
      }

      if($_POST['mode'] == 'approve') {
         $status = 'approved';
         $subject = sanitize_text_field($formData->approval_subject);
         $body = nl2br(wp_kses_post($formData->approval_message));
         if($formData->approval_attachment) {
            $attachment = $uploaddir . $formData->approval_attachment;
         }
         else {
            $attachment = null;
         }
      }

      if($from_mail && $from_name && $subject && $body  ) {
              
         $selected = $wpdb->get_results(
            $wpdb->prepare("SELECT `value`, `data_id` FROM " . RM_CF7_DATA_ENTRY_TABLE_NAME . " WHERE cf7_id = %s AND name = %s AND data_id IN ($preparedIds)",
               $fid,
               $formData->email_field ?: 'your-email',
               ...$ids
            )
         );
         $headers = [
            "From: $from_name<$from_mail>",
            "Content-Type: text/html; charset=ISO-8859-1",
         ];
         if(!empty($reply_to)) {
            $headers[] = "Reply-To: ".$reply_to;
         }
         if(!empty($cc)){
            $headers[] = "Cc: ".$cc;
         }
         if(!empty($bcc)){
            $headers[] = "Bcc: ".$bcc;
         }
         foreach ($selected as $key => $user) {

            $mail_to = $user->value;
            // Send email
            if (wp_mail($mail_to, $subject, $body, $headers, $attachment)) {
               echo  '<div class="cf7-rm-popup cf7-rm-success-popup">Approval / Rejection mail sent successfully</div>';
               $wpdb->query($wpdb->prepare('INSERT INTO '.RM_CF7_DATA_ENTRY_TABLE_NAME.'(`cf7_id`, `data_id`, `name`, `value`) VALUES (%d,%d,%s,%s)', $fid, $user->data_id, 'status', $status));
            }
            else{
               echo '<div class="cf7-rm-popup cf7-rm-error-popup">Something went wrong while sending your mail, retry please...</div>';
            }
         }
      } else {
         echo '<div class="cf7-rm-popup cf7-rm-error-popup">You must add <a href="'.admin_url( 'admin.php?page=cf7_reply_manager-settings' ).'">reply settings</a> before sending a response</div>';
      }
   }
}
?>

<?php  
  $data = $wpdb->get_results(
   $wpdb->prepare(
      "SELECT e.name, e.value, d.created, e.data_id FROM `".RM_CF7_DATA_ENTRY_TABLE_NAME."` e
         LEFT JOIN `".RM_CF7_DATA_TABLE_NAME."` d ON e.data_id = d.id
         WHERE cf7_id = %s",
      $fid
   )
  );
  $fields = array_unique(array_map(function($row){ 
   return $row->name;
  }, $data));
  $sorted_data = cf7rm_sortdata($data, $status_filter);

?>
<div class="wrap cf7-rm-container">
   <?php CF7_RM_SelectForms($cf7Forms, $fid) ?>
   <ul class="status-navigation">
      <li>
         <a href="#" class="<?php if(!$status_filter) echo 'active'; ?>" data-filter="">Pending</a>
      </li>
      <li>
         <a href="#" class="<?php if($status_filter === 'approved') echo 'active'; ?>" data-filter="approved">Approved</a>
      </li>
      <li>
         <a href="#" class="<?php if($status_filter === 'rejected') echo 'active'; ?>" data-filter="rejected">Rejected</a>
      </li>
      <li>
         <a href="#" class="<?php if($status_filter === 'all') echo 'active'; ?>" data-filter="all">All</a>
      </li>
   </ul>
   <form action="" method="post">
      <input id="mode" name="mode" type="hidden" value="approve">
      <input id="approve-button" type="button" value="Send Approval Message">
      <input id="reject-button" type="button" value="Send Rejection Message">
      <input id="remove-button" type="button" value="Remove">
      <input id="csv-button" type="button" value="Export Csv">
      <div id="date-filter">
         From:
         <input type="date" id="min" placeholder="from">
         To:
         <input type="date" id="max" placeholder="to">
      </div>
      <table id="cf7-rm-response-list">
        <thead>
          <tr>
            <th><input name="select-all" type="checkbox" id="cf7-rm-toggle-all"></th>
            <?php foreach ($fields as $field): ?>
              <th><?= $field ?></th>
            <?php endforeach; ?>
            <th>Date</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($sorted_data as $i => $item):?>
            <tr class="<?= $item['status'] ?>">
               <td><input name="ids[]" type="checkbox" value="<?= $i ?>"></td>
               <?php foreach ($fields as $field): ?>
                  <td><?= $item[$field] ?></td>
               <?php endforeach; ?>
               <td><?= $item['created'] ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
   </form>
   
</div>

<?php

wp_register_style( 'Data_Tables', plugin_dir_url(__FILE__ ).'../css/datatables.min.css' );
wp_enqueue_style('Data_Tables');

wp_register_script( 'Data_Tables', plugin_dir_url(__FILE__ ).'../js/datatables.min.js', null, null, true );
wp_enqueue_script('Data_Tables');





