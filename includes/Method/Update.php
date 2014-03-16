<?php

namespace Arctic\Method;

use Arctic\Api;
use Arctic\Model;

class Update extends Method
{
	public function __construct() {
		parent::__construct( self::TYPE_EXISTING_MODEL , Api::METHOD_PUT );
	}

	/**
	 * @param array $response
	 * @return Model
	 */
	protected function _parseResponse( $response ) {
		$this->_model->fillExistingData( $this->_model->getID() , $response );

        // update cache if an ID was returned
        if ($id = $this->_model->getID()) {
            Api::getInstance()->getCacheManager()->set($id, $response, $this->_model_class);
        }

		return $this->_model;
	}

	protected function _prepareRequest( $api_path , $arguments ) {
		// changed values
		$changed = array();
		foreach ( $this->_model->delta() as $key => $val ) {
			$changed[ $key ] = $val[ 1 ];
		}

		// nothing changed
		if ( empty( $changed ) ) {
			return $this->_model;
		}

		// run the request
		return $this->_runRequest( $api_path , $this->_method , json_encode( $changed ) );
	}
}
