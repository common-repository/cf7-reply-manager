<?php

/**
 * Fired during plugin activation
 *
 * @link       https://bigfive.it
 * @since      1.0.0
 *
 * @package    CF7_Reply_Manager
 * @subpackage CF7_Reply_Manager/includes
 */

/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    CF7_Reply_Manager
 * @subpackage CF7_Reply_Manager/includes
 * @author     Bigfive <https://bigfive.it>
 */
class Cf7_Reply_Manager_Activator {

	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		global $wpdb;
		if (function_exists('is_multisite') && is_multisite()) {
         // check if it is a network activation - if so, run the activation function for each blog id
         $old_blog = $wpdb->blogid;
         // Get all blog ids
         $blogids = $wpdb->get_col("SELECT blog_id FROM $wpdb->blogs");
         foreach ($blogids as $blog_id) {
            switch_to_blog($blog_id);
            create_table_cf7rm_vdata();
            create_table_cf7rm_vdata_entry();
            create_table_cf7rm_replies();
            create_upload_folder();
         }
         switch_to_blog($old_blog);
		}else{
			create_table_cf7rm_vdata();
			create_table_cf7rm_vdata_entry();
         create_table_cf7rm_replies();
         create_upload_folder();
		}

	} 

}	

/**
 * Contact Form submitted table created from here
 */

function create_table_cf7rm_vdata(){
	
	global $wpdb;
	$table_name = RM_CF7_DATA_TABLE_NAME;
	
	$charset_collate = $wpdb->get_charset_collate();
	if( $wpdb->get_var( "show tables like '{$table_name}'" ) != $table_name ) {
        $sql = "CREATE TABLE " . $table_name . " (
             `id` int(11) NOT NULL AUTO_INCREMENT,
			 `created` timestamp NOT NULL,
			  UNIQUE KEY id (id)
		)$charset_collate;";
		
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );
    }
}
/**
 * Contact Form entry table created from here
 */

function create_table_cf7rm_vdata_entry(){
	global $wpdb;
	$table_name = RM_CF7_DATA_ENTRY_TABLE_NAME;
	$charset_collate = $wpdb->get_charset_collate();
	if( $wpdb->get_var( "show tables like '{$table_name}'" ) != $table_name ) {
        $sql = "CREATE TABLE " . $table_name . " (
				`id` int(11) NOT NULL AUTO_INCREMENT,
				`cf7_id` int(11) NOT NULL,
				`data_id` int(11) NOT NULL,
				`name` varchar(250),
				`value` text,
				UNIQUE KEY id (id)
		)$charset_collate;";
		
        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }
}


/**
 * Create replies settings 
 */

function create_table_cf7rm_replies(){
   
   global $wpdb;
   $table_name = $wpdb->prefix .'cf7rm_replies';
   
   $charset_collate = $wpdb->get_charset_collate();
   if( $wpdb->get_var( "show tables like '{$table_name}'" ) != $table_name ) {
      $sql = "CREATE TABLE " . $table_name . " (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `cf7_id` int(11) NOT NULL,
            `value` text,
           UNIQUE KEY id (id)
      )$charset_collate;";
      
      require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
      dbDelta( $sql );
   }
}

/**
 * Create upload folder
 */

function create_upload_folder(){
   $sqluploaddir = WP_CONTENT_DIR.'/cf7-rm-uploads/';
   if (!file_exists($sqluploaddir)) {
       mkdir($sqluploaddir, 0777, true);
   }
}