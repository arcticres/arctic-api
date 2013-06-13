<?php

/**
 * @property int $businessgroupid
 * @property int $id
 * @property string $name
 * @property string $timeincrement
 * @property int $accountid
 * @property array $invoicesubitems
 * @property string $minimumduration
 * @property string $recoverytime
 * @property string $color
 * @property bool $orenable
 * @proeprty string $orname
 * @proeprty string $ordescription
 * @proeprty string $ordetails
 * @proeprty string $orimageid
 * @proeprty Time $orcutoff
 * @proeprty int $registrationformid
 * @proeprty int $registrationcutoffdays
 * @property int $inventoryitemid
 * @property string $createdon
 * @property string $modifiedon
 * @property bool $deleted
 * @property Arctic_BusinessGroup $businessgroup
 * @property Arctic_RentalItem_PricingLevel[] $pricinglevels
 */
class Arctic_RentalItem extends ArcticModel
{
	public static function getApiPath() {
		return 'rentalitem';
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'businessgroup' , 'Arctic_BusinessGroup' , array( 'businessgroupid' => 'id' ) );
		$this->_addMultipleReference('pricinglevels','Arctic_RentalItem_PricingLevel' , 'pricinglevel' );
	}
}