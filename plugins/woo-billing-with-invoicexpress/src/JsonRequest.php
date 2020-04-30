<?php
namespace Webdados\InvoiceXpressWooCommerce;

use Webdados\InvoiceXpressWooCommerce\JsonRequestException as JsonRequestException;

use Curl;

class JsonRequest {


	// invoicexpress app domain
	protected $domain;
	// api token needed to authenticate
	protected $api_token;
	// api url used to Curl
	protected $api_url;
	// the raw request to be done
	protected $request;
	// arguments that are needed for this request
	protected $args;
	// curl object
	protected $curl;


	/*
	 * Constructor
	 */
	public function __construct( $parameters = array() ) {
		$this->curl = new Curl\Curl();
		// InvoiceXpress settings
		$this->api_token = get_option( 'hd_wc_ie_plus_api_token' );
		$this->domain    = get_option( 'hd_wc_ie_plus_subdomain' );
		// auto-populate object..
		foreach ( $parameters as $key => $value ) {
			$this->$key = $value;
		}
		//Set the API URL
		$this->setApiUrl();
		//Set basic Curl options
		$this->setBasicCurlOptions();
	}

	/*
	 * Set the API URL
	 */
	public function setApiUrl() {
		$this->api_url = sprintf(
			'https://%1$s.app.invoicexpress.com/%2$s?api_key=%3$s',
			$this->domain,
			$this->request,
			$this->api_token
		);
	}

	/*
	 * Set basic CURL options
	 */
	public function setBasicCurlOptions() {
		$this->curl->setUserAgent( '' );
		$this->curl->setReferrer( '' );
		$this->curl->setHeader( 'Accept', 'application/json; charset=utf-8' );
		$this->curl->setHeader( 'Content-Type', 'application/json; charset=utf-8' );
		$this->curl->setOpt( CURLOPT_MAXREDIRS, 2 );
		$this->curl->setOpt( CURLOPT_TIMEOUT, 30 );
		$this->curl->setOpt( CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1 );
		if ( defined( 'WP_LOCAL_DEV' ) && WP_LOCAL_DEV ) {
			$this->curl->setOpt( CURLOPT_SSL_VERIFYPEER, false );
		}
	}

	/*
	 * Parses the JSON response into an object
	 * @return php object with the parsed JSON
	 */
	public function jsonToObject( $json ) {
		if ( $json ) {
			if ( $object = json_decode( $json ) ) {
				if ( isset( $object->errors ) && is_array( $object->errors ) && count( $object->errors ) > 0 ) {
					$error_message = array();
					foreach ( $object->errors as $error ) {
						if ( isset( $error->error ) ) {
							$error_message[] = $error->error;
						}
					}
					return array(
						'success'       => false,
						'error_code'    => 0,
						'error_message' => implode( ', ', $error_message ),
					);
				} else {
					return array(
						'success' => true,
						'object' => $object,
					);
				}
			} else {
				return array(
					'success'       => false,
					'error_code'    => 0,
					'error_message' => 'Unable to decode the JSON string',
				);
			}
		} else {
			return array(
				'success'       => false,
				'error_code'    => 0,
				'error_message' => 'Not an JSON string',
			);
		}
	}

	/*
	 * Method that starts the get request
	 * @return php array with the parsed response
	 */
	public function getRequest() {
		try {
			$json = $this->processGetRequest();
			return $this->jsonToObject( $json );
		} catch( JsonRequestException $e ) {
			return array(
				'success'       => false,
				'error_code'    => $e->getCode(),
				'error_message' => $e->getMessage(),
			);
		}
	}

	/*
	 * Method that starts the request
	 * @return php array with the parsed response
	 */
	public function postRequest() {
		try {
			$json = $this->processPostRequest();
			return $this->jsonToObject( $json );
		} catch( JsonRequestException $e ) {
			return array(
				'success'       => false,
				'error_code'    => $e->getCode(),
				'error_message' => $e->getMessage(),
			);
		}
	}

