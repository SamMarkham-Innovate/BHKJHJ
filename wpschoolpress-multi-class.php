<?php
/*
Plugin Name: 	Wpschoolpress Multi Class
Plugin URI: 	http://wpschoolpress.com
Description:    With Multi-class Selection add-on, students in WPSchoolPress can be enrolled into multiple classes simultaneously as well as can check history of all previously enrolled classes.
Version: 		1.0
Author: 		WPSchoolPress Team
Author URI: 	wpschoolpress.com
Text Domain:	WPSchoolPress
Domain Path:    languages
*/
define( 'EDD_SAMPLE_STORE_URL_multiclss', 'https://wpschoolpress.com/' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file
define( 'EDD_SAMPLE_ITEM_ID_multiclss', 143997 ); // you should use your own CONSTANT name, and be sure to replace it throughout this file
ob_start();
define( 'EDD_SAMPLE_ITEM_NAME1_multiclss', 'Multi-class Add-on' ); // you should use your own CONSTANT name, and be sure to replace it throughout this file

// the name of the settings page for the license input to be displayed
define( 'EDD_SAMPLE_PLUGIN_LICENSE_PAGE_multiclss', 'pluginname-license1_multiclss' );
//define( 'EDD_SAMPLE_PLUGIN_LICENSE_PAGE', 'pluginname-license' );
if( !class_exists( 'EDD_SL_Plugin_Updater' ) ) {
	// load our custom updater
	include( dirname( __FILE__ ) . '/EDD_SL_Plugin_Updater.php' );
}
function sample_admin_notice__success_multicls() {
 if( is_plugin_active( 'wpschoolpress-multi-class/wpschoolpress-multi-class.php' ) ) {
   ?>
   <div class="notice notice-success is-dismissible">
       <?php echo '<p>To enable add-on functionalities, please add your license key in <a href="'.admin_url('admin.php?page=sch-settings').'">WPSchoolPress General Settings</a></p>';?>
   </div>
   <?php
 }
}
add_action( 'admin_notices', 'sample_admin_notice__success_multicls' );
add_action( 'admin_enqueue_scripts', 'wpsp_multicls_pluign_script' );
function wpsp_multicls_pluign_script( $hook ) {
  wp_enqueue_script( 'wpsp_multicls_script', plugins_url( '/', __FILE__ ). 'js/script.js', array(), '1.0' );

	wp_localize_script( 'wpsp_multicls_script', 'myback', array(
		'ajax_url' => admin_url( 'admin-ajax.php' )
	));

}
// Exit if accessed directly
/**
 * Basic plugin definitions
 *

 * @package WPSchoolPressmessage
 * @since 1.0.0
*/
global $wpdb;
$wpsp_settings_table	=	$wpdb->prefix."wpsp_settings";
$wpsp_settings_edit		=	$wpdb->get_results("SELECT * FROM $wpsp_settings_table" );

foreach( $wpsp_settings_edit as $sdat ) {
	$settings_data[$sdat->option_name]	=	$sdat->option_value;
}
$license_key = trim($settings_data['mcaon']);


function edd_sample_activate_license_multiclss() {
	global $wpdb;
$wpsp_settings_table	=	$wpdb->prefix."wpsp_settings";
$wpsp_settings_edit		=	$wpdb->get_results("SELECT * FROM $wpsp_settings_table" );
foreach( $wpsp_settings_edit as $sdat ) {
	$settings_data[$sdat->option_name]	=	$sdat->option_value;

								}

		$license_key = trim($settings_data['mcaon']);
		// data to send in our API request
		$api_params = array(
			'edd_action' => 'activate_license',
			'license'    => $license_key,
			'item_id'    => urlencode(EDD_SAMPLE_ITEM_ID_multiclss ), // the name of our product in EDD
			'url'        => home_url()
		);
		// Call the custom API.
		 $response = wp_remote_post( EDD_SAMPLE_STORE_URL_multiclss, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}

		} else {

			$license_data = json_decode( wp_remote_retrieve_body( $response ) );


			global $wpdb;
			$wpsp_settings_table	=	$wpdb->prefix."wpsp_settings";
			$sql = "INSERT INTO $wpsp_settings_table (option_name, option_value) VALUES ('mcaon_addon_pay_id', '".$license_data->payment_id."')";
			$wpdb->query($sql);

		}


}
add_action('admin_init', 'edd_sample_activate_license_multiclss');
/***********************************************
* Illustrates how to deactivate a license key.
* This will decrease the site count
***********************************************/


