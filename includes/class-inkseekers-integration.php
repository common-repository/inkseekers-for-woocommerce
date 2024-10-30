<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inkseekers_Integration
{
    const INKR_API_CONNECT_STATUS = 'inkseekers_api_connect_status';
    const INKR_CONNECT_ERROR = 'inkseekers_connect_error';

	public static $_instance;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	public function __construct() {
		self::$_instance = $this;
	}

    /**
     * @return Inkseekers_Client
     * @throws InkseekersException
     */
	public function inkseekers_get_client() {

		require_once 'class-inkseekers-client.php';
		$client = new Inkseekers_Client( $this->get_option( 'inkseekers_key' ), $this->get_option( 'disable_ssl' ) == 'yes' );

		return $client;
	}

    /**
     * Check if the connection to inkseekers is working
     * @param bool $force
     * @return bool
     * @throws InkseekersException
     */
	public function inkseekers_is_connected( $force = false ) {
               global $wpdb;
               $api_key =  $wpdb->get_row("SELECT * FROM {$wpdb->prefix}woocommerce_api_keys WHERE description LIKE 'Inkseekers - API%'");
              //dont need to show error - the plugin is simply not setup
		if ( empty( $api_key->consumer_key ) ) {
			return false;
		}
              
		//validate length, show error
		if ( strlen( $api_key->consumer_key ) != 64 ) {
			$message      = 'Invalid API key - the key must be 36 characters long. Please ensure that your API key in <a href="%s">Settings</a> matches the one in your <a href="%s">Inkseekers dashboard</a>.';
			$settings_url = admin_url( 'admin.php?page=inkseekers-dashboard&tab=settings' );
			$inkseekers_url = Inkseekers_Base::inkseeker_get_host() . 'woocommerce/';
                        $this->set_connect_error(sprintf( $message, $settings_url, $inkseekers_url ) );

			return false;
		}

		//show connect status from cache
		if ( ! $force ) {
                  	$connected = get_transient( self::INKR_API_CONNECT_STATUS );
			if ( $connected && $connected['status'] == 1 ) {
				$this->clear_connect_error();
				return true;
			} else if ( $connected && $connected['status'] == 0 ) {    //try again in a minute
				return false;
			}
		}

		$client   = $this->inkseekers_get_client();
		$response = false;
                $this->clear_connect_error();
                set_transient( self::INKR_API_CONNECT_STATUS, array( 'status' => 1 ) );
                $response = true;
         	return $response;
	}

	/**
	 * Update connect error message
	 * @param string $error
	 */
	public function set_connect_error($error = '') {
		update_option( self::INKR_CONNECT_ERROR, $error );
	}

	/**
	 * Get current connect error message
	 */
	public function get_connect_error() {
		return get_option( self::INKR_CONNECT_ERROR, false );
	}

	/**
	 * Remove option used for storing current connect error
	 */
	public function clear_connect_error() {
		delete_option( self::INKR_CONNECT_ERROR );
	}

    /**
     * AJAX call endpoint for connect status check
     * @throws InkseekersException
     */
	public static function ajax_force_check_connect_status() {
		if ( Inkseekers_Integration::instance()->inkseekers_is_connected( true ) ) {
			die( 'OK' );
		}

		die( 'FAIL' );
	}

	/**
	 * Wrapper method for getting an option
	 * @param $name
	 * @param array $default
	 * @return bool
	 */
	public function get_option( $name, $default = array() ) {
		$options  = get_option( 'woocommerce_inkseekers_settings', $default );
		if ( ! empty( $options[ $name ] ) ) {
			return $options[ $name ];
		}

		return false;
	}

	/**
	 * Save the setting
	 * @param $settings
	 */
	public function update_settings( $settings ) {
		delete_transient( self::INKR_API_CONNECT_STATUS );    //remove the successful API status since API key could have changed
		update_option( 'woocommerce_inkseekers_settings', $settings );
	}
}