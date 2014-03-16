<?php

namespace Arctic\Method;

use Arctic\Api;
use Arctic\Model;
use Arctic\ModelSet;

class Query extends Method
{
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
        foreach ( $response['entries'] as $arr ) {
            if (!isset($arr['id'])) continue;
            Api::getInstance()->getCacheManager()->set($arr['id'], $arr, $this->_model_class);
        }

		return new ModelSet( $this->_model_class , $response );
	}
}