add_action('wp_ajax_wpsp_multicls_license_deactivate', 'wpsp_multicls_license_deactivate');
function wpsp_multicls_license_deactivate(){

	//add_action('admin_menu', 'edd_sample_deactivate_license');
	//edd_sample_deactivate_license();
	global $wpdb;
$wpsp_settings_table	=	$wpdb->prefix."wpsp_settings";
$wpsp_settings_edit		=	$wpdb->get_results("SELECT * FROM $wpsp_settings_table" );
foreach( $wpsp_settings_edit as $sdat ) {
	$settings_data[$sdat->option_name]	=	$sdat->option_value;
}

		$license_key = trim($settings_data['mcaon']);
		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license_key,
			'item_name'  => urlencode( EDD_SAMPLE_ITEM_NAME1_multiclss ), // the name of our product in EDD
			'url'        => home_url()
		);

		// Call the custom API.
		$response = wp_remote_post( EDD_SAMPLE_STORE_URL_multiclss, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {

			if ( is_wp_error( $response ) ) {
				$message = $response->get_error_message();
			} else {
				$message = __( 'An error occurred, please try again.' );
			}


		}

		$wpdb->query("UPDATE $wpsp_settings_table SET option_value=''  WHERE option_name='mcaon'");

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	header( 'Content-Type: application/json' );
	header( 'Status: 200' );
	// echo json_encode( array( 'result' => $license_data) );
	echo json_encode( array( 'result' => true) );
	die();
}


// add_action('admin_init', 'edd_sample_deactivate_license');
function edd_sl_sample_plugin_updater_multiclss() {


	global $wpdb;
$wpsp_settings_table	=	$wpdb->prefix."wpsp_settings";
$wpsp_settings_edit		=	$wpdb->get_results("SELECT * FROM $wpsp_settings_table" );
foreach( $wpsp_settings_edit as $sdat ) {

									$settings_data[$sdat->option_name]	=	$sdat->option_value;

								}
$license_key = trim($settings_data['mcaon']);
	// setup the updater
	$edd_updater = new EDD_SL_Plugin_Updater( EDD_SAMPLE_STORE_URL_multiclss, __FILE__,
		array(
			'version' => '1.0',                    // current version number
			'license' => $license_key,             // license key (used get_option above to retrieve from DB)
			'item_id' => EDD_SAMPLE_ITEM_ID_multiclss,       // ID of the product
			'author'  => 'Easy Digital Downloads', // author of this plugin
			'beta'    => false,
		)
	);

}
// $license_data->payment_id;
global $wpdb;
$wpsp_settings_table	=	$wpdb->prefix."wpsp_settings";
$wpsp_settings_edit		=	$wpdb->get_results("SELECT * FROM $wpsp_settings_table" );
foreach( $wpsp_settings_edit as $sdat ) {
									$settings_data[$sdat->option_name]	=	$sdat->option_value;
								}
$ids = $settings_data['mcaon_addon_pay_id'];
$member_detail = wp_remote_get('https://wpschoolpress.com/wp-json/api/v1/payment_key/'.$ids);
$team_details = json_decode(wp_remote_retrieve_body($member_detail));
if($team_details->message == "sucess")
{

		add_action( 'admin_init', 'edd_sl_sample_plugin_updater_multiclss', 0 );
}



