<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inkseekers_Admin_Dashboard {

	const API_KEY_SEARCH_STRING = 'Inkseekers';

	public static $_instance;

	/**
	 * @return Inkseekers_Admin_Dashboard
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Inkseekers_Admin_Dashboard constructor.
	 */
	function __construct() {

	}

    /**
     * Show the inkseekers_view
     * @throws InkseekersException
     */
	public static function inkseekers_view() {
        global $wpdb;
		$dashboard = self::instance();
                $api_key =  $wpdb->get_row("SELECT * FROM {$wpdb->prefix}woocommerce_api_keys WHERE description LIKE 'Inkseekers - API%'");
				if(!empty($api_key)){
					$api_consumer_key = $api_key->consumer_key;
				}else{
					$api_consumer_key = '';
				}
                $connect_status = Inkseekers_Integration::instance()->inkseekers_is_connected();
                    if ( $connect_status ) {
                            $dashboard->inkseekers_inkseekers_render_dashboard();
                    } else if(!$connect_status && strlen($api_consumer_key) > 0) {
                            $dashboard->inkseekers_inkseekers_render_connect_error();
                    } else {
                            $dashboard->inkseekers_inkseekers_render_connect();
                    }
                  
	}

	/**
	 * Display the Inkseekers connect page
	 */
	public function inkseekers_inkseekers_render_connect() {

		$status = Inkseekers_Admin_Status::instance();
		$issues = array();

		$permalinks_set = $status->inkseekers_run_single_test( 'inkseekers_check_permalinks' );

		if ( $permalinks_set == Inkseekers_Admin_Status::INKR_STATUS_FAIL ) {
			$message      = 'WooCommerce API will not work unless your permalinks are set up correctly. Go to <a href="%s">Permalinks settings</a> and make sure that they are NOT set to "plain".';
			$settings_url = admin_url( 'options-permalink.php' );
			$issues[]     = sprintf( $message, $settings_url );
		}

		if ( strpos( get_site_url(), 'localhost' ) ) {
			$issues[] = 'You can\'t connect to Inkseekers from localhost. Inkseekers needs to be able reach your site to establish a connection.';
		}

                Inkseekers_Admin::inkseekers_load_template( 'header', array( 'tabs' => Inkseekers_Admin::inkseekers_get_tabs() ) );
		Inkseekers_Admin::inkseekers_load_template( 'connect', array(
				'consumer_key'       => $this->inkseekers_get_consumer_key(),
				'waiting_sync'       => isset( $_GET['sync-in-progress'] ),
				'consumer_key_error' => isset( $_GET['consumer-key-error'] ),
				'issues'             => $issues,
			)
		);

		if ( isset( $_GET['sync-in-progress'] ) ) {
			$emit_auth_response = 'Inkseekers_Connect.send_return_message();';
			Inkseekers_Admin::inkseekers_load_template( 'inline-script', array( 'script' => $emit_auth_response ) );
		}

		Inkseekers_Admin::inkseekers_load_template('footer');
	}

	/**
	 * Display the Inkseekers connect error page
	 */
	public function inkseekers_inkseekers_render_connect_error() {

		Inkseekers_Admin::inkseekers_load_template( 'header', array( 'tabs' => Inkseekers_Admin::inkseekers_get_tabs() ) );

		$connect_error = Inkseekers_Integration::instance()->get_connect_error();
		if ( $connect_error ) {
			Inkseekers_Admin::inkseekers_load_template('error', array('error' => $connect_error));
		}

		Inkseekers_Admin::inkseekers_load_template('footer');
	}

	/**
	 * Display the dashboard
	 */
	public function inkseekers_inkseekers_render_dashboard() {
		Inkseekers_Admin::inkseekers_load_template( 'header', array( 'tabs' => Inkseekers_Admin::inkseekers_get_tabs() ) );
		$error = false;
		Inkseekers_Admin::inkseekers_load_template( 'quick-links' );

		if ( isset( $_GET['sync-in-progress'] ) ) {
			$emit_auth_response = 'Inkseekers_Connect.send_return_message();';
			Inkseekers_Admin::inkseekers_load_template( 'inline-script', array( 'script' => $emit_auth_response ) );
		}

		Inkseekers_Admin::inkseekers_load_template( 'footer' );
	}

	/**
	 * Get the last used consumer key fragment and use it for validating the address
	 * @return null|string
	 */
	private function inkseekers_get_consumer_key() {

		global $wpdb;

		// Get the API key
        $inkseekersKey = '%' . esc_sql( $wpdb->esc_like( wc_clean( self::API_KEY_SEARCH_STRING ) ) ) . '%';
        $consumer_key = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT truncated_key FROM {$wpdb->prefix}woocommerce_api_keys WHERE description LIKE %s ORDER BY key_id DESC LIMIT 1",
                $inkseekersKey
            )
        );

		//if not found by description, it was probably manually created. try the last used key instead
		if ( ! $consumer_key ) {
			$consumer_key = $wpdb->get_var(
			    "SELECT truncated_key FROM {$wpdb->prefix}woocommerce_api_keys ORDER BY key_id DESC LIMIT 1"
            );
		}

		return $consumer_key;
	}

}