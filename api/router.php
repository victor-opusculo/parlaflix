<?php
return 
[
    '/student' => fn() =>
    [
        '/login' => \VictorOpusculo\Parlaflix\Api\Student\Login::class,
        '/logout' => \VictorOpusculo\Parlaflix\Api\Student\Logout::class,
        '/register' => \VictorOpusculo\Parlaflix\Api\Student\Register::class,
        '/presence' => \VictorOpusculo\Parlaflix\Api\Student\Presence::class,
        '/[studentId]' => \VictorOpusculo\Parlaflix\Api\Student\StudentId::class,
        '/subscribe' => fn() =>
        [
            '/[courseId]' => \VictorOpusculo\Parlaflix\Api\Student\Subscribe\CourseId::class
        ],
        '/recover_password' => fn() =>
        [
            '/request_otp' => \VictorOpusculo\Parlaflix\Api\Student\RecoverPassword\RequestOtp::class,
            '/change_password' => \VictorOpusculo\Parlaflix\Api\Student\RecoverPassword\ChangePassword::class
        ]
    ],
    '/certificate' => fn() =>
    [
        '/auth' => \VictorOpusculo\Parlaflix\Api\Certificate\Auth::class
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
                '/[subscriptionId]' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Subscriptions\SubscriptionId::class,
                '/send_email' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Subscriptions\SendEmail::class
            ],
            '/reports' => fn() =>
            [
                '/export_course_subscriptions' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Reports\ExportCourseSubscriptions::class
            ]
        ]
    ]
];