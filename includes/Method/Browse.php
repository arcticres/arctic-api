<?php

namespace Arctic\Method;

use Arctic\Api;
use Arctic\Model;
use Arctic\ModelSet;

class Browse extends Method
{
    private $_cache_key;

	public function __construct() {
		parent::__construct( self::TYPE_GENERAL , Api::METHOD_GET , null , array(
			0   =>  'start',
			1   =>  'number'
		) );
	}

	/**
	 * @param array $response
	 * @return Model[]
	 */
	protected function _parseResponse( $response ) {
        // add to cache
        $ids = [];
        foreach ( $response['entries'] as $arr ) {
            if (!isset($arr['id'])) continue;
            $ids[] = $arr['id'];
            Api::getInstance()->getCacheManager()->set($arr['id'], $arr, $this->_model_class);
        }

        // cache ids
        // store api path for cache
        $cache_val = $response;
        $cache_val['entries'] = $ids;
        Api::getInstance()->getCacheManager()->set($this->_cache_key, $cache_val, $this->_model_class);

        return new ModelSet( $this->_model_class , $response );
	}

    protected function _prepareRequest($api_path, $arguments) {
        // store api path for cache
        $this->_cache_key = $api_path;
        if ($arguments) $this->_cache_key .= '::' . implode('-', $arguments);

        // run query
        $cache_manager = Api::getInstance()->getCacheManager();
        $cache_browse = $cache_manager->get($this->_cache_key, $this->_model_class);
        if (isset($cache_browse)) {
            $use_cache = true;
            foreach ($cache_browse['entries'] as $key => $id) {
                // load
                $cache_manager->forceCacheForNext();
                if ($cache_obj = $cache_manager->get($id, $this->_model_class)) {
                    $cache_browse['entries'][$key] = $cache_obj;
                }
                else {
                    $use_cache = false;
                    break;
                }
            }

            // found all entries in the cache? use it...
            if ($use_cache) {
                return new ModelSet($this->_model_class, $cache_browse);
            }
        }

        return parent::_prepareRequest($api_path, $arguments);
    }
}
