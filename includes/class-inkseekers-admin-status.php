<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class Inkseekers_Admin_Status {

	const INKR_STATUS_OK = 1;
	const INKR_STATUS_WARNING = 0;
	const INKR_STATUS_FAIL = -1;
	const INKR_STATUS_NOT_CONNECTED = 2;

	const API_KEY_SEARCH_STRING = 'Inkseekers';
	const INKR_WEBHOOK_NAME = 'Inkseekers Integration';
	const INKR_REMOTE_REQUEST_URL = 'hook/woocommerce?store=1';
	const INKR_REMOTE_REQUEST_TOPIC = 'woo.plugin.test';
	const INKR_STATUS_ISSUE_COUNT = 'inkseekers_status_issue_count';
	const INKR_CACHED_CHECKLIST = 'inkseekers_cached_checklist';
         const STATUS_CONNECTED_DISPLAYABLE_METHODS = array(
        'check_INKR_webhooks',
        'check_INKR_sync_errors',
        'check_INKR_API_key',
        'inkseekers_check_WC_API_access'
    );

    public static $_instance;
    
	public static function inkseekers_getChecklistItems() {
	    return array(
            array(
                'name'        => __( 'Connection to Inkseekers API', 'inkseekers' ),
                'description' => __( 'Is your store successfully connected to Inkseekers API?', 'inkseekers' ),
                'method'      => 'check_INKR_API_connect',
            ),
            array(
                'name'        => __( 'Inkseekers API key is set', 'inkseekers' ),
                'description' => __( 'Your store needs access to Inkseekers API to use most of it\'s features like shipping rates, tax rates and other settings.', 'inkseekers' ),
                'method'      => 'check_INKR_API_key',
            ),
            array(
                'name'        => __( 'WordPress Permalinks', 'inkseekers' ),
                'description' => __( 'WooCommerce API will not work unless your permalinks in Settings > Permalinks are set up correctly. Make sure you that they are NOT set to "plain".', 'inkseekers' ),
                'method'      => 'inkseekers_check_permalinks',
            ),
            array(
                'name'        => __( 'WordPress version', 'inkseekers' ),
                'description' => __( 'WordPress should always be updated to the latest version. Updates can be installed from your WordPress admin dashboard.', 'inkseekers' ),
                'method'      => 'inkseekers_check_WP_version',
            ),
            array(
                'name'        => __( 'WooCommerce Webhooks', 'inkseekers' ),
                'description' => __( 'Inkseekers requires WooCommerce webhooks to be set up to quickly capture you incoming orders, products updates etc.', 'inkseekers' ),
                'method'      => 'check_INKR_webhooks',
            ),
            array(
                'name'        => __( 'WooCommerce API keys are set', 'inkseekers' ),
                'description' => __( 'Inkseekers needs access to your WooCommerce API for the integration to work - otherwise we can\'t sync your store, push or pull your products etc.', 'inkseekers' ),
                'method'      => 'inkseekers_check_WC_API_access',
            ),
            array(
                'name'        => __( 'WooCommerce authentication URL access', 'inkseekers' ),
                'description' => __( 'Inkseekers needs access to WooCommerce API authorize page. This sometimes may get blocked due to hosts having unnecessarily intrusive security checks in place that prevent WooCommerce API authentication from working (for example mod_security rule #1234234). If this check fails, you will not be able authorize Inkseekers app.', 'inkseekers' ),
                'method'      => 'inkseekers_check_WC_auth_url_access',
            ),
            array(
                'name'        => __( 'WordPress remote requests', 'inkseekers' ),
                'description' => __( 'WordPress needs to be able to connect to Inkseekers server to call webhooks. If this check fails, contact your hosting support.', 'inkseekers' ),
                'method'      => 'inkseekers_check_remote_requests',
            ),
            array(
                'name'        => __( 'WordPress Site URL', 'inkseekers' ),
                'description' => __( 'If your currently setup WordPress site URL is redirected to another URL the integration might not work correctly. Typically this happens with incorrect http to https redirects. Go to Settings > General to fix this.' , 'inkseekers' ),
                'method'      => 'inkseekers_check_site_url_redirect',
            ),
            array(
                'name'        => __( 'Recent store sync errors', 'inkseekers' ),
                'description' => __( 'Inkseekers will connect to your store\'s API regularly and sync your latest products, orders etc. If there have been any recent issues with sync, this check will fail.', 'inkseekers' ),
                'method'      => 'check_INKR_sync_errors',
            ),
            array(
                'name'        => __( 'Write permissions', 'inkseekers' ),
                'description' => __( 'Make the uploads directory writable. This is required for mockup generator product push to work correctly. Contact your hosting provider if you need help with this.', 'inkseekers' ),
                'method'      => 'inkseekers_check_uploads_write',
            ),
            array(
                'name'        => __( 'PHP memory limit', 'inkseekers' ),
                'description' => __( 'Set PHP allocated memory limit to at least 128mb. Contact your hosting provider if you need help with this.', 'inkseekers' ),
                'method'      => 'inkseekers_check_PHP_memory_limit',
            ),
            array(
                'name'        => __( 'PHP script time limit', 'inkseekers' ),
                'description' => __( 'Set PHP script execution time limit to at least 30 seconds. This is required to successfully push products with many variants. Contact your hosting provider if you need help with this.', 'inkseekers' ),
                'method'      => 'inkseekers_check_PHP_time_limit',
            ),
            array(
                'name'        => __( 'W3 Total Cache DB Cache', 'inkseekers' ),
                'description' => __( 'If you are using W3 Total Cache, the database caching feature needs to be disabled since it can cause issues with product push to store.', 'inkseekers' ),
                'method'      => 'inkseekers_check_W3_db_cache',
                'silent'      => true,
            ),
            array(
                'name'        => __( 'WP SpamShield', 'inkseekers' ),
                'description' => __( 'If you are using WP SpamShield, you might experience problems connecting to Inkseekers and pushing products.', 'inkseekers' ),
                'method'      => 'inkseekers_check_wp_spamshield',
                'silent'      => true,
            ),
            array(
                'name'        => __( 'Remove Print Aura plugin', 'inkseekers' ),
                'description' => __( 'Print Aura plugin is known to cause issues so it needs to be removed.', 'inkseekers' ),
                'method'      => 'inkseekers_check_printaura_plugin',
                'silent'      => true,
            ),
        );
    }

    /**
     * @return Inkseekers_Admin_Status
     */
	public static function instance() {
        if ( ! function_exists( 'get_plugins' ) ) {
            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        }

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Setup the inkseekers_view variables
	 */
	public static function inkseekers_view() {

		$status = self::instance();
		$status->inkseekers_render();
	}

	/**
	 * inkseekers_render the inkseekers_view
	 */
	public function inkseekers_render() {
        Inkseekers_Admin::inkseekers_load_template( 'header', array( 'tabs' => Inkseekers_Admin::inkseekers_get_tabs() ) );

		$checklist = self::inkseekers_get_checklist( true );
		if ( $checklist ) {
			Inkseekers_Admin::inkseekers_load_template( 'status-table', array( 'checklist' => $checklist ) );
		} else {
			Inkseekers_Admin::inkseekers_load_template( 'ajax-loader', array( 'action' => 'get_inkseekers_status_checklist', 'message' => __( 'Testing your store (this may take up to 30 seconds)...', 'inkseekers' ) ) );
		}

		Inkseekers_Admin::inkseekers_load_template( 'footer' );
	}

	/**
	 * Build the content for status page
	 */
	public static function inkseekers_render_status_table_ajax() {

		$checklist = self::inkseekers_get_checklist();
		Inkseekers_Admin::inkseekers_load_template( 'status-table', array( 'checklist' => $checklist ) );

		exit;
	}

    /**
     * Run the tests
     * @param bool $only_cached_results
     * @return array
     * @throws InkseekersException
     */
	public static function inkseekers_get_checklist( $only_cached_results = false ) {

		$status = self::instance();

		$list = get_transient( Inkseekers_Admin_Status::INKR_CACHED_CHECKLIST );

		if ( $only_cached_results || $list ) {
			return $list;
		}

		$list                   = array();
		$list['overall_status'] = true;
		$issueCount             = 0;

		foreach ( self::inkseekers_getChecklistItems() as $item ) {
			$list_item                = array();
			$list_item['name']        = $item['name'];
			$list_item['description'] = $item['description'];

			if ( method_exists( $status, $item['method'] ) ) {
				$list_item['status'] = $status->{$item['method']}();

				// Do not display status for methods that are depending on connection status to Inkseekers
				if ( ! Inkseekers_Integration::instance()->inkseekers_is_connected(true) && in_array( $item['method'], self::STATUS_CONNECTED_DISPLAYABLE_METHODS ) ) {
				    continue;
                }

				if ( $status->inkseekers_should_result_be_visible( $list_item['status'], $item ) ) {
					$list['items'][] = $list_item;
				}

				if ( $list_item['status'] == self::INKR_STATUS_FAIL || $list_item['status'] == self::INKR_STATUS_NOT_CONNECTED ) {
					$list['overall_status'] = false;
					$issueCount ++;
				}
			}
		}

		set_transient( Inkseekers_Admin_Status::INKR_CACHED_CHECKLIST, $list, MINUTE_IN_SECONDS );
		set_transient( Inkseekers_Admin_Status::INKR_STATUS_ISSUE_COUNT, $issueCount, HOUR_IN_SECONDS );

		return $list;
	}

	/**
	 * Execute only one test
	 * @param $method
	 * @return mixed
	 */
	public function inkseekers_run_single_test( $method ) {
		if ( method_exists( $this, $method ) ) {
			return $this->{$method}();
		}
		return false;
	}

	/**
	 * @param $status
	 * @param bool $item
	 *
	 * @return int
	 */
	private function inkseekers_should_result_be_visible( $status, $item = false ) {

		if ( ! isset( $item['silent'] ) || ( $item['silent'] === true && $status === self::INKR_STATUS_FAIL ) ) {   //silent items are only shown on FAIL
			return true;
		}

		return false;
	}

    /**
     * Function for checking if thumbnails are resized
     */
	private function inkseekers_check_uploads_write() {

		$upload_dir = wp_upload_dir();
		if ( is_writable( $upload_dir['basedir'] ) ) {
			return self::INKR_STATUS_OK;
		}

		return self::INKR_STATUS_FAIL;
	}

	/**
	 * @return int
	 */
	private function inkseekers_check_PHP_memory_limit() {

		$memory_limit = ini_get( 'memory_limit' );

		if ( preg_match( '/^(\d+)(.)$/', $memory_limit, $matches ) ) {
			if ( $matches[2] == 'M' ) {
				$memory_limit = $matches[1] * 1024 * 1024; // nnnM -> nnn MB
			} else if ( $matches[2] == 'K' ) {
				$memory_limit = $matches[1] * 1024; // nnnK -> nnn KB
			}
		}

		$ok = ( $memory_limit >= 128 * 1024 * 1024 ); // at least 128M?

		if ( $ok ) {
			return self::INKR_STATUS_OK;
		}

		return self::INKR_STATUS_FAIL;
	}

	/**
	 * @return int
	 */
	private function inkseekers_check_WP_version() {

		$current = get_bloginfo( 'version' );

		try {
			$url      = 'https://api.wordpress.org/core/version-check/1.7/';
			$response = wp_remote_get( $url );

			if ( ! is_wp_error( $response ) ) {
				$json = $response['body'];
				$obj  = json_decode( $json );
			}

			if ( empty( $obj ) ) {
				return self::INKR_STATUS_FAIL;
			}

			$version = $obj->offers[0];
			$latest  = $version->version;

		} catch ( Exception $e ) {
			return self::INKR_STATUS_FAIL;
		}

		if ( ! $latest ) {
			return self::INKR_STATUS_FAIL;
		}

        if ( version_compare( $current, $latest, '>=' ) ) {
            return self::INKR_STATUS_OK;
        }

		return self::INKR_STATUS_FAIL;
	}

	/**
	 * @return int
	 */
	private function check_INKR_webhooks() {
	    global $wpdb;

		// Get the webhooks
		// In version 3.3 the webhooks are stored in separate table, before that they were stored in posts table
		if ( version_compare( WC()->version, '3.3', '<' ) ) {

			// Query args
			$args = array(
				'post_type'           => 'shop_webhook',
				'nopaging'            => true,
				'ignore_sticky_posts' => true,
				's'                   => self::INKR_WEBHOOK_NAME,
				'post_status'         => 'published',
			);

			$webhook_results = new WP_Query( $args );
			$webhooks        = $webhook_results->posts;
			$count           = count( $webhooks ) > 0;
		} else {
			$count = $wpdb->get_var(
				$wpdb->prepare( "SELECT COUNT(*) as webhook_count FROM {$wpdb->prefix}wc_webhooks WHERE name = %s",
					self::INKR_WEBHOOK_NAME
				) );
		}

		if ( $count > 0 ) {
			return self::INKR_STATUS_OK;
		}

        return self::INKR_STATUS_OK;
	}

	/**
	 * @return int
	 */
	private function inkseekers_check_WC_API_access() {

		global $wpdb;

		//if any keys are set
		$count    = $wpdb->get_var(
		    "SELECT COUNT(*) as key_count FROM {$wpdb->prefix}woocommerce_api_keys"
                );

		if ( $count == 0 ) {
			return self::INKR_STATUS_FAIL;
		}

		// Get the API key with matching description
        $inkseekersKey = esc_sql( $wpdb->esc_like( wc_clean( self::API_KEY_SEARCH_STRING ) ) );
		$queryArg = '%'. $wpdb->esc_like( $inkseekersKey ) . '%';

        $key = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$wpdb->prefix}woocommerce_api_keys WHERE description LIKE %s ORDER BY last_access LIMIT 1",
                $queryArg
            )
        );

		if ( ! empty( $key ) && $key->permissions == 'read_write' ) {
			return self::INKR_STATUS_OK;
		}

		return self::INKR_STATUS_WARNING;
	}

	/**
	 * @return int
	 */
	private function check_INKR_API_key() {
                global $wpdb;
                $option =  $wpdb->get_row("SELECT * FROM {$wpdb->prefix}woocommerce_api_keys WHERE description LIKE 'Inkseekers - API%'");
		if ( ! empty( $option->consumer_key ) && strlen( $option->consumer_key ) == 64 ) {
			return self::INKR_STATUS_OK;
		}

		return self::INKR_STATUS_FAIL;
	}

    /**
     * @return int
     * @throws InkseekersException
     */
	private function check_INKR_API_connect() {

		if ( Inkseekers_Integration::instance()->inkseekers_is_connected(true) ) {
			return self::INKR_STATUS_OK;
		}

		return self::INKR_STATUS_NOT_CONNECTED;
	}

	/**
	 * @return int
	 */
	private function inkseekers_check_PHP_time_limit() {
		$time_limit = ini_get( 'max_execution_time' );

		if ( !$time_limit || $time_limit >= 30 ) {
			return self::INKR_STATUS_OK;
		}

		return self::INKR_STATUS_FAIL;
	}

	/**
	 * @return int
	 */
	private function check_INKR_sync_errors() {

		$sync_log = get_option( Inkseekers_Request_log::INKR_OPTION_INCOMING_API_REQUEST_LOG, array() );
		if ( empty( $sync_log ) ) {
			return self::INKR_STATUS_OK;    //no results means no errors
		}

		$sync_log = array_reverse( $sync_log );
		$sync_log = array_slice( $sync_log, 0, 6 );   //we only care about last to syncs

		foreach ( $sync_log as $sl ) {
			if ( ! empty( $sl['result'] ) && $sl['result'] == 'ERROR' ) {
				return self::INKR_STATUS_FAIL;
			}
		}

		return self::INKR_STATUS_OK;
	}

	/**
	 * @return int
	 */
	private function inkseekers_check_W3_db_cache() {

		if ( ! is_plugin_active( 'w3-total-cache/w3-total-cache.php' ) ) {
			return self::INKR_STATUS_OK;
		}

		$w3tc_config_file = get_home_path() . 'wp-content/w3tc-config/master.php';
		if ( file_exists( $w3tc_config_file ) && is_readable( $w3tc_config_file ) ) {
			$content = @file_get_contents( $w3tc_config_file );
			$config  = @json_decode( substr( $content, 14 ), true );

			if ( is_array( $config ) && ! empty( $config['dbcache.enabled'] ) ) {
				return ! $config['dbcache.enabled'];
			}
		}

		return self::INKR_STATUS_OK;
	}

	/**
	 * @return int
	 */
	private function inkseekers_check_permalinks() {

		$permalinks = get_option( 'permalink_structure', false );

		if ( $permalinks && strlen( $permalinks ) > 0 ) {
			return self::INKR_STATUS_OK;
		}

		return self::INKR_STATUS_FAIL;
	}

	/**
	 * @return int
	 */
	private function inkseekers_check_printaura_plugin() {

		if ( ! is_plugin_active( 'printaura-woocommerce-api/printaura-woocommerce-api.php' ) ) {
			return self::INKR_STATUS_OK;
		}

		return self::INKR_STATUS_FAIL;
	}

	/**
	 * @return int
	 */
	private function inkseekers_check_wp_spamshield() {

		if ( ! is_plugin_active( 'wp-spamshield/wp-spamshield.php' ) ) {
			return self::INKR_STATUS_OK;
		}

		return self::INKR_STATUS_FAIL;
	}

	/**
	 * @return int
	 */
	private function inkseekers_check_remote_requests() {

		// Setup request args.
		$http_args = array(
			'method'      => 'POST',
			'timeout'     => MINUTE_IN_SECONDS,
			'redirection' => 0,
			'httpversion' => '1.0',
			'blocking'    => true,
			'user-agent'  => sprintf( 'WooCommerce/%s Hookshot (WordPress/%s)', WC_VERSION, $GLOBALS['wp_version'] ),
			'body'        => trim( json_encode( array( 'test' => true ) ) ),
			'headers'     => array( 'Content-Type' => 'application/json' ),
			'cookies'     => array(),
		);

		// Add custom headers.
		$http_args['headers']['X-WC-Webhook-Source'] = home_url( '/' ); // Since 2.6.0.
		$http_args['headers']['X-WC-Webhook-Topic']  = self::INKR_REMOTE_REQUEST_TOPIC;

		// Webhook away!
		$response = wp_safe_remote_request( Inkseekers_Base::inkseeker_get_api_host() . self::INKR_REMOTE_REQUEST_URL, $http_args );

		if ( is_wp_error( $response ) ) {
			return self::INKR_STATUS_FAIL;
		}

		return self::INKR_STATUS_OK;
	}

	/**
	 * @return int
	 */
	private function inkseekers_check_WC_auth_url_access() {
		$url       = home_url( '/' ) . 'wc-auth/v1/authorize?app_name=Inkseekers&scope=read_write&user_id=1&return_url=https%3A%2F%2Fapp.inkseekers.com%2F&callback_url=https%3A%2F%2Fapi.inkseekers.com';
		$http_args = array(
			'timeout'    => 60,
			'method'     => 'GET',
			'user-agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.82 Safari/537.36',
		);

		$response = wp_safe_remote_get( $url, $http_args );

		if ( is_wp_error( $response ) ) {
			return self::INKR_STATUS_FAIL;
		}

		$code = $response['response']['code'];

		if ( $code == 200 ) {
			return self::INKR_STATUS_OK;
		}

		return self::INKR_STATUS_FAIL;
	}

    /**
     * @return int
     */
	private function inkseekers_check_site_url_redirect()
    {

        $regular_site_url_req = wp_remote_head( get_option( 'siteurl' ) . '/wp-json/', array('redirection' => 0));

        //need to not trigger issues for subfolder wordpress setups
        $slashed_site_url_req = wp_remote_head( trailingslashit(get_option( 'siteurl' )), array('redirection' => 0));

        if (is_wp_error($regular_site_url_req) || is_wp_error($slashed_site_url_req)) {
            return self::INKR_STATUS_FAIL;
        }

        /** @var WP_HTTP_Requests_Response $response */
        $regular_response = $regular_site_url_req['http_response'];

        /** @var WP_HTTP_Requests_Response $slashed_response */
        $slashed_response = $slashed_site_url_req['http_response'];

        if ($regular_response->get_status() == 200 || $slashed_response->get_status() == 200) {
            return self::INKR_STATUS_OK;
        }

        if (in_array($regular_response->get_status(), array(301, 302, 303, 307))) {
            return self::INKR_STATUS_FAIL;
        }

        return self::INKR_STATUS_WARNING;
    }
}
