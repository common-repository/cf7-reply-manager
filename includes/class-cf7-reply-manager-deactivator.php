<?php

/**
 * Fired during plugin deactivation
 *
 * @link       https://bigfive.it
 * @since      1.0.0
 *
 * @package    CF7_Reply_Manager
 * @subpackage CF7_Reply_Manager/includes
 */

/**
 * Fired during plugin deactivation.
 *
 * This class defines all code necessary to run during the plugin's deactivation.
 *
 * @since      1.0.0
 * @package    CF7_Reply_Manager
 * @subpackage CF7_Reply_Manager/includes
 * @author     Bigfive <https://bigfive.it>
 */
class Cf7_Reply_Manager_Deactivator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function deactivate() {
      global $wpdb;
      
      if (function_exists('is_multisite') && is_multisite()) {
         // check if it is a network activation - if so, run the activation function for each blog id
         $old_blog = $wpdb->blogid;
         // Get all blog ids
         $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
         foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            delete_table_cf7rm_vdata();
            delete_table_cf7rm_vdata_entry();
            delete_table_cf7rm_replies();
            delete_upload_folder();
         }
         switch_to_blog($old_blog);
      }else{
         delete_table_cf7rm_vdata();
         delete_table_cf7rm_vdata_entry();
         delete_table_cf7rm_replies();
         delete_upload_folder();
      }
	}
}

function delete_table_cf7rm_vdata() {

   global $wpdb;
   $table_name = RM_CF7_DATA_TABLE_NAME;
   $sql = "DROP TABLE IF EXISTS $table_name";
   $wpdb->query($sql);

}

function delete_table_cf7rm_vdata_entry() {

   global $wpdb;
   $table_name = RM_CF7_DATA_ENTRY_TABLE_NAME;
   $sql = "DROP TABLE IF EXISTS $table_name";
   $wpdb->query($sql);

}

function delete_table_cf7rm_replies() {

   global $wpdb;
   $table_name = $wpdb->prefix .'cf7rm_replies';
   $sql = "DROP TABLE IF EXISTS $table_name";
   $wpdb->query($sql);

}

function delete_upload_folder() {
   $sqluploaddir = WP_CONTENT_DIR.'/cf7-rm-uploads/';
   if (is_dir($sqluploaddir)) {
      rmdir($sqluploaddir);
   }
}

