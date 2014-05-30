<?php

namespace Arctic\Model\PhotoSite;

use Arctic\Model;

/**
 * Class PhotoRetailItem
 * @property int $id
 * @property string $name
 * @property string $shortname
 * @property string $variation
 * @property string $description
 * @property array $taxrates
 * @property bool $requirephysicaldelivery
 * @property int $storeshippingclassid
 * @property float $weight
 * @property string $mixable
 * @property bool $enablephotos
 * @property bool $enablephotosets
 * @property int $maxphotos
 * @property int $maxphotosets
 * @property int $maxfiles
 * @property int $maxfilesize In bytes.
 * @property float $baseprice
 * @property float $priceperphoto
 * @property float $priceperphotoset
 * @property float $priceperphotosetphoto
 * @property int $accountid
 * @property int $order
 * @property string $createdon
 * @property string $modifiedon
 * @property bool $deleted
 * @property \Arctic\Model\Store\ShippingClass $shippingclass
 */
class PhotoRetailItem extends Model
{
    public static function getApiPath() {
        return 'photoretailitem';
    }

    public function __construct() {
        parent::__construct();

        $this->_addSingleReference('shippingclass', 'Arctic\Model\Store\ShippingClass');
    }
}