	/*
	 * Method that starts the request
	 * @return php array with the parsed response
	 */
	public function putRequest() {
		try {
			$result = $this->processPutRequest();
			return array(
				'success' => $result,
			);
		} catch( JsonRequestException $e ) {
			return array(
				'success'       => false,
				'error_code'    => $e->getCode(),
				'error_message' => $e->getMessage(),
			);
		}
	}

	/*
	 * Better error message
	 */
	protected function errorMessage( $message, $return ) {
		$message = array( trim( $message ) );
		if ( ! empty( $return ) ) {
			if ( $return = json_decode( $return ) ) {
				if ( isset( $return->errors ) && count( $return->errors ) > 0 ) {
					foreach ( $return->errors as $error ) {
						if ( isset( $error->error ) && ! empty( $error->error ) ) {
							$message[] = trim( $error->error );
						}
					}
				}
			}
		}
		return implode( ' - ', $message );
	}

	/*
	 * Core method that does the raw request and returns the JSON response using CURL (GET).
	 */
	protected function processGetRequest() {
		//On GET all arguments are added to the URL, and because we already got the api key, we'll do it like this
		if ( isset( $this->args ) && is_array( $this->args ) && count( $this->args ) > 0 ) {
			$this->api_url .= '&'. http_build_query( $this->args );
		}
		//Do it
		$this->curl->get( $this->api_url );
		if ( $this->curl->error ) {
			$array = array(
				'code'     => $this->curl->error_code,
				'message'  => $this->errorMessage( $this->curl->error_message, $this->curl->response ),
			);
			throw new JsonRequestException( $array );
		} else {
			return $this->curl->response;
		}
	}

	/*
	 * Core method that does the raw request and returns the JSON response using CURL (POST).
	 */
	protected function processPostRequest() {
		//Do it
		$this->curl->post( $this->api_url, json_encode( $this->args ) );
		if ( $this->curl->error ) {
			$array = array(
				'code'     => $this->curl->error_code,
				'message'  => $this->errorMessage( $this->curl->error_message, $this->curl->response ),
			);
			throw new JsonRequestException( $array );
		} else {
			return $this->curl->response;
		}
	}

	/*
	 * Core method that does the raw request and returns the JSON response using CURL (PUT).
	 */
	protected function processPutRequest() {
		//On PUT all arguments (the data) are added as fields (body) - curl->put does not work (why?)
		$data = json_encode( $this->args );
		$this->curl->setopt( CURLOPT_URL, $this->api_url );
		$this->curl->setopt( CURLOPT_CUSTOMREQUEST, 'PUT' );
		$this->curl->setopt( CURLOPT_POSTFIELDS, $data );
		//Do it
		$this->curl->_exec();
		if ( $this->curl->error ) {
			$array = array(
				'code'     => $this->curl->error_code,
				'message'  => $this->errorMessage( $this->curl->error_message, $this->curl->response ),
			);
			throw new JsonRequestException( $array );
		} else {
			return true;
		}
	}

	/*
	 * Method that starts the get request while the status code is not the specified
	 * @return php array with the parsed response
	 */
	public function getRequestWhileStatusCode( $code ) {
		$i = 0;
		do {
			try {
				$json = $this->processGetRequest();
			} catch( JsonRequestException $e ) {
				//Do not return yet
			}
			if ( $i > 0 ) {
				sleep( 1 );
			}
			$i++;
			if ( $i == apply_filters( 'invoicexpress_woocommerce_get_pdf_timeout', 10 ) ) {
				break;
			}
			if ( $this->curl->error ) {
				return array(
					'success'       => false,
					'error_code'    => $this->curl->error_code,
					'error_message' => $this->curl->response,
				);
			}
		} while ( $this->curl->http_status_code != $code );
		return $this->jsonToObject( $json );
	}
}
