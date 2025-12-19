<?php

namespace VictorOpusculo\Parlaflix\Lib\Model\Students;

use Exception;
use mysqli;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\Course;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataObjectProperty;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;
use VOpus\PhpOrm\Option;
use VOpus\PhpOrm\SqlSelector;

/**
 * @property Option<int> id
 * @property Option<string> email
 * @property Option<string> full_name
 * @property Option<object{telephone:Option<string>,institution:Option<string>,instRole:Option<string>}> other_data
 * @property Option<string> password_hash
 * @property Option<string> timezone
 * @property Option<int> is_abel_member
 * @property Option<int> lgpd_term_version
 * @property Option<string> lgpd_term
 */
class Student extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty('id', fn() => null, DataProperty::MYSQL_INT, false),
            'email' => new DataProperty('email', fn() => null, DataProperty::MYSQL_STRING, true),
            'full_name' => new DataProperty('fullname', fn() => null, DataProperty::MYSQL_STRING, true),
            'other_data' => new DataObjectProperty((object)
            [
                'telephone' => new DataProperty('telephone', fn() => ''),
                'institution' => new DataProperty('institution', fn() => ''),
                'instRole' => new DataProperty('instrole', fn() => '')
            ], true), 
            'password_hash' => new DataProperty(null, fn() => null, DataProperty::MYSQL_STRING, false),
            'timezone' => new DataProperty('timezone', fn() => 'America/Sao_Paulo', DataProperty::MYSQL_STRING, false),
            'is_abel_member' => new DataProperty(null, fn() => 0, DataProperty::MYSQL_INT, false),
            'lgpd_term_version' => new DataProperty('lgpdtermversion', fn() => null, DataProperty::MYSQL_INT),
            'lgpd_term' => new DataProperty('lgpdTermText', fn() => null, DataProperty::MYSQL_STRING)
        ];

        $this->properties->full_name->valueTransformer = 
            fn(Option $val) => Option::some(mb_convert_case($val->unwrapOrElse(fn() => throw new Exception('Nome não informado!')), MB_CASE_TITLE, "UTF-8"));

        $this->properties->email->valueTransformer = fn(Option $val) => Option::some(mb_strtolower($val->unwrapOrElse(fn() => throw new Exception("E-mail não informado!")))); 

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'students';
    protected string $formFieldPrefixName = 'students';
    protected array $primaryKeys = ['id']; 

    public array $subscriptions = [];

    public function getByEmail(mysqli $conn) : self
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearWhereClauses()
        ->clearValues()
        ->addWhereClause("{$this->getWhereQueryColumnName('email')} = lower(?) ")
        ->addValue('s', $this->properties->email->getValue()->unwrapOr("n@d"));

        $dr = $selector->run($conn, SqlSelector::RETURN_SINGLE_ASSOC);

        if (empty($dr))
            throw new DatabaseEntityNotFound("E-mail não localizado!", $this->databaseTable);
        else
            return $this->newInstanceFromDataRow($dr);
    }

    public function existsEmail(mysqli $conn) : bool
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable)
        ->addWhereClause("{$this->getWhereQueryColumnName('email')} = lower(?) ")
        ->addValue('s', $this->properties->email->getValue()->unwrapOr(null));

        $count = $selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return (int)$count > 0;
    }

    public function existsAnotherStudentWithEmail(mysqli $conn) : bool
    {
        $selector = (new SqlSelector)
        ->addSelectColumn("COUNT(*)")
        ->setTable($this->databaseTable)
        ->addWhereClause("{$this->getWhereQueryColumnName('email')} = lower(?) ")
        ->addWhereClause(" AND {$this->getWhereQueryColumnName('id')} != ? ")
        ->addValue('s', $this->properties->email->getValue()->unwrapOr(null))
        ->addValue('i', $this->properties->id->getValue()->unwrapOr(null));

        $count = $selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return (int)$count > 0;

    }

    public function checkPassword(string $givenPassword) : bool
    {
        return password_verify($givenPassword, $this->properties->password_hash->getValue()->unwrapOr('***'));
    }

    public function hashPassword(string $password) : self
    {
        $this->properties->password_hash->setValue(password_hash($password, PASSWORD_DEFAULT));
        return $this;
    }

    public function getCount(mysqli $conn, string $searchKeywords) : int
    {
        $selector = (new SqlSelector)
        ->addSelectColumn("COUNT(*)")
        ->setTable($this->databaseTable);

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause("Convert({$this->getWhereQueryColumnName('full_name')} using 'utf8mb4') LIKE ?")
            ->addWhereClause("OR Convert({$this->getWhereQueryColumnName('email')} using 'utf8mb4') LIKE ?")
            ->addValues('ss', [ "%$searchKeywords%", "%$searchKeywords%" ]);
        }

        $count = (int)$selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return $count;
    }

    public function getMultiple(mysqli $conn, string $searchKeywords, string $orderBy, int $page, int $numResultsOnPage) : array
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearWhereClauses()
        ->clearValues();

        if (mb_strlen($searchKeywords) > 3)
        {
            $selector = $selector
            ->addWhereClause("Convert({$this->getWhereQueryColumnName('full_name')} using 'utf8mb4') LIKE ?")
            ->addWhereClause("OR Convert({$this->getWhereQueryColumnName('email')} using 'utf8mb4') LIKE ?")
            ->addValues('ss', [ "%$searchKeywords%", "%$searchKeywords%" ]);
        }

        $selector->setOrderBy(match($orderBy)
        {
            'email' => 'email ASC',
            'name' => 'full_name ASC',
            'id' => 'id DESC',
            default => 'id DESC'
        });

        $calcPage = ($page - 1) * $numResultsOnPage;
        $selector = $selector->setLimit('?, ?')->addValues('ii', [ $calcPage, $numResultsOnPage ]);

        $drs = $selector->run($conn, SqlSelector::RETURN_ALL_ASSOC);
        return array_map([ $this, 'newInstanceFromDataRowFromDatabase' ], $drs);
    }

    public function fetchSubscriptions(mysqli $conn) : self
    {
        $subsGetter = new Subscription([ 'student_id' => $this->properties->id->getValue()->unwrapOr(0) ]);
        $subscriptions = $subsGetter->getAllFromStudent($conn);
        
        foreach ($subscriptions as $sub)
            $sub->fetchCourse($conn);

        $this->subscriptions = $subscriptions;
        return $this;
    }

    public function fetchSubscriptionsWithProgressData(mysqli $conn) : self
    {
        $subsGetter = new Subscription([ 'student_id' => $this->properties->id->getValue()->unwrapOr(0) ]);
        $subscriptions = $subsGetter->getAllFromStudentWithProgressData($conn);
        
        foreach ($subscriptions as $sub)
            $sub->fetchCourse($conn);

        $this->subscriptions = $subscriptions;
        return $this;
    }
}