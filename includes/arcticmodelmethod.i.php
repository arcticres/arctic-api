<?php

class ArcticModelMethod
{
	const TYPE_GENERAL = 0;
	const TYPE_MODEL = 1;
	const TYPE_EXISTING_MODEL = 2;

	protected $_type;
	protected $_method;
	protected $_uri;
	protected $_argument_mapping;

	/**
	 * @var string
	 */
	protected $_model_class;

	/**
	 * @var ArcticModel
	 */
	protected $_model;

	public function __construct( $type , $method=ArcticAPI::METHOD_GET , $uri=null , array $argument_mapping=null ) {
		$this->_type = $type;
		$this->_method = $method;
		$this->_uri = $uri;
		$this->_argument_mapping = $argument_mapping;
	}

	public function setModel( $class_or_instance ) {
		if ( is_string( $class_or_instance ) ) {
			$this->_model_class = $class_or_instance;
		}
		else {
			$this->_model = $class_or_instance;
			$this->_model_class = $class_or_instance;
		}
	}

	protected function _runRequest( $api_uri , $method , $body=null , $headers=null ) {
		$response = ArcticAPI::getInstance()->sendAuthenticatedRequest( $api_uri , $method , $body , $headers );
		if ( $response === false ) return false;

		// invalid type
		if ( !is_array( $response ) ) {
			ArcticAPI::getInstance()->raiseError('Invalid Response','Expected an array. Received: ' . gettype( $response ) . '.');
			return false;
		}

		// error
		if ( isset( $response[ 'error' ] ) ) {
			ArcticAPI::getInstance()->raiseError($response['error'], isset( $response['details'] ) ? $response['details'] : null);
			return false;
		}

		return $this->_parseResponse( $response );
	}

	protected function _parseResponse( $response ) {
		return true;
	}

	protected function _prepareRequest( $api_path , $arguments ) {
		$add = '';
		if ( $this->_argument_mapping ) {
			foreach ( $this->_argument_mapping as $index => $name ) {
				if ( !isset( $arguments[ $index ] ) ) continue;
				$add .= ( $add ? '&' : '' ) . urlencode( $name ) . '=' . urlencode( (string)$arguments[ $index ] );
			}
		}

		// build url
		$url = $api_path . ( $this->_uri ? '/' . $this->_uri : '' );
		if ( $add ) $url .= '?' . $add;

		return $this->_runRequest( $url , $this->_method );
	}

	public function runRequest( $api_path , $arguments ) {
		switch ( $this->_type ) {
			case self::TYPE_GENERAL:
				if ( $this->_model ) {
					ArcticAPI::getInstance()->raiseError('Invalid Method ' . __CLASS__,'General method called on model instance.');
					return false;
				}
				break;
			case self::TYPE_EXISTING_MODEL:
				if ( $this->_model && !$this->_model->doesExist() ) {
					ArcticAPI::getInstance()->raiseError('Invalid Method ' . __CLASS__,'Model method called on unsaved instance.');
					return false;
				}
			case self::TYPE_MODEL:
				if ( !$this->_model ) {
					ArcticAPI::getInstance()->raiseError('Invalid Method ' . __CLASS__,'Model method called on general instance.');
					return false;
				}
				break;
		}

		return $this->_prepareRequest( $api_path , $arguments );
	}
}