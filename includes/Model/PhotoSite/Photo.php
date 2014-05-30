<?php

namespace Arctic\Model\PhotoSite;

use Arctic\Model;

/**
 * Class Photo
 * @property int $socialregionid
 * @property int $photosetid
 * @property int $id
 * @property string $title
 * @property string $filename
 * @property string $filetype as mime type
 * @property int $filesize (in bytes)
 * @property int $imagewidth
 * @property int $imageheight
 * @property array $exifdata
 * @property bool $forpurchase
 * @property int $postedbypersonid
 * @property int $serverid
 * @property bool $oncdn
 * @property int $cdnversion
 * @property string $shorthash
 * @property string $hash
 * @property string|null $takenon
 * @property string $createdon
 * @property bool $deleted
 * @property PhotoSet $photoset
 */
class Photo extends Model
{
    public static function getApiPath() {
        return 'photo';
    }

    public function __construct() {
        parent::__construct();

        $this->_addSingleReference('photoset', __NAMESPACE__ . '\PhotoSet', array(
        	'photosetid'	=>	'id'
        ));
    }
}
