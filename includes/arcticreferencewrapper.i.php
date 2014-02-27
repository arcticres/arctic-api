<?php

class ArcticReferenceWrapper
{
	protected $_loaded = false;

	/**
	 * @var ArcticModel
	 */
	protected $_parent;
	protected $_model_class;

	/**
	 * @var ArcticModel
	 */
	protected $_model;

	/**
	 * @var ArcticModelReferenceDefinition
	 */
	protected $_definition;

	public function __construct( $parent , $class_or_instance , ArcticModelReferenceDefinition $definition ) {
		// potential memory leak issue
		$this->_parent = $parent;

		// set object
		if ( is_object( $class_or_instance ) ) {
			$this->_model = $class_or_instance;
			$this->_model->setParentReference( $parent , $definition );
			$this->_model_class = get_class( $class_or_instance );
			$this->_loaded = true;
		}
		else {
			$this->_model_class = $class_or_instance;
		}

		// use sub api path
		$this->_definition = $definition;
	}

	protected function _load() {
		if ( $sub_api_path = $this->_definition->getSubApiPath() ) {

		}

		// use mapping
		if ( $mapping = $this->_definition->getMapping() ) {
			if ( count( $mapping ) > 1 ) {
				ArcticAPI::getInstance()->raiseError('Unable To Load','No mapping or sub-API path defined.');
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
			/** @var ArcticModel $obj */
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

		ArcticAPI::getInstance()->raiseError('Unable To Load','No mapping or sub-API path defined.');
		return false;
	}

	public function __get( $name ) {
		if ( !$this->_loaded ) {
			if ( !$this->_load() ) return false;
		}

		return $this->_model->__get( $name );

	}

	public function __set( $name , $value ) {
		if ( !$this->_loaded ) {
			if ( !$this->_load() ) return;
		}

		$this->_model->__set( $name , $value );
	}

	public function __unset( $name ) {
		if ( !$this->_loaded ) {
			if ( !$this->_load() ) return;
		}

		$this->_model->__unset( $name );
	}

	public function __isset( $name ) {
		if ( !$this->_loaded ) {
			if ( !$this->_load() ) return false;
		}

		return $this->_model->__isset( $name );
	}

	public function __call( $name , $arguments ) {
		if ( !$this->_loaded ) {
			if ( !$this->_load() ) return false;
		}

		return $this->_model->__call( $name , $arguments );
	}

    /**
     * Used by wrapper->isset() to see if a reference is defined.
     * @return bool
     */
    public function isDefined() {
        if ( !$this->_loaded ) {
            if ( !$this->_load() ) return false;
        }

        return true;
    }
}
