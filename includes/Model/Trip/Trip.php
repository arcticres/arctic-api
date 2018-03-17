<?php

namespace Arctic\Model\Trip;

use Arctic\Model;

/**
 * Class Trip
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
 * @property int $remainingopenings Remaining openings available to book.
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
 * @property string $onlinebookingurl Link to book online (if enabled and available).
 * @property int $guests Number of guests.
 * @property \Arctic\Model\BusinessGroup $businessgroup
 * @property PricingLevel[] $pricinglevels
 * @property Component[] $components
 * @property \Arctic\Model\Guide\GuideSchedule[] $guides
 */
class Trip extends Model
{
	public static function getApiPath() {
		return 'trip';
	}

	public function __construct() {
		parent::__construct();

        $this->_addSingleReference( 'businessgroup' , 'Arctic\Model\BusinessGroup' , array( 'businessgroupid' => 'id' ) );
        $this->_addMultipleReference('pricinglevels', __NAMESPACE__ . '\PricingLevel' , 'pricinglevel' );
        $this->_addMultipleReference('components', __NAMESPACE__ . '\Component' , 'component' );
        $this->_addMultipleReference('guides', 'Arctic\Model\Guide\GuideSchedule', 'guide');
	}
}
