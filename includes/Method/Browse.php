<?php

namespace Arctic\Method;

use Arctic\Api;
use Arctic\Model;
use Arctic\ModelSet;

class Browse extends Method
{
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
		return new ModelSet( $this->_model_class , $response );
	}
}
