<?php
/**
 * Plugin Name:           Inkseekers for WooCommerce
 * Plugin URI:            https://wordpress.org/plugins/inkseekers-for-woocommerce/
 * Description:           This is a Inkseekers-Woocommerce integration and Upload your designs to create t-shirt & tumbler mockups, then publish it on woocommerce store using this app.
 * Author:                Inkseekers
 * Author URI:            https://profiles.wordpress.org/inkseekers/
 * License:               GPL3 https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:           inkseekers
 * Requires at least:     5.3
 * Requires PHP:          7.1
 * WC requires at least:  4.7
 * WC tested up to:       4.9.1
 * Version:               1.0
 *
 * @package               inkseekers
 */


if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! defined( 'INKR_PLUGIN_FILE' ) ) {
    define( 'INKR_PLUGIN_FILE', __FILE__ );
}

class Inkseekers_Base {

    const VERSION = '1.0';
    const INKR_HOST = 'https://app.inkseekers.com/';
    const INKR_API_HOST = 'https://app.inkseekers.com/';

    /**
     * Construct the plugin.
     */
    public function __construct() {
        add_action( 'plugins_loaded', array( $this, 'init' ) );
        add_action( 'plugins_loaded', array( $this, 'inkseekers_load_plugin_textdomain') );
    }

    /**
     * Initialize the plugin.
     */
    public function init() {

        if (!class_exists('WC_Integration')) {
            return;
        }

	    // WP REST API.
	    $this->inkseeker_rest_api_init();

        //load required classes
	    require_once 'includes/class-inkseekers-integration.php';
	    require_once 'includes/class-inkseekers-request-log.php';
	    require_once 'includes/class-inkseekers-admin.php';
	    require_once 'includes/class-inkseekers-admin-dashboard.php';
	    require_once 'includes/class-inkseekers-admin-status.php';
	    require_once 'includes/class-inkseekers-admin-support.php';
	   
	    //launch init
	    Inkseekers_Request_log::init();
	    Inkseekers_Admin::init();
	 
	    //hook ajax callbacks
	    add_action( 'wp_ajax_ajax_force_check_connect_status', array( 'Inkseekers_Integration', 'ajax_force_check_connect_status' ) );
	    add_action( 'wp_ajax_get_inkseekers_stats', array( 'Inkseekers_Admin_Dashboard', 'inkseekers_render_stats_ajax' ) );
	    add_action( 'wp_ajax_get_inkseekers_status_checklist', array( 'Inkseekers_Admin_Status', 'inkseekers_render_status_table_ajax' ) );
	    add_action( 'wp_ajax_get_inkseekers_status_report', array( 'Inkseekers_Admin_Support', 'inkseekers_render_status_report_ajax' ) );
        }

    /**
     * Load Localisation files.
     *
     * Note: the first-loaded translation file overrides any following ones if the same translation is present.
     */
    public function inkseekers_load_plugin_textdomain() {
        load_plugin_textdomain( 'inkseekers', false, plugin_basename( dirname( INKR_PLUGIN_FILE ) ) . '/i18n/languages' );
    }

	/**
	 * @return string
	 */
    public static function inkseekers_get_asset_url() {
		return trailingslashit(plugin_dir_url(__FILE__)) . 'assets/';
    }

    /**
	 * @return string
	 */
	public static function inkseeker_get_host() {
		if ( defined( 'INKR_DEV_HOST' ) ) {
			return INKR_DEV_HOST;
		}

		return self::INKR_HOST;
	}

	/**
	 * @return string
	 */
	public static function inkseeker_get_api_host() {
		if ( defined( 'INKR_DEV_API_HOST' ) ) {
			return INKR_DEV_API_HOST;
		}

		return self::INKR_API_HOST;
	}

    private function inkseeker_rest_api_init()
    {
        // REST API was included starting WordPress 4.4.
        if ( ! class_exists( 'WP_REST_Server' ) ) {
            return;
        }

        // Init REST API routes.
        add_action( 'inkseeker_rest_api_init', array( $this, 'inkseeker_register_rest_routes' ), 20);
    }

    public function inkseeker_register_rest_routes()
    {
        require_once 'includes/class-inkseekers-rest-api-controller.php';

        $inkseekersRestAPIController = new Inkseekers_REST_API_Controller();
        $inkseekersRestAPIController->register_routes();
    }
    
    
}

new Inkseekers_Base();    //let's go

/*plugin activate hook*/
    function inkseeker_activate_store()
    { 
        
        global $wpdb;
        
        $site_url=site_url();
        $url_parse = wp_parse_url($site_url);
        
        $activate_store_arg = array(
        'shop'    => $url_parse['host'],
        'isActive'   => 1
        );
    
        inkseeker_store_status($activate_store_arg);
        
    }
    register_activation_hook( __FILE__, 'inkseeker_activate_store' );

    /*plugin deactivate hook*/
    function inkseeker_deactivate_store()
    { 
        
        global $wpdb;
        
        $url=Inkseekers_Base::INKR_API_HOST.'woocommerce/plugin_remove';
        $site_url=site_url();
        $url_parse = wp_parse_url($site_url);
        $deactivate_store_arg = array(
        'shop'    => $url_parse['host'],
        'isActive'   => 0
        );

        inkseeker_store_status($deactivate_store_arg);
        $remove_key =  $wpdb->query("DELETE FROM {$wpdb->prefix}woocommerce_api_keys WHERE description LIKE 'Inkseekers - API%'");
       
        
    }
    register_deactivation_hook( __FILE__, 'inkseeker_deactivate_store' );
    /**
     * send store status.
     */
    function inkseeker_store_status($body){

        $url=Inkseekers_Base::INKR_API_HOST.'woocommerce/plugin_remove';
        $args = array(
        'body'        => $body,
        'timeout'     => '5',
        'redirection' => '5',
        'httpversion' => '1.0',
        'blocking'    => true,
        'headers'     => array(),
        'cookies'     => array(),
        );

        $response = wp_remote_post( $url, $args );
        return $response;
    }

    
    