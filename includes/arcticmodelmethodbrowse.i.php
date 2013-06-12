<?php

class ArcticModelMethodBrowse extends ArcticModelMethod
{
	public function __construct() {
		parent::__construct( self::TYPE_GENERAL , ArcticAPI::METHOD_GET , null , array(
			0   =>  'start',
			1   =>  'number'
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