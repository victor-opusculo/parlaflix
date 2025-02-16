<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class StudentReviews extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $table = $this->table('course_surveys');
        $table
        ->addColumn('course_id', 'integer', [ 'null' => false, 'signed' => false ])
        ->addColumn('student_id', 'integer', [ 'null' => true, 'signed' => false ])
        ->addColumn('points', 'integer', [ 'signed' => false, 'null' => false ])
        ->addColumn('message', 'text', [ 'null' => true ])
        ->addColumn('created_at', 'datetime', [ 'null' => false ])
        ->addForeignKey('course_id', 'courses', 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ])
        ->addForeignKey('student_id', 'students', 'id', [ 'delete' => 'SET_NULL', 'update' => 'CASCADE' ])
        ->addIndex('message', [ 'type' => 'fulltext' ])
        ->create();
    }
}
