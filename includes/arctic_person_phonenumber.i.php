<?php

/**
 * Class Arctic_Person_PhoneNumber
 * @property int $personid
 * @property int $id
 * @property string $type
 * @property bool $isprimary
 * @property int $countryid
 * @property string $phonenumber
 * @property string $createdon
 * @property string $modifiedon
 * @property Arctic_Country $country
 */
class Arctic_Person_PhoneNumber extends ArcticModel
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
