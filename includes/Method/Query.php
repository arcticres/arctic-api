<?php

namespace Arctic\Method;

use Arctic\Api;
use Arctic\Model;
use Arctic\ModelSet;

class Query extends Method
{
    private $_cache_key;
	private $_cache_each; // if special arguments are included, associated item cache with those arguments

	public function __construct() {
		parent::__construct( self::TYPE_GENERAL , Api::METHOD_GET , null , array(
			0   =>  'query'
		) );
	}

	/**
	 * @param array $response
	 * @return Model[]
	 */
	protected function _parseResponse( $response ) {
        // add to cache
        $ids = array();
        foreach ( $response['entries'] as $arr ) {
            if (!isset($arr['id'])) continue;
            $ids[] = $arr['id'];
            Api::getInstance()->getCacheManager()->set($arr['id'] . $this->_cache_each, $arr, $this->_model_class);
        }

        // cache ids
        if (count($ids) === count($response['entries'])) {
            // store api path for cache
            $cache_val = $response;
            $cache_val['entries'] = $ids;
            Api::getInstance()->getCacheManager()->set($this->_cache_key, $cache_val, $this->_model_class);
        }

		return new ModelSet( $this->_model_class , $response );
	}

    protected function _prepareRequest($api_path, $arguments) {
        $query = reset($arguments);

        // check for ID
        if (empty($query)) {
            Api::getInstance()->raiseError('No Query Specified','Query expects a valid object query.');
        }

        // store api path for cache
        $this->_cache_key = $api_path . '::' . $query;

	    // include special arguments in cache key and per item cache (since can affect items)
	    $last = count($arguments) - 1;
	    if (0 < $last && is_array($arguments[$last])) {
	    	$this->_cache_each = '?' . http_build_query($arguments[$last]);
		    $this->_cache_key .= $this->_cache_each;
	    }
	    else {
	    	$this->_cache_each = '';
	    }

        // run query
        $cache_manager = Api::getInstance()->getCacheManager();
        $cache_browse = $cache_manager->get($this->_cache_key, $this->_model_class);
        if (isset($cache_browse) && is_array($cache_browse)) {
            $use_cache = true;
            foreach ($cache_browse['entries'] as $key => $id) {
                // load
                $cache_manager->forceCacheForNext();
                if ($cache_obj = $cache_manager->get($id . $this->_cache_each, $this->_model_class)) {
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
