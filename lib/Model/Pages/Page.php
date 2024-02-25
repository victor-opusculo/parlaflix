<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Pages;

use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;

final class Page extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty('id', fn() => null, DataProperty::MYSQL_INT),
            'title' => new DataProperty('name', fn() => '', DataProperty::MYSQL_STRING),
            'content' => new DataProperty('content', fn() => '', DataProperty::MYSQL_STRING),
            'html_enabled' => new DataProperty('htmlEnabled', fn() => 0, DataProperty::MYSQL_INT),
            'is_published' => new DataProperty('isPublished', fn() => 1, DataProperty::MYSQL_INT)
        ];

        $this->properties->html_enabled->valueTransformer = [Data::class, 'booleanTransformer'];
        $this->properties->is_published->valueTransformer = [Data::class, 'booleanTransformer'];

        parent::__construct($initialValues);
    }
}