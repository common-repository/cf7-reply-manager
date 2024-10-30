<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://bigfive.it
 * @since      1.0.0
 *
 * @package    CF7_Reply_Manager
 * @subpackage CF7_Reply_Manager/includes
 */


/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    CF7_Reply_Manager
 * @subpackage CF7_Reply_Manager/includes
 * @author     Bigfive <https://bigfive.it>
 */

class CF7_Reply_Manager {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      CF7_Reply_Manager_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $vnctionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {

		$this->plugin_name = 'cf7_reply_manager';
		$this->version = '1.2.3';

		// $this->load_dependencies();
		// $this->set_locale();
		$this->define_admin_hooks();
		// $this->define_public_hooks();

	}

   private function define_admin_hooks() {
      require_once plugin_dir_path( __FILE__ ) . '../admin/class-cf7-reply-manager-admin.php';
   }

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {

     add_action( 'wpcf7_mail_sent', 'handle_form_submission' ); 

     //Define function for which field value not insert in table
      function bigfive_cf7_no_save_fields(){
         $cf7_no_save_fields = array('_wpcf7', '_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag', '_wpcf7_is_ajax_call','_wpcf7_container_post', 'g-recaptcha-response');
         //Add filter for customize values
         return apply_filters('bigfive_cf7_no_save_fields', $cf7_no_save_fields);
      }


      function handle_form_submission( $contact_form ) {
         global $wpdb;
         $cf7_id = $contact_form->id();
         
         $submission = WPCF7_Submission::get_instance();

         if ( $submission ) {
            // Insert current form submission time in database
            $time = date('Y-m-d H:i:s');
            $wpdb->query($wpdb->prepare('INSERT INTO '.RM_CF7_DATA_TABLE_NAME.'(`created`) VALUES (%s)', $time));
            //Get last inserted id
            $data_id = $wpdb->insert_id;

            if(!empty($cf7_id) && !empty($data_id)){
               $posted_data = $submission->get_posted_data();
               $bigfive_cf7_no_save_fields = bigfive_cf7_no_save_fields();

               foreach ($posted_data as $k => $v) {
                  //Check not inserted fields name in array or not
                  if(in_array($k, $bigfive_cf7_no_save_fields)) {
                     continue;
                  }
                  else{
                     //If value is check box and radio button value then creaye single string
                     if(is_array($v)){
                        $v = implode("\n", $v);
                     }
                     $k = htmlspecialchars($k);
                     $v = htmlspecialchars($v);
                     $wpdb->query($wpdb->prepare('INSERT INTO '.RM_CF7_DATA_ENTRY_TABLE_NAME.'(`cf7_id`, `data_id`, `name`, `value`) VALUES (%d,%d,%s,%s)', $cf7_id, $data_id, $k, $v));
                  }
               }
            }
         }
      }
	}
}
