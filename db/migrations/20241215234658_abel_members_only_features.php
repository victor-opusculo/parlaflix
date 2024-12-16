<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AbelMembersOnlyFeatures extends AbstractMigration
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
        $students = $this->table('students');
        $students->addColumn('is_abel_member', 'boolean', [ 'null' => false, 'default' => 0, 'after' => 'timezone' ]);
        $students->update();

        $courses = $this->table('courses');
        $courses->addColumn('members_only', 'boolean', [ 'null' => false, 'default' => 0, 'after' => 'is_visible' ]);
        $courses->update();
    }
}
