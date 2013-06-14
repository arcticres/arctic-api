<?php

/**
 * Class ArcticException
 * @method static static[] browse($start=0,$number=50)
 * @method static static[] query($query)
 * @method static static load($id)
 * @method insert()
 * @method update()
 * @method static delete(ArcticModel $model)
 */
class ArcticModel
{
	protected $_id;
	protected $_explicit;
	protected $_exists = false;
	protected $_data = array();
	protected $_new_data = array();

	/**
	 * @var ArcticModelReferenceDefinition[]
	 */
	protected $_reference_definitions = array();

	protected $_references = array();

	/**
	 * @var ArcticModel
	 */
	protected $_parent;

	/**
	 * @var ArcticModelReferenceDefinition
	 */
	protected $_parent_reference;

	protected static $_relative_api_path;

	public static function getApiPath() {
		$class = get_called_class();
		if ( $p = strpos( $class , '_' ) ) {
			return strtolower( substr( $class , $p ) );
		}
		return strtolower( $class );
	}

	public static function getRelativeApiPath() {
		if ( isset( self::$_relative_api_path ) ) {
			$ret = self::$_relative_api_path;
			self::$_relative_api_path = null;;
			return $ret;
		}

		return static::getApiPath();
	}

	/**
	 * Static calls do not have a parent reference context. This is used to temporarily force a context. It will only
	 * apply to the next static call.
	 * @param $api
	 */
	public static function forceRelativeApiPath( $api ) {
		self::$_relative_api_path = $api;
	}

	/**
	 * @param string $method
	 * @return ArcticModelMethod
	 */
	protected static function _mapMethod( $method ) {
		switch ( $method ) {
			case 'browse':
				return new ArcticModelMethodBrowse();
			case 'query':
				return new ArcticModelMethodQuery();
			case 'load':
				return new ArcticModelMethodLoad();
			case 'insert':
				return new ArcticModelMethodInsert();
			case 'update':
				return new ArcticModelMethodUpdate();
			case 'delete':
				return new ArcticModelMethodDelete();
			default:
				return false;
		}
	}

	public function __construct() {

	}

	public function getMyRelativeApiPath() {
		// build uri
		if ( isset( $this->_parent ) ) {
			if ( $sub_api_path = $this->_parent_reference->getSubApiPath() ) {
				$path = $this->_parent->getMyRelativeApiPath() . '/' . $sub_api_path;
			}
			else {
				$path = static::getApiPath();
			}
		}
		else {
			$path = static::getApiPath();
		}

		// add model id
		if ( $this->doesExist() ) {
			if ( $id = $this->getID() ) {
				$path .= '/' . $id;
			}
			else {
				ArcticAPI::getInstance()->raiseError('No ID Defined','Unable to determine the ID of an existing model.');
				return false;
			}
		}

		return $path;
	}

	/**
	 * Limited: currently, never uses sub-api. Sub-api does not currently support
	 * single references.
	 * @param $name
	 * @param $class
	 * @param array $mapping
	 */
	protected function _addSingleReference( $name , $class , array $mapping=null ) {
		$this->_reference_definitions[ $name ] = new ArcticModelReferenceDefinition(
			ArcticModelReferenceDefinition::TYPE_SINGLE ,
			$name ,
			$class ,
			null ,
			$mapping
		);
	}

	/**
	 * ALWAYS uses sub-api.
	 * @param $name
	 * @param $class
	 * @param string $sub_api_path
	 * @param array $mapping
	 */
	protected function _addMultipleReference( $name , $class , $sub_api_path , array $mapping=null ) {
		$this->_reference_definitions[ $name ] = new ArcticModelReferenceDefinition(
			ArcticModelReferenceDefinition::TYPE_MULTIPLE ,
			$name ,
			$class ,
			$sub_api_path ,
			$mapping
		);
	}

	/**
	 * @param ArcticModel $parent
	 * @param ArcticModelReferenceDefinition $reference
	 */
	public function setParentReference( ArcticModel $parent , ArcticModelReferenceDefinition $reference ) {
		$this->_parent = $parent;
		$this->_parent_reference = $reference;
	}

	/**
	 * @return mixed
	 */
	public function getParentReference() {
		return $this->_parent_reference;
	}

	/**
	 * @return boolean
	 */
	public function doesExist() {
		return $this->_exists;
	}

	/**
	 * @return mixed
	 */
	public function getID() {
		return $this->_id;
	}

