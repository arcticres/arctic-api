<?php

namespace Arctic\Model;

use Arctic\Model;

/**
 * Class FormField
 * @property string $formname
 * @property int $id
 * @property string $type
 * @property string $name
 * @property bool $builtin
 * @property bool $hidden
 * @property string $mapto
 * @property array $data
 * @property int $parentobjectid
 * @property int $order
 * @property string $createdon
 * @property string $modifiedon
 * @property bool $deleted
 */
class FormField extends Model
{
    public static function getApiPath() {
        return 'formfield';
    }

    public function __construct() {
        parent::__construct();
    }
}
