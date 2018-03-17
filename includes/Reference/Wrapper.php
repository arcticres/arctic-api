<?php

namespace Arctic\Reference;

use Arctic\Api;
use Arctic\Exception;
use Arctic\Model;

class Wrapper
{
	protected $_loaded = false;

	/**
	 * @var Model
	 */
	protected $_parent;
	protected $_model_class;

	/**
	 * @var Model
	 */
	protected $_model;

	/**
	 * @var Definition
	 */
	protected $_definition;

	public function __construct($parent, Definition $definition, array $data=null) {
		// potential memory leak issue
		$this->_parent = $parent;

		// use sub api path
		$this->_definition = $definition;
		$this->_model_class = $definition->getModelClass();

		// set data
		if ($data) {
			$this->_model = new $this->_model_class();
			$this->_model->fillExistingData(isset($data['id']) ? $data['id'] : null, $data);
			$this->_model->setParentReference($parent, $definition);
			$this->_loaded = true;
		}
	}

	protected function _load() {
		if ( $sub_api_path = $this->_definition->getSubApiPath() ) {

		}

		// use mapping
		if ( $mapping = $this->_definition->getMapping() ) {
			if ( count( $mapping ) > 1 ) {
				Api::getInstance()->raiseError('Unable To Load','No mapping or sub-API path defined.');
				return false;
			}

			// get class
			$class = $this->_model_class;

			// read mapping
			$foreign = reset( $mapping );
			$local = key( $mapping );

            // mark as loaded (even if loading fails, no need to reattempt)
            $this->_loaded = true;

			// id
			$id = $this->_parent->$local;

			// no value
			if ( empty( $id ) ) {
				return false;
			}

			// load it
			/** @var Model $obj */
			$obj = $class::load( $id );
			if ( $obj === false ) {
				// not found (or failed)
				return false;
			}

			// set parent
			$obj->setParentReference( $this->_parent , $this->_definition );

			// store it
			$this->_model = $obj;
			return true;
		}

		Api::getInstance()->raiseError('Unable To Load','No mapping or sub-API path defined.');
		return false;
	}

	public function __get( $name ) {
		if ( !$this->_loaded ) {
			if ( !$this->_load() ) return false;
		}

        // no model
        if ( !$this->_model ) return null;

		return $this->_model->__get( $name );

	}

	public function __set( $name , $value ) {
		if ( !$this->_loaded ) {
			if ( !$this->_load() ) return;
		}

        // no model
        if ( !$this->_model ) throw new Exception( 'Setting property on undefined object.' );

		$this->_model->__set( $name , $value );
	}

	public function __unset( $name ) {
		if ( !$this->_loaded ) {
			if ( !$this->_load() ) return;
		}

        // no model
        if ( !$this->_model ) throw new Exception( 'Setting property on undefined object.' );

		$this->_model->__unset( $name );
	}

	public function __isset( $name ) {
		if ( !$this->_loaded ) {
			if ( !$this->_load() ) return false;
		}

        // no model
        if ( !$this->_model ) return false;

		return $this->_model->__isset( $name );
	}

	public function __call( $name , $arguments ) {
		if ( !$this->_loaded ) {
			if ( !$this->_load() ) return false;
		}

        if ( !$this->_model ) throw new \BadMethodCallException( 'Unable to call "' . $name . '" on undefined object.' );

		return call_user_func_array(array($this->_model, $name), $arguments);
	}

    /**
     * Used by wrapper->isset() to see if a reference is defined.
     * @return bool
     */
    public function isDefined() {
        if ( !$this->_loaded ) {
            if ( !$this->_load() ) return false;
        }

        return isset( $this->_model );
    }
}
