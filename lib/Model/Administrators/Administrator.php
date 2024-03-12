<?php
namespace VictorOpusculo\Parlaflix\Lib\Model\Administrators;

use Exception;
use mysqli;
use VOpus\PhpOrm\DataEntity;
use VOpus\PhpOrm\DataProperty;
use VOpus\PhpOrm\Exceptions\DatabaseEntityNotFound;
use VOpus\PhpOrm\Option;
use VOpus\PhpOrm\SqlSelector;

class Administrator extends DataEntity
{
    public function __construct(?array $initialValues = null)
    {
        $this->properties = (object)
        [
            'id' => new DataProperty(null, fn() => null, DataProperty::MYSQL_INT),
            'full_name' => new DataProperty('fullname', fn() => null, DataProperty::MYSQL_STRING),
            'email' => new DataProperty('email', fn() => null, DataProperty::MYSQL_STRING),
            'password_hash' => new DataProperty(null, fn() => null, DataProperty::MYSQL_STRING),
            'timezone' => new DataProperty('timezone', fn() => 'America/Sao_Paulo', DataProperty::MYSQL_STRING)
        ];
        
        $this->properties->full_name->valueTransformer = 
            fn(Option $v) => Option::some(\VictorOpusculo\Parlaflix\Lib\Helpers\Data::formatNameCase($v->unwrapOrElse(fn() => throw new Exception('Nome não informado!'))));

        parent::__construct($initialValues);
    }

    protected string $databaseTable = 'administrators';
    protected string $formFieldPrefixName = 'administrators';
    protected array $primaryKeys = ['id']; 

    public function getByEmail(mysqli $conn) : self
    {
        $selector = $this->getGetSingleSqlSelector()
        ->clearValues()
        ->clearWhereClauses()
        ->addWhereClause("{$this->getWhereQueryColumnName('email')} = ?")
        ->addValue('s', $this->properties->email->getValue()->unwrapOr(''));

        $dr = $selector->run($conn, SqlSelector::RETURN_SINGLE_ASSOC);
        if (isset($dr))
            return $this->newInstanceFromDataRow($dr);
        else
            throw new DatabaseEntityNotFound("Conta não localizada!", $this->databaseTable);
    }

    public function exists(mysqli $conn) : bool
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable)
        ->addWhereClause("{$this->getWhereQueryColumnName('id')} = ?")
        ->addValue('i', $this->properties->id->getValue()->unwrapOr(0));

        $count = $selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return (int)$count > 0;
    }

    public function existsEmail(mysqli $conn) : bool
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable)
        ->addWhereClause("{$this->getWhereQueryColumnName('email')} = ?")
        ->addValue('s', $this->properties->email->getValue()->unwrapOr(''));

        $count = $selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return (int)$count > 0;
    }

    public function existsAnotherAdminWithEmail(mysqli $conn) : bool
    {
        $selector = (new SqlSelector)
        ->addSelectColumn('COUNT(*)')
        ->setTable($this->databaseTable)
        ->addWhereClause("{$this->getWhereQueryColumnName('email')} = ?")
        ->addWhereClause(" AND {$this->getWhereQueryColumnName('id')} != ?")
        ->addValue('s', $this->properties->email->getValue()->unwrapOr(''))
        ->addValue('i', $this->properties->id->getValue()->unwrapOr(''));

        $count = $selector->run($conn, SqlSelector::RETURN_FIRST_COLUMN_VALUE);
        return (int)$count > 0;
    }

    public function checkPassword(string $password) : bool
    {
        return password_verify($password, $this->properties->password_hash->getValue()->unwrapOr('***'));
    }

    public function hashPassword(string $newPassword) : self
    {
        $this->properties->password_hash->setValue(password_hash($newPassword, PASSWORD_DEFAULT));
        return $this;
    }
}