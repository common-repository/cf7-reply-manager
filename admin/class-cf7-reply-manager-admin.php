<?php


// Add Plugin Page
add_action('admin_menu', 'cf7_reply_manager_menu');

function cf7_reply_manager_menu() {
   add_menu_page('CF7 Reply Manager', 'CF7 Reply Manager', 'read', 'cf7_reply_manager', 'cf7_reply_manager_admin_page', 'dashicons-yes', 26);
   add_submenu_page( 'cf7_reply_manager', 'CF7 Reply Manager Settings', 'Settings', 'read', 'cf7_reply_manager-settings', 'cf7_reply_manager_settings_page');
}
   

function cf7_reply_manager_settings_page() {
   require_once plugin_dir_path( __FILE__ ) . './partials/replies.php';
}   

function cf7_reply_manager_admin_page() {
   require_once plugin_dir_path( __FILE__ ) . './partials/form-list.php';
}


wp_register_style( 'cf7_rm_style', plugin_dir_url(__FILE__ ).'css/style.css', array(), '1.0.0', 'all' );
wp_enqueue_style('cf7_rm_style');

wp_register_script( 'cf7_rm_script', plugin_dir_url(__FILE__ ).'js/cf7_rm.js', array(), '1.0.0', 'all' );
wp_enqueue_script('cf7_rm_script');


function CF7_RM_SelectForms($cf7Forms, $selected) { ?>
   <h3><?php _e('Select Form') ?></h3>
   <select name="" id="cf7-rm-select-form">
      <?php foreach ($cf7Forms as $key => $value): ?>
         <option <?php if($value->ID == $selected ){echo 'selected';} ?> value="<?=  $value->ID ?>"><?= $value->post_title ?></option>
      <?php endforeach; ?>
   </select>
<?php 
}

function cf7rm_sortdata($data, $filter){
   $sorted = array_reduce($data, function($sorted, $row){
      if(!isset($sorted[$row->data_id])){
         $sorted[$row->data_id] = [
            'status' => '',
            'created' => $row->created
         ];
      }
      $sorted[$row->data_id][$row->name] = apply_filters('cf7d_entry_value', $row->value, $row->name);

      return $sorted;
   }, []);

   return array_filter($sorted, function($row) use ($filter)  {
      return 'all' === $filter || $row['status'] === $filter;
   });

}