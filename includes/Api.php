<?php

namespace Arctic;

/**
 * Class Api
 * This handles authorization and interaction with the Arctic Reservations API.
 * This is a singleton class.
 *
 * To specify configuration, use the init function.
 *
 *
 * \Arctic\Api::init('installation_name','api_username','api_password');
 */
class Api
{
	const VERSION = '2.0beta2';

	const AUTH_TYPE_BASIC = 'basic';
	const AUTH_TYPE_OAUTH = 'oauth';

	const ERRORS_EXCEPTION = 'exception';
	const ERRORS_ERROR = 'error';
	const ERRORS_WARNING = 'warning';
	const ERRORS_SILENT = 'silent';

	const METHOD_GET = 'GET';
	const METHOD_POST = 'POST';
	const METHOD_PUT = 'PUT';
	const METHOD_PATCH = 'PATCH';
	const METHOD_DELETE = 'DELETE';

	private static $_instance;
	private static $_last_error;

	private $_config;

	/**
	 * Authentication token for OAuth based authentication.
	 * @var string|null
	 */
	private $_token;

	/**
	 * Is current token from the cache manager? Tracked to allow automatically rerun queries in case token has expired.
	 * @var bool
	 */
	private $_cached_token = false;

	/**
	 * Used to track conditions where authenticated request should be reattempt (primarily in case token has expired).
	 * @var bool
	 */
	private $_should_retry;

	/**
	 * Tracks the status of the last response, which is used to provide more detailed exception types.
	 * @var int
	 */
	private $_last_status;

	/**
	 * @var Cache\Manager
	 */
	private $_cache_manager;

	private function __construct() {
	}

	public function __clone() {
		throw new \Exception('Can not clone singleton class.');
	}

