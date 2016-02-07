<?php

namespace Arctic\Model\Rental;

use Arctic\Model;

/**
 * @class RentalInclude
 * @property int $activityid
 * @property int $id
 * @property int $rentalitemid
 * @property int $rentalitempricinglevelid
 * @property int $inventoryallocationid
 * @property array $setinventoryallocationids
 * @property int $quantity
 * @property string $createdon
 * @property string $modifiedon
 * @property bool $deleted
 * @property Rental $rental
 * @property Item $item
 */
class RentalInclude extends Model
{
	public static function getApiPath() {
		// currently does not have a direct api call
		// just accessed as a subobject of rentals
		return null;
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference('rental', __NAMESPACE__ . '\Rental', array('activityid' => 'activityid'));
		$this->_addSingleReference('item', __NAMESPACE__ . '\Item', array('rentalitemid' => 'id'));
	}
}
