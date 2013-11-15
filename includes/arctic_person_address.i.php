<?php

/**
 * Class Arctic_Person_Address
 * @property int $personid
 * @property int $id
 * @property string $type
 * @property bool $isprimary
 * @property string $address1
 * @property string $address2
 * @property string $city
 * @property string $state
 * @property string $postalcode
 * @property int $countryid
 * @property bool $subscribetomaillist
 * @property string $createdon
 * @property string $modifiedon
 * @property Arctic_Country $country
 */
class Arctic_Person_Address extends ArcticModel
{
	public static function getApiPath() {
		// currently does not have a direct api call
		// just accessed as a subobject of persons
		return null;
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'country' , 'Arctic_Country' , array( 'countryid' => 'id' ) );
	}
}
