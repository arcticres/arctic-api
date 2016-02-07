<?php

namespace Arctic\Model\Reservation;

use Arctic\Model;

/**
 * @class ReservationMember
 * @property int $activityid
 * @property int $id
 * @property int $personid
 * @property string $pricingleveluniquename
 * @property string $pricinglevelname
 * @property float $pricinglevelcost
 * @property array $components
 * @property string|null $registrationupdatedon
 * @property bool $registrationcomplete
 * @property string $registrationby
 * @property string $createdon
 * @property string $modifiedon
 * @property int $createdbyuserid
 * @property bool $deleted
 * @property Reservation $reservation
 * @property \Arctic\Model\Person\Person $person
 */
class ReservationMember extends Model
{
	public static function getApiPath() {
		// currently does not have a direct api call
		// just accessed as a subobject of reservations
		return null;
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference('reservation', __NAMESPACE__ . '\Reservation', array('activityid' => 'activityid'));
		$this->_addSingleReference('person', 'Arctic\Model\Person\Person', array('personid'=>'id'));
	}
}
