<?php

namespace Arctic\Model\Person;

use Arctic\Api;
use Arctic\Method\Method;
use Arctic\Model;

/**
 * Class Person
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
 * @property EmailAddress[] $emailaddresses
 * @property Address[] $addresses
 * @property PhoneNumber[] $phonenumbers
 * @property Note[] $notes
 * @method email(int $templateid=null,bool $outbox=false)
 * @method link(int $siteid=null)
 */
class Person extends Model
{
	public static function getApiPath() {
		return 'person';
	}

	public function __construct() {
		parent::__construct();

		$this->_addMultipleReference('emailaddresses', __NAMESPACE__ . '\EmailAddress', 'emailaddress', array( 'id' => 'personid' ) );
		$this->_addMultipleReference('addresses', __NAMESPACE__ . '\Address', 'address' , array( 'id' => 'personid' ) );
		$this->_addMultipleReference('phonenumbers', __NAMESPACE__ . '\PhoneNumber', 'phonenumber' , array( 'id' => 'personid' ) );
		$this->_addMultipleReference('notes', __NAMESPACE__ . '\Note', 'note', array( 'id' => 'personid' ) );
	}

    protected static function _mapMethod( $method ) {
        // person specific method: email($templateid=null, $outbox=false)
        if ( $method === 'email' ) {
            return new Method(Method::TYPE_EXISTING_MODEL, Api::METHOD_POST, 'email', array('templateid' , 'outbox'));
        }

        // person specific method: link($siteid=null)
        if ( $method === 'link' ) {
            return new Method(Method::TYPE_EXISTING_MODEL, Api::METHOD_GET, 'link', array('siteid'));
        }

        return parent::_mapMethod($method);
    }
}
