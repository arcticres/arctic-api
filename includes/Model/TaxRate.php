<?php

namespace Arctic\Model;

use Arctic\Model;

/**
 * Class TaxRate
 * @property int $id
 * @property string $name
 * @property float $rate
 * @property int $accountid
 * @property string $createdon
 * @property string $modifiedon
 */
class TaxRate extends Model
{
    public static function getApiPath() {
        return 'taxrate';
    }

    public function __construct() {
        parent::__construct();
    }
}