	public function toArray() {
		return array_merge( $this->_data , $this->_new_data );
	}

	public function delta() {
		$ret = array();
		foreach ( $this->_new_data as $key => $new ) {
			$old = ( isset( $this->_data[ $key ] ) ? $this->_data[ $key ] : null );
			$ret[ $key ] = array( $old , $new );
		}
		return $ret;
	}

	/**
	 * @return ArcticModelReferenceDefinition[]
	 */
	public function getReferenceDefinitions() {
		return $this->_reference_definitions;
	}

	/**
	 * @return array
	 */
	public function getReferences() {
		return $this->_references;
	}

	public function __call( $method , $arguments ) {
		$method = static::_mapMethod( $method );
		if ( $method === false ) {
			throw new BadMethodCallException('Method does not exist: ' . $method . '.' );
		}

		// pass self
		$method->setModel( $this );

		// build path
		$path = $this->getMyRelativeApiPath();
		if ( $path === false ) return false;

		return $method->runRequest( $path , $arguments );
	}

	public static function __callStatic( $method , $arguments ) {
		$method = static::_mapMethod( $method );
		if ( $method === false ) {
			throw new BadMethodCallException('Method does not exist: ' . $method . '.' );
		}

		// pass model class
		$method->setModel( get_called_class() );

		return $method->runRequest( static::getRelativeApiPath() , $arguments );
	}

	public function __get( $name ) {
		// check references
		if ( isset( $this->_reference_definitions[ $name ] ) ) {
			// initiate blank
			if ( !isset( $this->_references[ $name ] ) ) {
				$this->_references[ $name ] = $this->_reference_definitions[ $name ]->initiateBlankReference( $this );
			}

			return $this->_references[ $name ];
		}

		// read new data
		if ( array_key_exists( $name , $this->_new_data ) ) {
			return $this->_new_data[ $name ];
		}

		// read existing data
		if ( array_key_exists( $name , $this->_data ) ) {
			return $this->_data[ $name ];
		}

		return null;
	}

	// set
	public function __set( $name , $value ) {
		// no change required?
		if ( isset( $this->_data[ $name ] ) && $this->_data[ $name ] == $value ) {
			unset( $this->_new_data[ $name ] );
			return;
		}

		// can not write to references
		if ( isset( $this->_reference_definitions[ $name ] ) ) {
			ArcticAPI::getInstance()->raiseError('Unable to Set Reference','Reference can not be directly set. Modify the reference object, or edit the reference id column.');
		}

		$this->_new_data[ $name ] = $value;
	}

	public function __isset( $name ) {
		// is reference set
		if ( isset( $this->_reference_definitions[ $name ] ) ) {
			return $this->_reference_definitions[ $name ]->isReferenceSet( $this->_references[ $name ] );
		}

		// is in new data
		if ( array_key_exists( $name , $this->_new_data ) ) {
			return ( $this->_new_data[ $name ] !== null );
		}

		// is in existing data
		if ( array_key_exists( $name , $this->_data ) ) {
			return ( $this->_data[ $name ] !== null );
		}

		return false;
	}

	public function __unset( $name ) {
		// clear if exists in _data
		if ( array_key_exists( $name , $this->_data ) ) {
			if ( $this->_data[ $name ] !== null ) {
				$this->_new_data[ $name ] = null;
			}
			return;
		}

		if ( isset( $this->_reference_definitions[ $name ] ) ) {
			ArcticAPI::getInstance()->raiseError('Unable to Unset Reference','Reference can not be unset. Delete the reference object, or edit the reference id column.');
		}
	}

	/**
	 * @private
	 * @param $id
	 * @param $data
	 */
	public function fillExistingData($id,$data) {
		$this->_id = $id;
		$this->_exists = true;
		$this->_data = $data;
		$this->_new_data = array();

		// move references out of data
		foreach ( $this->_reference_definitions as $name => $definition ) {
			// unset it, in case existing data was loaded that may have been invalidated
			unset( $this->_references[ $name ] );

			// no new data, skip it
			if ( !isset( $this->_data[ $name ] ) ) {
				$this->_references[ $name ] = $definition->initiateBlankReference( $this );
				continue;
			}

			// move it
			$ref_data = $this->_data[ $name ];
			unset( $this->_data[ $name ] );

			// initiate reference data
			$this->_references[ $name ] = $definition->initiateReferenceData( $this , $ref_data );
		}
	}
}