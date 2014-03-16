<?php

namespace Arctic\Method;

use Arctic\Api;
use Arctic\Model;
use Arctic\Reference\SetWrapper;

class Insert extends Method
{
	public function __construct() {
		parent::__construct( self::TYPE_MODEL , Api::METHOD_POST );
	}

	/**
	 * @param array $response
	 * @return Model
	 */
	protected function _parseResponse( $response ) {
		// get references (before filling data)
		$references = $this->_model->getReferences();

		// fill data
		$this->_model->fillExistingData( isset( $response[ 'id' ] ) ? $response[ 'id' ] : null , $response );

        // update cache if an ID was returned
        if (isset($response['id'])) {
            Api::getInstance()->getCacheManager()->set($response['id'], $response, $this->_model_class);
        }

		// write references too
		foreach ( $references as $name => $obj ) {
			if ( $obj instanceof SetWrapper ) {
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
			Api::getInstance()->raiseError('Model Already Saved','Cannot be inserted again.');
		}

		// build uri
		return $this->_runRequest( $api_path , $this->_method , json_encode( $this->_model->toArray() ) );
	}
}
