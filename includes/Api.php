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
	private $_token;

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
        if ( substr( $class , 0 , 7 ) !== 'Arctic\\' ) return;
        $class = substr( $class , 7 );

        // convert to file name
        $file_name  = '';
        $namespace = '';
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
			'auth_path'     =>  'oauth/application/token',
			'secure'        =>  true,
			'errors'        =>  self::ERRORS_EXCEPTION,
            'autoload'      =>  null,
			'sign'          =>  null
		);

		// insert parameters and default values
		if ( $params ) $config = array_merge( $config , $default_config , $params );
		else $config = array_merge( $config , $default_config );

		// assemble host
		if ( !isset( $config[ 'host' ] ) ) {
            $config[ 'host' ] = $installation_name . '.arcticres.com';
        }

        // get instance
        $instance = self::getInstance();

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
     * @return Cache\Manager
     */
    public function getCacheManager() {
        // initiate cache manager
        if (!isset($this->_cache_manager)) {
            $this->_cache_manager = new Cache\Manager(
                $this->_getConfig('cache'),
                $this->_getConfig('cache_config', ['prefix'=>$this->_getConfig('installation')])
            );
        }

        return $this->_cache_manager;
    }

//	private function _signRequest( $url , $method , $body=null ) {
//		//if ( $body ) return hash_hmac('sha256',$body,)
//	}

	public function raiseError($error_name,$error_description) {
        // store last error
        self::$_last_error = sprintf('%s: %s',$error_name,$error_description);

		switch ( isset( $this->_config ) && isset( $this->_config[ 'errors' ] ) ? $this->_config[ 'errors' ] : self::ERRORS_EXCEPTION ) {
			case self::ERRORS_EXCEPTION:
				throw new Exception(sprintf('%s: %s',$error_name,$error_description));
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
			'User-Agent'    =>  'ArcticAPI/' . self::VERSION,
			'Accepts'       =>  'application/json'
		);

		// figure out protocol
		$protocol = 'http' . ( $this->_getConfig('secure',true) ? 's' : '' );
		$url = $protocol . '://' . $url;

		// capitalize method name
		$method = strtoupper($method);

		// build header name
		if ( $headers ) {
			$headers = array_merge( $default_headers , $headers );
		}
		else {
			$headers = $default_headers;
		}

		// build options
		$opts = array(
			'http' => array(
				'method' => $method ,
				'max_redirects' => 0 ,
				'timeout' => $this->_getConfig('timeout',10),
				'ignore_errors' => true
			)
		);

		// add content-type and request body
		if ( $body ) {
			$opts[ 'http' ][ 'content' ] = $body;
			if ( !isset( $headers[ 'Content-type' ] ) ) {
				if ( $body[0] === '{' || $body[0] === '[' ) {
					$headers['Content-type'] = 'application/json';
				}
				else {
					$headers['Content-type'] = 'application/x-www-form-urlencoded';
				}
			}
		}

		// add headers
		if ( $headers ) {
			$string = '';
			foreach ( $headers as $key => $val ) {
				$string .= $key . ': ' . $val . "\r\n";
			}
			$opts[ 'http' ][ 'header' ] = $string;
		}

		// send request
		$context = stream_context_create( $opts );
		$raw_response = file_get_contents( $url , false , $context );

		// log it
		$this->_log($method . ' ' . $url,$body,$raw_response);

		// fail
		if ( $raw_response === false ) {
			$this->raiseError('Request Failed','Unable to connect, connection reset or timed-out.');
			return false;
		}

		// parse it
		$parsed = @json_decode($raw_response,true);
		if ( $parsed === false ) {
			$this->raiseError('Unable to Parse Response',json_last_error());
			return false;
		}

		return $parsed;
	}

	private function _getToken() {
		// return token
		if ( isset( $this->_token ) ) return $this->_token;

		// allow hard coded tokens
		if ( $token = $this->_getConfig( 'token' ) ) {
			return $this->_token = $token;
		}

        // use cache
        $cache = 'token::' . $this->_getConfig('username');
        if ($token = $this->getCacheManager()->get($cache)) {
            return $token;
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
		$body = http_build_query( array_filter( $request ) );

		// send response
		$response = $this->_sendRequest( $url , self::METHOD_POST , $body );
		if ( $response === false ) return false;

		// success!
		if ( is_array( $response ) && isset( $response[ 'access_token' ] ) ) {
            $this->_token = $response[ 'access_token' ];

            // cache token
            $this->getCacheManager()->set($cache, $this->_token);

			return $this->_token;
		}

		// error
		if ( is_array( $response ) && isset( $response[ 'error' ] ) ) {
			$this->raiseError('Authentication Failed','Response: ' . $response['error'] );
			return false;
		}

		// unknown
		$this->raiseError('Unknown Authentication Response','Response type: ' . gettype($response));
		return false;
	}

	public function sendAuthenticatedRequest( $api_path , $method=self::METHOD_GET , $body=null , array $headers=null ) {
		// get token
		$token = $this->_getToken();
		if ( $token === false ) return false;

		// build url
		$url = $this->_getConfig('host') . $this->_getConfig('api_path') . $api_path;
		$headers = array_merge( (array)$headers , array(
			'Authorization'    =>  'Bearer ' . $token
		));

		return $this->_sendRequest( $url , $method , $body , $headers );
	}
}
