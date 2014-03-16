<?php

namespace Arctic\Model\Trip;

use Arctic\Model;

/**
 * Class PricingLevel
 * @property string $parenttable
 * @property int $parentid
 * @property int $id
 * @property string $name
 * @property bool $default
 * @property string $uniquename
 * @property string $description
 * @property float $amount
 * @property bool $showonline
 * @property string $createdon
 * @property string $modifiedon
 * @property bool $deleted
 */
class PricingLevel extends Model
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
