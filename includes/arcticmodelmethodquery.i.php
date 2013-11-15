<?php

class ArcticModelMethodQuery extends ArcticModelMethod
{
	public function __construct() {
		parent::__construct( self::TYPE_GENERAL , ArcticAPI::METHOD_GET , null , array(
			0   =>  'query'
		) );
	}

	/**
	 * @param array $response
	 * @return ArcticModel[]
	 */
	protected function _parseResponse( $response ) {
		return new ArcticModelSet( $this->_model_class , $response );
	}
}
