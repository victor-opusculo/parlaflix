<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class TestCompletedTimestamp extends AbstractMigration
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
        $table = $this->table("tests_completed");
        $table
        ->addColumn('created_at', 'datetime', [ 'null' => false, 'after' => 'is_approved' ])
        ->addIndex('created_at')
        ->addIndex('subscription_id')
        ->addIndex('lesson_id')
        ->update();
    }
}
