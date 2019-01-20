<?php

namespace Arctic\Method;

use Arctic\Api;
use Arctic\Model;

class Load extends Method
{
	protected $_id;
	protected $_cache_key;

	public function __construct() {
		parent::__construct( self::TYPE_GENERAL , Api::METHOD_GET );
	}

	/**
	 * @param array $response
     * @param bool $update_cache
	 * @return Model
	 */
	protected function _parseResponse($response, $update_cache=true) {
		$class = $this->_model_class;

        // update cache?
        if ($update_cache) {
            Api::getInstance()->getCacheManager()->set($this->_cache_key, $response, $class);
        }

		/** @var Model $me */
		$me = new $class();
		$me->fillExistingData( $this->_id , $response );

		return $me;
	}

	protected function _prepareRequest( $api_path , $arguments ) {
		// first element
		$this->_id = reset( $arguments );

        // check for ID
        if ( empty( $this->_id ) ) {
            Api::getInstance()->raiseError('No ID Specified','Load expects a valid object ID to fetch.');
        }

		// include special arguments in cache key
		$last = count($arguments) - 1;
		if (0 < $last && is_array($arguments[$last])) {
			$this->_cache_key = $this->_id . '?' . http_build_query($arguments[$last]);
		}
		else {
			$this->_cache_key = $this->_id;
		}

        // check cache
		$cached_response = Api::getInstance()->getCacheManager()->get($this->_cache_key, $this->_model_class);
        if (isset($cached_response) && is_array($cached_response)) {
            return $this->_parseResponse($cached_response, false);
        }

		// build uri
		$uri = $api_path . '/' . urlencode( $this->_id );

		return $this->_runRequest( $uri , $this->_method );
	}
}
