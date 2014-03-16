<?php

namespace Arctic\Model\Trip;

use Arctic\Model;

/**
 * Class Component
 * @property string $parenttable
 * @property int $parentid
 * @property int $id
 * @property string $name
 * @property string $uniquename
 * @property string $addto "reservation", "member"
 * @property string $type "required", "optional", "set"
 * @property string $setname
 * @property bool $showonline
 * @property string $onlinedescription
 * @property bool $isdefault
 * @property float $price
 * @property int $accountid
 * @property array $invoicesubitems
 * @property string $createdon
 * @property string $modifiedon
 * @property bool $deleted
 */
class Component extends Model
{
	public static function getApiPath() {
		// currently does not have a direct api call
		// just accessed as a subobject of trips
		return null;
	}

	public function __construct() {
		parent::__construct();
	}
}
