<?php

/**
 * Class Arctic_Person
 * @property int $id
 * @property string $namefirst
 * @property string $namelast
 * @property string $namecompany
 * @property bool $iscustomer
 * @property bool $isuser
 * @property bool $isguide
 * @property bool $isbookingagent
 * @property bool $isvendor
 * @property string $customersource
 * @property string $gender
 * @property string $birthday
 * @property int $dependentofpersonid
 * @property string $createdon
 * @property string $modifiedon
 * @property Arctic_Person_EmailAddress[] $emailaddresses
 * @property Arctic_Person_Address[] $addresses
 * @property Arctic_Person_PhoneNumber[] $phonenumbers
 * @property Arctic_Person_Note[] $notes
 */
class Arctic_Person extends ArcticModel
{
	public static function getApiPath() {
		return 'person';
	}

	public function __construct() {
		parent::__construct();

		$this->_addMultipleReference('emailaddresses','Arctic_Person_EmailAddress' , 'emailaddress' , array( 'id' => 'personid' ) );
		$this->_addMultipleReference('addresses','Arctic_Person_Address' , 'address' , array( 'id' => 'personid' ) );
		$this->_addMultipleReference('phonenumbers','Arctic_Person_PhoneNumber' , 'phonenumber' , array( 'id' => 'personid' ) );
		$this->_addMultipleReference('notes','Arctic_Person_Note' , 'note' , array( 'id' => 'personid' ) );
	}
}
