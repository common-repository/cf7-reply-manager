<?php
/**
 * @link              https://bigfive.it/
 * @since             1.0.0
 * @package           CF7_Reply_Manager
 *
 * @wordpress-plugin
 * Plugin Name:       CF7 Reply Manager
 * Description:       CF7 add-on to provide easy, pre-compiled answers for each form in your website
 * Version:           1.2.3
 * Author:            Bigfive
 * Author URI:        https://bigfive.it/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cf7_reply_manager
 * Domain Path:       /languages
 */


if ( ! defined( 'ABSPATH' ) ) {
   exit;
}

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
   die;
}

global $cf7rm_current_version;
$cf7rm_current_version = '1.2.3';

/**
 * Defining all the table names and setting their prefix here
 */
global $wpdb;

define('RM_CF7_REPLIES_TABLE_NAME',  $wpdb->prefix.'cf7rm_replies');
define('RM_CF7_DATA_TABLE_NAME',  $wpdb->prefix.'cf7rm_vdata');
define('RM_CF7_DATA_ENTRY_TABLE_NAME', $wpdb->prefix.'cf7rm_vdata_entry');

define('RM_CF7_UPLOAD_FOLDER','cf7-reply-manager-upload');

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-advanced-cf7-db-activator.php
 */
function activate_cf7_reply_manager() {
   // @TODO: check if cf7 is installed
   require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-reply-manager-activator.php';
   Cf7_Reply_Manager_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-advanced-cf7-db-deactivator.php
 */
function deactivate_cf7_reply_manager() {
   require_once plugin_dir_path( __FILE__ ) . 'includes/class-cf7-reply-manager-deactivator.php';
   Cf7_Reply_Manager_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_cf7_reply_manager' );
register_deactivation_hook( __FILE__, 'deactivate_cf7_reply_manager' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-cf7-reply-manager.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_cf7_reply_manager() {

   $plugin = new CF7_Reply_Manager();
   $plugin->run();

}
run_cf7_reply_manager();

add_filter('plugin_action_links_'.plugin_basename(__FILE__), 'cf7_reply_manager_add_plugin_page_settings_link');

function cf7_reply_manager_add_plugin_page_settings_link( $links ) {
   $links[] = '<a href="' .
      admin_url( 'admin.php?page=cf7_reply_manager-settings' ) .
      '">' . __('Settings') . '</a>';
   return $links;
}



function cf7_rm_error() {

  if( !file_exists(WP_PLUGIN_DIR.'/contact-form-7/wp-contact-form-7.php') ) {

    $cme_error_out = '<div class="error" id="messages"><p>';
    $cme_error_out .= __('The Contact Form 7 plugin must be installed for the <b>CF7 Reply Manager</b> to work. <b><a href="'.admin_url('plugin-install.php?tab=plugin-information&plugin=contact-form-7&from=plugins&TB_iframe=true&width=600&height=550').'" class="thickbox" title="Contact Form 7">Install Contact Form 7  Now.</a></b>', 'cme_error');
    $cme_error_out .= '</p></div>';
    echo $cme_error_out;

  } else if ( !class_exists( 'WPCF7') ) {

    $cme_error_out = '<div class="error" id="messages"><p>';
    $cme_error_out .= __('The Contact Form 7 is installed, but <strong>you must activate Contact Form 7</strong> below for the <b>CF7 Reply Manager</b> to work.','cme_error');
    $cme_error_out .= '</p></div>';
    echo $cme_error_out;

  }

}
add_action('admin_notices', 'cf7_rm_error');

