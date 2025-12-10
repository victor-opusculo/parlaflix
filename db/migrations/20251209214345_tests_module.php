<?php
declare(strict_types=1);

require_once __DIR__ . "/../../lib/Model/Courses/PresenceMethod.php";


use Phinx\Migration\AbstractMigration;
use VictorOpusculo\Parlaflix\Lib\Model\Courses\PresenceMethod;

final class TestsModule extends AbstractMigration
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
        $table = $this->table("tests_skel");
        $table
        ->addColumn("lesson_id", "integer", [ 'null' => false, 'signed' => false])
        ->addColumn("name", "string", [ 'limit' => 280, 'null' => false ])
        ->addColumn("presentation_text", "text", [ 'null' => true ])
        ->addColumn("test_data", "json", [ 'null' => false ])
        ->addColumn("min_percent_for_approval", "integer", [ 'null' => false, 'default' => 70 ])
        ->addForeignKey('lesson_id', 'course_lessons', 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ])
        ->addIndex('name', [ 'type' => 'fulltext' ])
        ->create();

        $table = $this->table("tests_completed");
        $table
        ->addColumn("subscription_id", 'integer', [ 'null' => false, 'signed' => false ])
        ->addColumn("test_skel_id", 'integer', [ 'null' => true, 'signed' => false ])
        ->addColumn("lesson_id", 'integer', [ 'null' => false, 'signed' => false ])
        ->addColumn('test_data', 'json', [ 'null' => false ])
        ->addColumn('is_approved', 'boolean')
        ->addForeignKey('subscription_id', "student_subscriptions", 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ])
        ->addForeignKey('test_skel_id', "tests_skel", 'id', [ 'delete' => 'SET_NULL', 'update' => 'CASCADE' ])
        ->addForeignKey('lesson_id', 'course_lessons', 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ])
        ->create();

        $table = $this->table("course_lessons");
        $table
        ->addColumn('presence_method', "string", [ 'null' => false, 'limit' => 50, 'default' => PresenceMethod::Password->value, 'after' => 'video_url' ])
        ->update();
    }
}
