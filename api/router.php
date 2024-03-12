<?php
return 
[
    '/student' => fn() =>
    [
        '/login' => \VictorOpusculo\Parlaflix\Api\Student\Login::class,
        '/logout' => \VictorOpusculo\Parlaflix\Api\Student\Logout::class,
        '/register' => \VictorOpusculo\Parlaflix\Api\Student\Register::class,
        '/presence' => \VictorOpusculo\Parlaflix\Api\Student\Presence::class,
        '/subscribe' => fn() =>
        [
            '/[courseId]' => \VictorOpusculo\Parlaflix\Api\Student\Subscribe\CourseId::class
        ]
    ],
    '/administrator' => fn() =>
    [
        '/login' => \VictorOpusculo\Parlaflix\Api\Administrator\Login::class,
        '/logout' => \VictorOpusculo\Parlaflix\Api\Administrator\Logout::class,
        '/[id]' => \VictorOpusculo\Parlaflix\Api\Administrator\AdminId::class,
        '/panel' => fn() =>
        [
            '/media' => fn() =>
            [
                '/' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Media\Home::class,
                '/create' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Media\Create::class,
                '/[mediaId]' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Media\MediaId::class
            ],
            '/pages' => fn() =>
            [
                '/' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Pages\Home::class,
                '/create' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Pages\Create::class,
                '/set_homepage' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Pages\SetHomepage::class,
                '/[pageId]' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Pages\PageId::class
            ],
            '/categories' => fn() =>
            [
                '/create' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Categories\Create::class,
                '/[categoryId]' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Categories\CatId::class
            ],
            '/courses' => fn() =>
            [
                '/create' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Courses\Create::class,
                '/[courseId]' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Courses\CourseId::class
            ],
            '/certificates' => fn() =>
            [
                '/set_bg_image' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Certificates\SetBgImage::class
            ],
            '/students' => fn() =>
            [
                '/[studentId]' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Students\StudentId::class
            ],
            '/subscriptions' => fn() =>
            [
                '/[subscriptionId]' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Subscriptions\SubscriptionId::class
            ]
        ]
    ]
];