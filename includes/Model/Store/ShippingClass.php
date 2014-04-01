<?php

namespace Arctic\Model\Store;

use Arctic\Model;

/**
 * Class ShippingClass
 * @property int $id
 * @property string $name
 * @property bool $canpickup
 * @property string $pickupfiltertool
 * @property string $createdon
 * @property string $modifiedon
 * @property bool $deleted
 * @property ShippingMethod $methods[]
 */
class ShippingClass extends Model
{
    public static function getApiPath() {
        return 'shippingclass';
    }

    public function __construct() {
        parent::__construct();

        $this->_addMultipleReference('methods', __NAMESPACE__ . '\ShippingMethod', 'method');
    }
}