	/**
	 * @return self
	 */
	public static function getInstance() {
		if ( !isset( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public static function autoloadClass($class) {
		$class = ltrim($class, '\\');

		// only process "Arctic\" vendor code
		if (0 !== substr_compare($class, 'Arctic\\', 0, 7)) return;
		$class = substr($class, 7);

		// convert to file name
		$file_name  = '';

		// has namespace?
		if ($last_ns_position = strrpos($class, '\\')) {
			$namespace = substr($class, 0, $last_ns_position);
			$class = substr($class, $last_ns_position + 1);
			$file_name  = str_replace('\\', DIRECTORY_SEPARATOR, $namespace) . DIRECTORY_SEPARATOR;
		}

		$file_name .= str_replace('_', DIRECTORY_SEPARATOR, $class) . '.php';

		// add full path
		$file_name = __DIR__ . DIRECTORY_SEPARATOR . $file_name;

		// if found, load
		if ( file_exists($file_name) ) require $file_name;
	}

	protected function _setConfiguration( $config ) {
		$this->_config = $config;
	}

	/**
	 * @param string $installation_name
	 * @param string $username
	 * @param string $password
	 * @param array $params
	 */
	public static function init( $installation_name , $username , $password , array $params=null ) {
		// build initial configuration
		$config = array(
			'installation'  =>  $installation_name,
			'username'      =>  $username,
			'password'      =>  $password
		);

		// merge in other parameters... acceptable parameters: client_id, client_secret, host, api_path, secure, timeout
		static $default_config = array(
			'client_id'     =>  '',
			'client_secret' =>  '',
			'api_path'      =>  '/api/rest/',
			'auth_type'     =>  self::AUTH_TYPE_BASIC,
			'auth_path'     =>  'oauth/application/token',
			'secure'        =>  true,
			'errors'        =>  self::ERRORS_EXCEPTION,
			'autoload'      =>  null,
			'sign'          =>  null
		);

		// insert parameters and default values
		if ( $params ) $config = array_merge( $config , $default_config , $params );
		else $config = array_merge( $config , $default_config );

		// has client ID? use OAuth authentication by default
		if ($config['client_id']) {
			$config['auth_type'] = self::AUTH_TYPE_OAUTH;
		}

		// assemble host
		if ( !isset( $config[ 'host' ] ) ) {
			$config[ 'host' ] = $installation_name . '.arcticres.com';
		}

		// get instance
		$instance = self::getInstance();

		// already initialized
		if ($instance->_config) {
			$instance->raiseError('Already Configured', 'The Arctic API class has not been initialized. It must be deinitialized first.');
			return;
		}

		// store configuration
		$instance->_setConfiguration($config);

		// determine if autoloader is needed
		$need_autoload = $instance->_getConfig('autoload');
		if ( $need_autoload === null ) {
			// try to autoload base model to determine if an autoloader is needed
			$need_autoload = !class_exists( __NAMESPACE__ . '\Model' , true );
		}

		// if need autoloader, register it
		if ( $need_autoload ) {
			spl_autoload_register(__CLASS__ . '::autoloadClass');
		}
	}

	/**
	 * Deinitialize API. Useful if you connect to multiple installations.
	 */
	public static function deinit() {
		self::$_instance = null;
	}

	/**
	 * @return Cache\Manager
	 */
	public function getCacheManager() {
		// initiate cache manager
		if (!isset($this->_cache_manager)) {
			$this->_cache_manager = new Cache\Manager(
				$this->_getConfig('cache'),
				$this->_getConfig('cache_config', array('prefix'=>$this->_getConfig('installation')))
			);
		}

		return $this->_cache_manager;
	}

	public function raiseError($error_name, $error_description, $error_type=null) {
		// use HTTP status code if one is not specified
		if (null === $error_type && isset($this->_last_status) && 200 !== $this->_last_status) {
			$error_type = $this->_last_status;
		}

		// store last error
		self::$_last_error = sprintf('%s: %s (%d)',$error_name, $error_description, $error_type);

		switch ( isset( $this->_config ) && isset( $this->_config[ 'errors' ] ) ? $this->_config[ 'errors' ] : self::ERRORS_EXCEPTION ) {
			case self::ERRORS_EXCEPTION:
				throw Exception::create($error_type, sprintf('%s: %s',$error_name,$error_description));
			case self::ERRORS_ERROR:
				trigger_error(sprintf('%s: %s',$error_name,$error_description),E_USER_ERROR);
				break;
			case self::ERRORS_WARNING:
				trigger_error(sprintf('%s: %s',$error_name,$error_description),E_USER_WARNING);
				break;
			case self::ERRORS_SILENT:
				return;
			default:
				throw new Exception('Invalid configuration value for "errors".');
		}
	}

	/**
	 * Get the last error raised by the API call.
	 * @return string|null
	 */
	public static function getLastError() {
		return self::$_last_error;
	}

	private function _log($request,$body,$response) {
		if ( $this->_getConfig('debug') ) {
			printf( "== %s ==\n", $request);
			if ( $body ) printf("Request Body:\n%s\n",$body);
			printf("Response:\n%s\n\n",$response);
		}
	}

	private function _getConfig($name,$default=null) {
		if ( !isset( $this->_config ) ) {
			$this->raiseError('Not Configured','The Arctic API class has not been initiated.');
			return null;
		}

		if ( isset( $this->_config[ $name ] ) ) {
			return $this->_config[ $name ];
		}

		return $default;
	}

	private function _sendRequest( $url , $method=self::METHOD_GET , $body=null , array $headers=null ) {
		$default_headers = array(
			'Accept' => 'application/json'
		);

		// figure out protocol
		$protocol = 'http' . ($this->_getConfig('secure', true) ? 's' : '');
		$url = $protocol . '://' . $url;

		// capitalize method name
		$method = strtoupper($method);

		// build header name
		if ( $headers ) {
			$headers = array_merge($default_headers, $headers);
		}
		else {
			$headers = $default_headers;
		}

		// add content-type
		if ($body && !isset($headers[ 'Content-type'])) {
			if ('{' === $body[0] || '[' === $body[0]) {
				$headers['Content-type'] = 'application/json';
			}
			else {
				$headers['Content-type'] = 'application/x-www-form-urlencoded';
			}
		}

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, $this->_getConfig('timeout',10));
		curl_setopt($ch, CURLOPT_USERAGENT, 'ArcticAPI/' . self::VERSION);

		// set headers
		if ($headers) {
			$request_headers = array();
			foreach ($headers as $key => $val) {
				$request_headers[] = sprintf('%s: %s', $key, $val);
			}
			curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
		}

		// add body
		if ($body) {
			// security safeguard
			if ('@' === $body[0]) {
				$this->raiseError('Bad Request', 'Invalid request body.');
				return false;
			}

			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
		}

		// exec
		$data = curl_exec($ch);

		// curl error
		if (false === $data) {
			// get error
			$error = curl_error($ch);

			// close
			curl_close($ch);

			// raise error
			$this->raiseError('Request Failed', 'Unable to connect: ' . $error . '.', Exception::TYPE_CONNECTION);
			return false;
		}

		// get status
		$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);

		// close
		curl_close($ch);

		// explode
		$raw_headers = substr($data, 0, $header_size);
		$raw_response = substr($data, $header_size);

		// log it
		$this->_log($method . ' ' . $url, $body, $raw_headers . "\n\n" . $raw_response);

		// store status
		$this->_last_status = $status;

		// check status
		if (200 > $status || 300 <= $status) {
			// special status codes
			if (403 === $status) {
				// flag for retry if the token is from the cache
				if ($this->_cached_token) {
					// clear cached token
					$this->_clearToken();

					// flag should retry
					$this->_should_retry = true;

					// could keep running, but just cut to the chase....
					return false;
				}

				// clear token
				$this->_clearToken();
			}

			// non  JSON response? just raise the error here
			if ('{' !== $raw_response[0] && '[' !== $raw_response[0]) {
				$this->raiseError('Unexpected Response: '.  $status, $raw_response, Exception::TYPE_PARSE);
				return false;
			}
		}

		// parse it
		$parsed = @json_decode($raw_response, true);
		if (null === $parsed) {
			$this->raiseError('Unable to Parse Response', sprintf("JSON: %d\n%s", json_last_error(), $raw_response), Exception::TYPE_PARSE);
			return false;
		}

		return $parsed;
	}

	private function _clearToken() {
		// clear internal token
		$this->_token = null;
		$this->_cached_token = false;

		// get cache key
		$cache = 'token::' . $this->_getConfig('username');

		// clear from cache
		$this->getCacheManager()->remove($cache, null);
	}

	private function _getToken() {
		// return token
		if (isset($this->_token)) return $this->_token;

		// allow hard coded tokens
		if ($token = $this->_getConfig('token')) {
			$this->_cached_token = false;
			return $this->_token = $token;
		}

		// use cache
		$cache = 'token::' . $this->_getConfig('username');
		if ($token = $this->getCacheManager()->get($cache)) {
			$this->_cached_token = true;
			return $this->_token = $token;
		}

		// fetch token
		$request = array(
			'client_id'     =>  $this->_getConfig('client_id'),
			'client_secret' =>  $this->_getConfig('client_secret'),
			'grant_type'    =>  'password',
			'username'      =>  $this->_getConfig('username'),
			'password'      =>  $this->_getConfig('password'),
			'scope'         =>  $this->_getConfig('scope')
		);

		// build URL
		$url = $this->_getConfig('host') . $this->_getConfig('api_path') . $this->_getConfig('auth_path');
		$body = http_build_query(array_filter($request));

		// send response
		$response = $this->_sendRequest($url, self::METHOD_POST, $body);
		if (false === $response) return false;

		// success!
		if (is_array($response) && isset($response[ 'access_token'])) {
			$this->_cached_token = false;
			$this->_token = $response['access_token' ];

			// cache token
			$this->getCacheManager()->set($cache, $this->_token, null, isset($response['expires_in']) ? (int)$response['expires_in'] : null);

			return $this->_token;
		}

		// error
		if ( is_array( $response ) && isset( $response[ 'error' ] ) ) {
			$this->raiseError('Authentication Failed', 'Response: ' . $response['error'], Exception::TYPE_UNAUTHORIZED);
			return false;
		}

		// unknown
		$this->raiseError('Unknown Authentication Response', 'Response type: ' . gettype($response), Exception::TYPE_PARSE);
		return false;
	}

	public function sendRequest( $api_path , $method=self::METHOD_GET , $body=null , array $headers=null ) {
		// build url
		$url = $this->_getConfig('host') . $this->_getConfig('api_path') . $api_path;

		return $this->_sendRequest( $url , $method , $body , $headers );
	}

	public function sendAuthenticatedRequest( $api_path , $method=self::METHOD_GET , $body=null , array $headers=null ) {
		// build url
		$url = $this->_getConfig('host') . $this->_getConfig('api_path') . $api_path;
		$headers = (array)$headers;

		// add authentication information
		switch ($this->_getConfig('auth_type')) {
			case self::AUTH_TYPE_BASIC:
				// add to headers
				$headers = array_merge($headers, array(
					'Authorization'    =>  sprintf('Basic %s', base64_encode(sprintf('%s:%s', $this->_getConfig('username'), $this->_getConfig('password'))))
				));

				break;

			case self::AUTH_TYPE_OAUTH:
				// get token
				$token = $this->_getToken();
				if (false === $token) return false;

				// add to headers
				$headers = array_merge($headers, array(
					'Authorization'    =>  'Bearer ' . $token
				));

				break;

			default:
				return false;
		}

		// response
		$this->_should_retry = false;
		$response = $this->_sendRequest($url, $method, $body, $headers);

		// allow retry once
		if ($this->_should_retry) {
			// update authentication information
			switch ($this->_getConfig('auth_type')) {
				case self::AUTH_TYPE_BASIC:
					// should be unreachable (basic authentication should never result in _should_retry being true)
					return false;

				case self::AUTH_TYPE_OAUTH:
					// get token
					$token = $this->_getToken();
					if (false === $token) return false;

					// updated headers, just in case
					$headers['Authorization'] = 'Bearer ' . $token;

					break;

				default:
					return false;
			}

			// run response again
			$response = $this->_sendRequest($url, $method, $body, $headers);
		}

		return $response;
	}

	public function authenticateFromAuthorizationCode($code, $redirect_uri=null, $cache=true) {
		if (self::AUTH_TYPE_OAUTH !== $this->_getConfig('auth_type')) {
			$this->raiseError('Must be configured for OAuth authentication.');
		}

		// parameters for request
		$params = array(
			'grant_type' => 'authorization_code',
			'code' => $code,
			'client_id' => $this->_getConfig('client_id'),
			'client_secret' => $this->_getConfig('client_secret')
		);

		// add redirect uri
		if ($redirect_uri) {
			$params['redirect_uri'] = $redirect_uri;
		}

		// run request
		$response = $this->sendRequest('oauth/user/token', self::METHOD_POST, http_build_query($params));

		if (is_array($response)) {
			// has access token?
			if (isset($response['access_token'])) {
				// store token
				$this->_cached_token = false;
				$this->_token = $response['access_token'];

				// cache token
				if ($cache) {
					$this->getCacheManager()->set($cache, $this->_token, null, isset($response['expires_in']) ? (int)$response['expires_in'] : null);
				}

				return $response;
			}

			// authentication failure
			if (isset($response['error'])) {
				$this->raiseError('Authentication Failed', 'Response: ' . $response['error']);
				return false;
			}
		}

		// unknown
		$this->raiseError('Unknown Authentication Response', 'Response type: ' . gettype($response));
		return false;
	}

	public function buildAuthenticationLink($redirect_uri=null, $scope=null, $state=null) {
		if (self::AUTH_TYPE_OAUTH !== $this->_getConfig('auth_type')) {
			$this->raiseError('Must be configured for OAuth authentication.');
		}

		// build url
		$url = $this->_getConfig('host') . $this->_getConfig('oauth_link', '/auth/login/oauth');

		// build params
		$params = array(
			'client_id' => $this->_getConfig('client_id'),
			'response_type' => 'code',
			'redirect_uri' => $redirect_uri
		);

		// add optional parameters
		if ($scope) {
			$params['scope'] = $scope;
		}

		if ($state) {
			$params['state'] = $state;
		}

		return $url . '?' . http_build_query($params);
	}
}
