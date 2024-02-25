<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Media;

use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;

final class Media extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty('id', fn() => null, DataProperty::MYSQL_INT),
            'name' => new DataProperty('name', fn() => '', DataProperty::MYSQL_STRING),
            'file_extension' => new DataProperty('fileExtension', fn() => '', DataProperty::MYSQL_STRING)
        ];

        parent::__construct($initialValues);
    }
}