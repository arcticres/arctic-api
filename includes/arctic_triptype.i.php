<?php

/**
 * Class Arctic_TripType
 * @property int $businessgroupid
 * @property int $parenttripid
 * @property int $id
 * @property string $starttime
 * @property string $subtripstartoffset
 * @property string $name
 * @property string $shortname
 * @property string $color
 * @property int $openings
 * @property string $duration
 * @property int $registrationformid
 * @property bool $orenable
 * @property string $orname
 * @property string $ordescription
 * @property string $ordetails
 * @property int $orimageid
 * @property int $orminimumguests
 * @property string $orcutoff
 * @property int $registrationcutoffdays
 * @property bool $attachsameasparent
 * @property string $notes
 * @property int $accountid
 * @property int $paymentplanid
 * @property int $cancellationpolicyid
 * @property string $createdon
 * @property string $modifiedon
 * @property bool $deleted
 * @property Arctic_BusinessGroup $businessgroup
 * @property Arctic_Trip_PricingLevel[] $pricinglevels
 * @property Arctic_Trip_Component[] $components
 */
class Arctic_TripType extends ArcticModel
{
	public static function getApiPath() {
		return 'triptype';
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'businessgroup' , 'Arctic_BusinessGroup' , array( 'businessgroupid' => 'id' ) );
		$this->_addMultipleReference('pricinglevels','Arctic_Trip_PricingLevel' , 'pricinglevel' );
		$this->_addMultipleReference('components','Arctic_Trip_Component' , 'component' );
	}
}