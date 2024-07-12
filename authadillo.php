<?php
/*
Plugin Name: AuthAdillo

Plugin URI:  N/A 
Description: Simple membership plugin
Version:     1.1.0
Author:      Leslie K. Nielsen
Author URI:  https://www.linkedin.com/in/leslieknielsen/
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: authadillo

*/

session_start();

if (!defined('ABSPATH')) 
{
    exit; // Exit if accessed directly.
}

define('AUTHADILLO_ABSPATH', dirname( __FILE__ ) . '/');
define('AUTHADILLO_DIR', dirname(__FILE__));

/* Helpers */
include_once( AUTHADILLO_ABSPATH . 'includes/authadillo-helpers.php' );

/* Main Class */
include_once( AUTHADILLO_ABSPATH . 'includes/authadillo-controller.php' );

//Include primary classes
$primary_classes = array(
    'AuthadilloAuthentication' => 'includes/authadillo-authentication.php'
);

foreach ($primary_classes as $class => $relative_path) 
{
    if (!class_exists($class)) increq_file(''.$relative_path, 'include_once');
}

/* Instantiate Authentication Object */
$auth_obj = new AuthadilloAuthentication();

/* Instantiate Authadillo Object */
$authadillo_obj = new Authadillo($auth_obj);

/* Register activation and deactivation functions */
register_activation_hook( __FILE__, array($authadillo_obj, 'ActivatePlugin'));
register_deactivation_hook( __FILE__, array($authadillo_obj, 'DeactivatePlugin'));

/* Enqueue JavaScript files */
add_action('wp_enqueue_scripts', 'enqueue_authadillo_scripts');

/* Register AJAX functions */
add_action('wp_ajax_authadillo_signout', 'authadillo_signout');
add_action('wp_ajax_nopriv_authadillo_signout', 'authadillo_signout');

function enqueue_authadillo_scripts()
{
    wp_enqueue_style('authadillo-style', plugin_dir_url(__FILE__) . 'public/css/authadillo.css');
    
    // Check if jQuery is already enqueued
    if (!wp_script_is('jquery', 'enqueued')) 
    {
        // Enqueue jQuery if not already enqueued
        wp_enqueue_script('jquery');
    }

    wp_enqueue_script('authadillo-script', plugin_dir_url(__FILE__) . 'public/js/authadillo.js', array('jquery'), null, true);

    wp_localize_script('authadillo-script', 'authadillo_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php')
    ));
}

/* AJAX Functions */
function authadillo_signout($auth_obj) 
{
    global $auth_obj;
    
    if ($auth_obj->GetAuthStatus()) 
    {
        // Assuming AuthadilloAuthentication::SignOut() handles the sign-out process
        $auth_obj->SignOut();

        wp_send_json_success(array('success' => true, 'redirect_url' => home_url())); // Adjust the redirect URL as needed
    } 
    else 
    {
        wp_send_json_error(array('message' => 'User is not logged in.'));
    }
}

