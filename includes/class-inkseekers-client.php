<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Inkseekers API client
 */
class Inkseekers_Client {

	private $key = false;
	private $lastResponseRaw;
	private $lastResponse;
	private $userAgent = 'Inkseekers WooCommerce Plugin';
	private $apiUrl;

	/**
	 * @param string $key Inkseekers Store API key
	 * @param bool|string $disable_ssl Force HTTP instead of HTTPS for API requests
	 *
	 * @throws InkseekersException if the library failed to initialize
	 */
	public function __construct( $key = '', $disable_ssl = false ) {
                global $wpdb;
                $key =  $wpdb->get_row("SELECT * FROM {$wpdb->prefix}woocommerce_api_keys WHERE description LIKE 'Inkseekers - API%'");
		$this->userAgent .= ' ' . Inkseekers_Base::VERSION . ' (WP ' . get_bloginfo( 'version' ) . ' + WC ' . WC()->version . ')';

		if ( ! function_exists( 'json_decode' ) || ! function_exists( 'json_encode' ) ) {
			throw new InkseekersException( 'PHP JSON extension is required for the Inkseekers API library to work!' );
		}
		if ( strlen( $key->consumer_key ) < 64 ) {
			throw new InkseekersException( 'Missing or invalid Inkseekers store key!' );
		}
		$this->key = $key;

		if ( $disable_ssl ) {
			$this->apiUrl = str_replace( 'https://', 'http://', $this->apiUrl );
		}

		//setup api host
		$this->apiUrl = Inkseekers_Base::inkseeker_get_api_host();
	}
  
    /**
     * Perform a PATCH request to the API
     * @param string $path Request path
     * @param array $data Request body data as an associative array
     * @param array $params
     * @return mixed API response
     * @throws InkseekersApiException if the API call status code is not in the 2xx range
     * @throws InkseekersException if the API call has failed or the response is invalid
     */
    public function patch( $path, $data = array(), $params = array() )
    {
        return $this->request( 'PATCH', $path, $params, $data );
    }

    /**
     * Return raw response data from the last request
     * @return string|null Response data
     */
	public function getLastResponseRaw() {
		return $this->lastResponseRaw;
	}
    /**
     * Return decoded response data from the last request
     * @return array|null Response data
     */
	public function getLastResponse() {
		return $this->lastResponse;
	}

	/**
	 * Internal request implementation
	 *
	 * @param $method
	 * @param $path
	 * @param array $params
	 * @param null $data
	 *
	 * @return
	 * @throws InkseekersApiException
	 * @throws InkseekersException
	 */
	private function request( $method, $path, array $params = array(), $data = null ) {
               $this->lastResponseRaw = null;
		$this->lastResponse    = null;

		$url = trim( $path, '/' );

		if ( ! empty( $params ) ) {
			$url .= '?' . http_build_query( $params );
		}

		$request = array(
			'timeout'    => 10,
			'user-agent' => $this->userAgent,
			'method'     => $method,
			'headers'    => array( 'Authorization' => 'Basic ' . base64_encode( $this->key ) ),
			'body'       => $data !== null ? json_encode( $data ) : null,
		);

		$result = wp_remote_get( $this->apiUrl . $url, $request );

		//allow other methods to hook in on the api result
		$result = apply_filters( 'inkseekers_api_result', $result, $method, $this->apiUrl . $url, $request );

		if ( is_wp_error( $result ) ) {
			throw new InkseekersException( "API request failed - " . $result->get_error_message() );
		}
		$this->lastResponseRaw = $result['body'];
		$this->lastResponse    = $response = json_decode( $result['body'], true );

		if ( ! isset( $response['code'], $response['result'] ) ) {
			throw new InkseekersException( 'Invalid API response' );
		}
		$status = (int) $response['code'];
		if ( $status < 200 || $status >= 300 ) {
			throw new InkseekersApiException( (string) $response['result'], $status );
		}

		return $response['result'];
	}
}

/**
 * Class InkseekersException Generic Inkseekers exception
 */
class InkseekersException extends Exception {}
/**
 * Class InkseekersException Inkseekers exception returned from the API
 */
class InkseekersApiException extends InkseekersException {}