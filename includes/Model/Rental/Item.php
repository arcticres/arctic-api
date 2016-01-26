<?php

namespace Arctic\Model\Rental;

use Arctic\Api;
use Arctic\Method\Method;
use Arctic\Model;

class _MethodPrice extends Method
{
	public function __construct() {
		parent::__construct(self::TYPE_EXISTING_MODEL, Api::METHOD_GET, 'price', array('start', 'end'));
	}

	protected function _parseResponse($response) {
		return $response;
	}
}

class _MethodAvailability extends Method
{
	public function __construct() {
		parent::__construct(self::TYPE_EXISTING_MODEL, Api::METHOD_GET, 'availability', array('start', 'end'));
	}

	protected function _parseResponse($response) {
		return $response;
	}
}

/**
 * @class Item
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
 * @property \Arctic\Model\BusinessGroup $businessgroup
 * @property PricingLevel[] $pricinglevels
 * @property array|null $availability If you include `available_start` and `available_end` parameters with a request, these entries will be populated on load.
 * @method array price($start, $end) Get an array of prices over the specified time period.
 * @method array availability($start=null, $end=null) Get an array of changes to the available quantity, due to either adjustments in the inventory or allocations.
 */
class Item extends Model
{
	public static function getApiPath() {
		return 'rentalitem';
	}

	public function __construct() {
		parent::__construct();

		$this->_addSingleReference( 'businessgroup' , '\Arctic\Model\BusinessGroup' , array( 'businessgroupid' => 'id' ) );
		$this->_addMultipleReference('pricinglevels', __NAMESPACE__ . '\PricingLevel' , 'pricinglevel' );
	}

	protected static function _mapMethod($method) {
		// invoice specific method: price($start, $end)
		if ($method === 'price') {
			return new _MethodPrice();
		}

		// rental item specific method: availability($start=null, $end=null)
		if ($method === 'availability') {
			return new _MethodAvailability();
		}

		return parent::_mapMethod($method);
	}
}
