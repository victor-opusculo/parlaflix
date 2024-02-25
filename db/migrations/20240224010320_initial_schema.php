<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class InitialSchema extends AbstractMigration
{
    public function change(): void
    {
        $media = $this->table('media');
        $media
        ->addColumn('name', 'string', [ 'limit' => 140 ])
        ->addColumn('description', 'text')
        ->addColumn('file_extension', 'string', [ 'limit' => 50 ])
        ->addIndex([ 'name', 'description' ], [ 'type' => 'fulltext' ])
        ->create();

        $pages = $this->table('pages');
        $pages
        ->addColumn('title', 'string', [ 'limit' => 140, 'null' => false ])
        ->addColumn('content', 'text')
        ->addColumn('html_enabled', 'boolean', [ 'default' => 0 ])
        ->addColumn('is_published', 'boolean', [ 'default' => 1 ])
        ->addIndex([ 'title', 'content' ], [ 'type' => 'fulltext' ])
        ->create();

        $settings = $this->table('settings', [ 'id' => false, 'primary_key' => 'name' ]);
        $settings
        ->addColumn('name', 'string', [ 'limit' => 100, 'null' => false ])
        ->addColumn('value', 'text', [ 'null' => false ])
        ->create();

        $administrators = $this->table('administrators');
        $administrators
        ->addColumn('email', 'string', [ 'limit' => 140, 'null' => false ])
        ->addColumn('full_name', 'string', [ 'null' => false ])
        ->addColumn('password_hash', 'string', [ 'limit' => 140, 'null' => false ])
        ->addColumn('timezone', 'string', [ 'limit' => 100, 'null' => false, 'default' => 'America/Sao_Paulo' ])
        ->addIndex('email', [ 'unique' => true ])
        ->create();

        $students = $this->table('students');
        $students
        ->addColumn('email', 'varbinary', [ 'limit' => 400, 'null' => false ])
        ->addColumn('full_name', 'varbinary', [ 'limit' => 400, 'null' => false ])
        ->addColumn('other_data', 'varbinary', [ 'limit' => 2000, 'null' => true ])
        ->addColumn('password_hash', 'string', [ 'limit' => 400, 'null' => false ])
        ->addColumn('timezone', 'string', [ 'limit' => 140, 'null' => false ])
        ->addColumn('lgpd_term_version', 'integer', [ 'null' => false ])
        ->addColumn('lgpd_term', 'text', [ 'null' => false ])
        ->create();

        $courses = $this->table('courses');
        $courses
        ->addColumn('name', 'string', [ 'limit' => 280, 'null' => false ])
        ->addColumn('presentation_html', 'text')
        ->addColumn('cover_image_media_id', 'integer', [ 'signed' => false, 'null' => true ])
        ->addColumn('hours', 'decimal', [ 'null' => false ])
        ->addColumn('certificate_text', 'text')
        ->addColumn('min_points_required', 'integer', [ 'null' => false ])
        ->addColumn('is_visible', 'boolean', [ 'default' => 1 ])
        ->addColumn('created_at', 'datetime', [ 'null' => false ])
        ->addForeignKey('cover_image_media_id', 'media', 'id', [ 'delete' => 'SET_NULL', 'update' => 'CASCADE' ])
        ->addIndex('name', [ 'type' => 'fulltext' ])
        ->create();

        $courseLessons = $this->table('course_lessons');
        $courseLessons
        ->addColumn('course_id', 'integer', [ 'signed' => false, 'null' => false ])
        ->addColumn('index', 'integer', [ 'null' => false ])
        ->addColumn('title', 'string', [ 'limit' => 280 ])
        ->addColumn('presentation_html', 'text')
        ->addColumn('video_host', 'string', [ 'limit' => 280, 'null' => true ])
        ->addColumn('video_url', 'string', [ 'limit' => 280, 'null' => true ])
        ->addColumn('completion_password', 'string', [ 'limit' => 100, 'null' => true ])
        ->addColumn('completion_points', 'integer', [ 'null' => true, 'default' => 1 ])
        ->addForeignKey('course_id', 'courses', 'id', [ 'update' => 'CASCADE', 'delete' => 'CASCADE' ])
        ->create();

        $categories = $this->table('categories');
        $categories
        ->addColumn('title', 'string', [ 'null' => false, 'limit' => 280 ])
        ->addColumn('icon_media_id', 'integer', [ 'signed' => false, 'null' => true ])
        ->addForeignKey('icon_media_id', 'media', 'id', [ 'delete' => 'SET_NULL', 'update' => 'CASCADE' ])
        ->create();

        $coursesCategoriesJoin = $this->table('courses_categories_join', [ 'id' => false, 'primary_key' => [ 'course_id', 'category_id' ] ]);
        $coursesCategoriesJoin
        ->addColumn('course_id', 'integer', [ 'signed' => false, 'null' => false ])
        ->addColumn('category_id', 'integer', [ 'signed' => false, 'null' => false ])
        ->addForeignKey('course_id', 'courses', 'id', [ 'update' => 'CASCADE', 'delete' => 'CASCADE' ])
        ->addForeignKey('category_id', 'categories', 'id', [ 'update' => 'CASCADE', 'delete' => 'CASCADE' ])
        ->create();

        $studentSubscriptions = $this->table('student_subscriptions');
        $studentSubscriptions
        ->addColumn('student_id', 'integer', [ 'signed' => false, 'null' => true ])
        ->addColumn('course_id', 'integer', [ 'signed' => false, 'null' => true ])
        ->addColumn('datetime', 'datetime')
        ->addForeignKey('course_id', 'courses', 'id', [ 'update' => 'CASCADE', 'delete' => 'SET_NULL' ])
        ->addForeignKey('student_id', 'students', 'id', [ 'update' => 'CASCADE', 'delete' => 'SET_NULL' ])
        ->create();

        $studentGivenLessonPasswords = $this->table('student_lesson_passwords');
        $studentGivenLessonPasswords
        ->addColumn('student_id', 'integer', [ 'signed' => false, 'null' => false ])
        ->addColumn('lesson_id', 'integer', [ 'signed' => false, 'null' => false ])
        ->addColumn('given_password', 'string', [ 'limit' => 100, 'null' => false ])
        ->addColumn('is_correct', 'boolean')
        ->addForeignKey('student_id', 'students', 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ])
        ->addForeignKey('lesson_id', 'course_lessons', 'id', [ 'delete' => 'CASCADE', 'update' => 'CASCADE' ])
        ->create();

        $generatedCertificates = $this->table('generated_certificates');
        $generatedCertificates
        ->addColumn('course_id', 'integer', [ 'signed' => false, 'null' => true ])
        ->addColumn('student_id', 'integer', [ 'signed' => false, 'null' => true ])
        ->addColumn('datetime', 'datetime', [ 'null' => false ])
        ->addForeignKey('course_id', 'courses', 'id', [ 'update' => 'CASCADE', 'delete' => 'SET_NULL' ])
        ->addForeignKey('student_id', 'students', 'id', [ 'update' => 'CASCADE', 'delete' => 'SET_NULL' ])
        ->addIndex([ 'course_id', 'student_id' ], [ 'unique' => true ])
        ->create();
    }
}
