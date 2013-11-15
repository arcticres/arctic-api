<?php

/**
 * Class Arctic_Trip
 * @property int $businessgroupid
 * @property int $triptypeid
 * @property int $parenttripid
 * @property int $id
 * @property string $start
 * @property string $starttime
 * @property string $subtripstartoffset
 * @property string $name
 * @property string $shortname
 * @property bool $canceled
 * @property string $color
 * @property int $openings
 * @property int $remainingopenings
 * @property int $inventoryitemid
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
class Arctic_Trip extends ArcticModel
{
	public static function getApiPath() {
		return 'trip';
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'businessgroup' , 'Arctic_BusinessGroup' , array( 'businessgroupid' => 'id' ) );
		$this->_addMultipleReference('pricinglevels','Arctic_Trip_PricingLevel' , 'pricinglevel' );
		$this->_addMultipleReference('components','Arctic_Trip_Component' , 'component' );
	}
}