function edd_sample_check_license_multiclss() {

	global $wp_version;
global $wpdb;
$wpsp_settings_table	=	$wpdb->prefix."wpsp_settings";
$wpsp_settings_edit		=	$wpdb->get_results("SELECT * FROM $wpsp_settings_table" );
foreach( $wpsp_settings_edit as $sdat ) {
	$settings_data[$sdat->option_name]	=	$sdat->option_value;

								}

 $license_key = trim($settings_data['mcaon']);
	$api_params = array(
		'edd_action' => 'check_license',
		'license' => $license_key,
		'item_name' => urlencode(EDD_SAMPLE_ITEM_NAME1_multiclss),
		'url'       => home_url()
	);

	// Call the custom API.
	$response = wp_remote_post( EDD_SAMPLE_STORE_URL_multiclss, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );
 //print_r($license_data);
	if( $license_data->license == 'valid' ) {
		 $valid =  'valid';

// Exit if accessed directly
//if ( ! defined( 'ABSPATH' ) ) exit;
/**
 * Basic plugin definitions
 *

 * @package WPSchoolPressaddon
 * @since 1.0.0
*/
if( !defined( 'WPSPmc_PLUGIN_URL' ) ) {
	define('WPSPmc_PLUGIN_URL', plugin_dir_url( __FILE__ ));
}
if( !defined( 'WPSPmc_PLUGIN_PATH' ) ) {
	define( 'WPSPmc_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
}
if( !defined( 'WPSPmc_PLUGIN_VERSION' ) ) {
	define( 'WPSPmc_PLUGIN_VERSION', '1.0' ); //Plugin version number
}

register_activation_hook(__FILE__, 'wpsp_mc_activation');
register_deactivation_hook( __FILE__, 'wpsp_mc_deactivation');



add_action( 'admin_init','WPSPmc_plugins_loaded', 999);
function WPSPmc_plugins_loaded() {
		$wpsp_lang_dir	= dirname( plugin_basename( __FILE__ ) ) . '/languages/';
		load_plugin_textdomain( 'WPSchoolPressmulticlass', false, $wpsp_lang_dir );


	

		global $wpsp_settings_data1, $WPSPmc, $wpspsetting1, $wpsp_mc_version, $wpsp_mc_version;
		$wpsp_mc_version = new wpsp_mc_version();
		if( !class_exists('Wpsp_Admin') ) {
			add_action( 'admin_init', array( $wpsp_mc_version, 'WPSPmc_plugin_deactivate' ) );
			add_action( 'admin_notices', array( $wpsp_mc_version, 'WPSPmc_plugin_admin_notice' ) );
		}
}

function wpsp_mc_activation()
{
	include_once (WPSPmc_PLUGIN_PATH . 'lib/wpsp_mc-activation.php');
}
function wpsp_mc_deactivation()
{
		include_once (WPSPmc_PLUGIN_PATH . 'lib/wpsp_mc-deactive.php');
}
class wpsp_mc_version {
	public function __construct() {
		add_filter( 'pre_set_site_transient_update_plugins', array( $this, 'WPSPmc_update_check' ) );

	}
function WPSPmc_plugin_admin_notice(){
	 echo '<div class="updated"><p> <strong> To Use  Multi Class Add-ons , Please Activate WPSchoolPress Plugin</strong></p></div>';
         if ( isset( $_GET['activate'] ) )
            unset( $_GET['activate'] );
	}

function WPSPmc_update_check($transient) {
		// Check if the transient contains the 'checked' information
		// If no, just return its value without hacking it
		if ( empty( $transient->checked ) )
			return $transient;
		$plugin_path = plugin_basename( __FILE__ );
		// POST data to send to your API
		$args = array(
			'referrer' 	=> 	get_site_url(),
			'code' 		=>	get_option('wpsp-lcode')
		);
		// Send request checking for an update
		$response = $this->WPSPmc_updateInfo( $args );
		$response=json_decode($response,true);
		$obj 				=	(object) array();
		$obj->slug 			=	'wpschoolpress-mc';
		$obj->new_version 	= 	$response['new_version'];
		$obj->tested 		=	$response['tested'];
		$obj->url 			= 	$response['url'];
		$obj->package 		=	$response['package'];
		// If there is a new version, modify the transient
		if( version_compare( $response['new_version'], $transient->checked[$plugin_path], '>' ) )
			$transient->response[$plugin_path] = $obj;
		return $transient;
	}
function WPSPmc_updateInfo( $args ) {
		// Send request
		$request = wp_remote_post('', array( 'method' => 'POST','body' => $args ) );
		if ( is_wp_error( $request ) || 200 != wp_remote_retrieve_response_code( $request ) )
			return false;
		return wp_remote_retrieve_body( $request ) ;
	}
}

}
else {
		 $valid = 'invalid';
		//register_deactivation_hook( __FILE__, 'wpsp_pro_plugin_deactivate');
		 //deactivate_plugins( plugin_basename( __FILE__ ) );
		// this license is no longer valid
	}
	//echo $license_data->license;
}
add_action( 'admin_init', 'edd_sample_check_license_multiclss', 0 );
 