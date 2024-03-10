<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Pages;

use mysqli;
use VictorOpusculo\Parlaflix\Lib\Helpers\Data;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\SqlSelector;

final class Page extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty('id', fn() => null, DataProperty::MYSQL_INT),
            'title' => new DataProperty('title', fn() => '', DataProperty::MYSQL_STRING),
            'content' => new DataProperty('content', fn() => '', DataProperty::MYSQL_STRING),
            'html_enabled' => new DataProperty('html_enabled', fn() => 0, DataProperty::MYSQL_INT),
            'is_published' => new DataProperty('is_published', fn() => 1, DataProperty::MYSQL_INT)
        ];

        $this->properties->html_enabled->valueTransformer = [Data::class, 'booleanTransformer'];
        $this->properties->is_published->valueTransformer = [Data::class, 'booleanTransformer'];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'pages';
    protected string $formFieldPrefixName = 'pages';
    protected array $primaryKeys = ['id'];

    public function getCount(mysqli $conn, string $searchKeywords) : int
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable);

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause('MATCH (title, content) AGAINST (?)')
            ->addValue('s', $searchKeywords);
        }

        $count = (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return $count;
    }

    public function getMultiple(mysqli $conn, string $searchKeywords, string $orderBy, int $page, int $numResultsOnPage) : array
    {
        $selector = (new SqlSelector)
        ->addSelectColumn($this->getSelectQueryColumnName('id'))
        ->addSelectColumn($this->getSelectQueryColumnName('title'))
        ->addSelectColumn($this->getSelectQueryColumnName('content'))
        ->addSelectColumn($this->getSelectQueryColumnName('html_enabled'))
        ->addSelectColumn($this->getSelectQueryColumnName('is_published'))
        ->setTable($this->databaseTable);

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause('MATCH (title, content) AGAINST (?)')
            ->addValue('s', $searchKeywords);
        }

        $selector = $selector
        ->setOrderBy(match ($orderBy)
        {
            'title' => 'title ASC',
            'is_published' => 'is_published ASC',
            'id' => 'id DESC',
            default => 'id DESC'
        });

        $calcPage = ($page - 1) * $numResultsOnPage;
        $selector = $selector
        ->setLimit('?,?')
        ->addValues('ii', [ $calcPage, $numResultsOnPage ]);

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRow' ], $drs);
    }

    public function exists(mysqli $conn) : bool
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('Count(*)')
        ->setTable($this->databaseTable)
        ->addWhereClause("{$this->getWhereQueryColumnName('id')} = ?")
        ->addValue('i', $this->properties->id->getValue()->unwrapOr(null));

        $count = (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return $count > 0;
    } 
}