<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Courses;

use mysqli;
use VictorOpusculo\Parlaflix\Lib\Model\Media\Media;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;
use VOpus\PhpOrm\SqlSelector;

class Category extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'title' => new DataProperty('title', fn() => null, DataProperty::MYSQL_STRING),
            'icon_media_id' => new DataProperty('icon_media_id', fn() => null, DataProperty::MYSQL_INT)
        ];

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'categories';
    protected string $formFieldPrefixName = 'categories';
    protected array $primaryKeys = ['id']; 

    public ?Media $icon = null;

    public function getCount(mysqli $conn, string $searchKeywords) : int
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable);

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause("{$this->getWhereQueryColumnName('title')} LIKE ?")
            ->addValue('s', "%$searchKeywords%");
        }

        $count = (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return $count;
    }

    public function getMultiple(mysqli $conn, string $searchKeywords, string $orderBy, int $page, int $numResultsOnPage) : array
    {
        $selector = (new SqlSelector)
        ->addSelectColumn($this->getSelectQueryColumnName('id'))
        ->addSelectColumn($this->getSelectQueryColumnName('title'))
        ->addSelectColumn($this->getSelectQueryColumnName('icon_media_id'))
        ->setTable($this->databaseTable);

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause("{$this->getWhereQueryColumnName('title')} LIKE ?")
            ->addValue('s', "%$searchKeywords%");
        }

        $selector = $selector
        ->setOrderBy(match ($orderBy)
        {
            'title' => 'title ASC',
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

    public function fetchIcon(mysqli $conn) : self
    {
        if (!$this->properties->icon_media_id->getValue()->unwrapOr(0)) 
            return $this;

        $this->icon = (new Media([ 'id' => $this->properties->icon_media_id->getValue()->unwrap() ]))->getSingle($conn);
        return $this;
    }

    public function beforeDatabaseInsert(mysqli $conn): int
    {
        try
        {
            (new Media([ 'id' => $this->properties->icon_media_id->getValue()->unwrapOr(0) ]))->getSingle($conn);
        }
        catch (DatabaseEntityNotFound $e)
        {
            throw new DatabaseEntityNotFound("ID de ícone não localizado no cadastro de mídias!", 'media');
        }
        return 0;
    }

    public function beforeDatabaseUpdate(mysqli $conn): int
    {
        return $this->beforeDatabaseInsert($conn);
    }
}