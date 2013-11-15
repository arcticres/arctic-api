<?php

/**
 * Class Arctic_Country
 * Read only!
 * @property int $id
 * @property string $name
 * @property string $twodigitcode
 * @property string $threedigitcode
 * @property string $numericcode
 * @property string $phonecode
 * @property string $postalcodemask
 * @property string $phonenumbermask
 */
class Arctic_Country extends ArcticModel
{
	public static function getApiPath() {
		return 'country';
	}

	public function __construct() {
		parent::__construct();
	}
}
