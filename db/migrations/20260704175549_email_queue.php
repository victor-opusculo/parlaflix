<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class EmailQueue extends AbstractMigration
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
        $table = $this->table("email_queue");
        $table
        ->addColumn('destination_email', 'string', [ 'null' => false, 'length' => 280 ])
        ->addColumn('destination_name', 'string', [ 'null' => false, 'length' => 280 ])
        ->addColumn('subject', 'string', [ 'null' => false, 'length' => 280 ])
        ->addColumn('message', 'text', [ 'null' => false ])
        ->create();
    }
}
