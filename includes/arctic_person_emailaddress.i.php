<?php

/**
 * Class Arctic_Person_EmailAddress
 * @property int $personid
 * @property int $id
 * @property string $type
 * @property bool $isprimary
 * @property string $emailaddress
 * @property bool $subscribetoemaillist
 * @property string $createdon
 * @property string $modifiedon
 */
class Arctic_Person_EmailAddress extends ArcticModel
{
	public static function getApiPath() {
		// currently does not have a direct api call
		// just accessed as a subobject of persons
		return null;
	}

	public function __construct() {
		parent::__construct();
	}
}