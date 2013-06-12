<?php

class ArcticModelMethodLoad extends ArcticModelMethod
{
	protected $_id;

	public function __construct() {
		parent::__construct( self::TYPE_GENERAL , ArcticAPI::METHOD_GET );
	}

	/**
	 * @param array $response
	 * @return ArcticModel
	 */
	protected function _parseResponse( $response ) {
		$class = $this->_model_class;

		/** @var ArcticModel $me */
		$me = new $class();
		$me->fillExistingData( $this->_id , $response );

		return $me;
	}

	protected function _prepareRequest( $api_path , $arguments ) {
		// first element
		$this->_id = reset( $arguments );

		// build uri
		$uri = $api_path . '/' . urlencode( $this->_id );

		return $this->_runRequest( $uri , $this->_method );
	}
}