<?php

namespace Arctic\Model\PhotoSite;

use Arctic\Model;

/**
 * Class PhotoSet
 * @property int $socialregionid
 * @property int $id
 * @property string $name
 * @property string $takenonstart
 * @property string $takenonend
 * @property string $timestart
 * @property string $timeend
 * @property bool $active - default to 1 for guest uploads, default to 0 for backend uploads (must be categorized and such)
 * @property bool $forpurchase
 * @property int $postedbypersonid
 * @property int $serverid
 * @property bool $oncdn Full uploaded (all photos too).
 * @property int $cdnversion
 * @property string $shorthash
 * @property string $createdon
 * @property bool $deleted
 * @property Photo[] $photos
 */
class PhotoSet extends Model
{
    public static function getApiPath() {
        return 'photoset';
    }

    public function __construct() {
        parent::__construct();

        $this->_addMultipleReference('photos', 'Arctic\Model\PhotoSite\Photo', 'photo');
    }
}
