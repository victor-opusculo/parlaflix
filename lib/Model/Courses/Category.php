<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;

class Category extends DataEntity
{
    public function __construct(?array $initialValues)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'title' => new DataProperty('title', fn() => null, DataProperty::MYSQL_STRING),
            'icon_media_id' => new DataProperty('iconMediaId', fn() => null, DataProperty::MYSQL_INT)
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'categories';
    protected string $formFieldPrefixName = 'categories';
    protected array $primaryKeys = ['id']; 
}