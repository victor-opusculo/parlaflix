<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class NewLgpdTerm extends AbstractMigration
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
    public function up(): void
    {
        /** @var \Cake\Database\Query\UpdateQuery */
        $qb = $this->getQueryBuilder("update");

        $qb
        ->update("settings")
        ->where(
        [
            'name' => 'DEFAULT_LGPD_TERM_VERSION'
        ])
        ->set('value', 2)
        ->execute();

        /** @var \Cake\Database\Query\UpdateQuery */
        $qb = $this->getQueryBuilder("update");

        $qb
        ->update("settings")
        ->where(
        [
            'name' => 'DEFAULT_LGPD_TERM_TEXT'
        ])
        ->set('value', file_get_contents(__DIR__ . '/premade_data/lgpdTerm2.html'))
        ->execute();
    }

    public function down(): void
    {
        /** @var \Cake\Database\Query\UpdateQuery */
        $qb = $this->getQueryBuilder("update");

        $qb
        ->update("settings")
        ->where(
        [
            'name' => 'DEFAULT_LGPD_TERM_VERSION'
        ])
        ->set('value', 1)
        ->execute();

        /** @var \Cake\Database\Query\UpdateQuery */
        $qb = $this->getQueryBuilder("update");

        $qb
        ->update("settings")
        ->where(
        [
            'name' => 'DEFAULT_LGPD_TERM_TEXT'
        ])
        ->set('value', file_get_contents(__DIR__ . '/premade_data/lgpdTerm1.html'))
        ->execute();
    }
}
