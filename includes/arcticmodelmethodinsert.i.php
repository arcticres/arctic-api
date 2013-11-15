<?php

class ArcticModelMethodInsert extends ArcticModelMethod
{
	public function __construct() {
		parent::__construct( self::TYPE_MODEL , ArcticAPI::METHOD_POST );
	}

	/**
	 * @param array $response
	 * @return ArcticModel
	 */
	protected function _parseResponse( $response ) {
		// get references (before filling data)
		$references = $this->_model->getReferences();

		// fill data
		$this->_model->fillExistingData( isset( $response[ 'id' ] ) ? $response[ 'id' ] : null , $response );

		// write references too
		foreach ( $references as $name => $obj ) {
			if ( $obj instanceof ArcticReferenceSetWrapper ) {
				$obj->insertAll();
			}
			else {
				$obj->insert();
			}
		}

		return $this->_model;
	}

	protected function _prepareRequest( $api_path , $arguments ) {
		if ( $this->_model->doesExist() ) {
			ArcticAPI::getInstance()->raiseError('Model Already Saved','Cannot be inserted again.');
		}

		// build uri
		return $this->_runRequest( $api_path , $this->_method , json_encode( $this->_model->toArray() ) );
	}
}
