<?php

namespace Arctic\Model\Store;

use Arctic\Model;

/**
 * Class TaxRule
 * @property int $id
 * @property string $filtertool
 * @property array $includetaxrates IDs of tax rates to include.
 * @property array $excludetaxrates IDs of tax rates to exclude.
 * @property int $order
 */
class TaxRule extends Model
{
    public static function getApiPath() {
        return 'taxrule';
    }

    public function __construct() {
        parent::__construct();
    }
}
