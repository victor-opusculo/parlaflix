<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class BaseData extends AbstractMigration
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
        $adm = $this->table('administrators');
        $adm
        ->insert(
        [
            [ 
                'email' => 'victor.ventura@uol.com.br',
                'full_name' => 'Victor Opusculo Oliveira Ventura de Almeida',
                'password_hash' => password_hash('12345678', PASSWORD_DEFAULT),
                'timezone' => 'America/Sao_Paulo'  
            ],
            [
                'email' => 'abel@portalabel.org.br',
                'full_name' => 'Abel',
                'password_hash' => password_hash('12345678', PASSWORD_DEFAULT),
                'timezone' => 'America/Sao_Paulo'
            ]
        ])
        ->saveData();

        $sett = $this->table('settings');
        $sett
        ->insert(
        [
            [
                'name' => 'DEFAULT_LGPD_TERM_VERSION',
                'value' => '1'
            ],
            [
                'name' => 'DEFAULT_LGPD_TERM_TEXT',
                'value' => file_get_contents(__DIR__ . '/premade_data/lgpdTerm1.html')
            ]
        ])
        ->saveData();
    }

    public function down(): void
    {
        $adm = $this->table('administrators');
        $adm->truncate();

        $sett = $this->table('settings');
        $sett->truncate();
    }
}
