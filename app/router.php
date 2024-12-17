<?php

namespace VictorOpusculo\Parlaflix\App;

return
[
    '/' => \VictorOpusculo\Parlaflix\App\HomePage::class,
    '/page' => fn() =>
    [
        '/[pageId]' => \VictorOpusculo\Parlaflix\App\Page\PageId::class
    ],
    '/info' => fn() =>
    [
        '/course' => fn() =>
        [
            '/' => \VictorOpusculo\Parlaflix\App\Info\Course\Home::class,
            '/[courseId]' => \VictorOpusculo\Parlaflix\App\Info\Course\CourseId::class
        ],
        '/category' => fn() =>
        [
            '/' => \VictorOpusculo\Parlaflix\App\Info\Category\Home::class
        ]
    ],
    '/student' => fn() =>
    [
        '/' => \VictorOpusculo\Parlaflix\App\Student\Login::class,
        '/login' => \VictorOpusculo\Parlaflix\App\Student\Login::class,
        '/register' => \VictorOpusculo\Parlaflix\App\Student\Register::class,
        '/recover_password' => \VictorOpusculo\Parlaflix\App\Student\RecoverPassword::class,
        '/panel' => fn() =>
        [
            '/' => \VictorOpusculo\Parlaflix\App\Student\Panel\Home::class,
            '/edit_profile' => \VictorOpusculo\Parlaflix\App\Student\Panel\EditProfile::class,
            '__layout' => \VictorOpusculo\Parlaflix\App\Student\Panel\PanelLayout::class,
            '/subscription' => fn() =>
            [
                '/' => \VictorOpusculo\Parlaflix\App\Student\Panel\Subscription\Home::class,
                '/[subscriptionId]' => \VictorOpusculo\Parlaflix\App\Student\Panel\Subscription\SubscriptionId::class
            ]
        ]
    ],
    '/admin' => fn() =>
    [
        '/' => \VictorOpusculo\Parlaflix\App\Admin\Login::class,
        '/login' => \VictorOpusculo\Parlaflix\App\Admin\Login::class,
        '/panel' => fn() =>
        [
            '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Home::class,
            '/edit_profile' => \VictorOpusculo\Parlaflix\App\Admin\Panel\EditProfile::class,
            '__layout' => \VictorOpusculo\Parlaflix\App\Admin\Panel\PanelLayout::class,
            '/media' => fn() =>
            [
                '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Media\Home::class,
                '/create' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Media\Create::class,
                '/[mediaId]' => fn() =>
                [
                    '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Media\MediaId\View::class,
                    '/edit' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Media\MediaId\Edit::class,
                    '/delete' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Media\MediaId\Delete::class
                ]
            ],
            '/pages' => fn() =>
            [
                '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Pages\Home::class,
                '/create' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Pages\Create::class,
                '/set_homepage' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Pages\SetHomePage::class,
                '/[pageId]' => fn() =>
                [
                    '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Pages\PageId\View::class,
                    '/edit' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Pages\PageId\Edit::class,
                    '/delete' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Pages\PageId\Delete::class
                ]
            ],
            '/categories' => fn() =>
            [
                '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Categories\Home::class,
                '/create' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Categories\Create::class,
                '/[categoryId]' => fn() =>
                [
                    '/edit' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Categories\CatId\Edit::class,
                    '/delete' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Categories\CatId\Delete::class
                ]
            ],
            '/courses' => fn() =>
            [
                '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\Home::class,
                '/create' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\Create::class,
                '/[courseId]' => fn() =>
                [
                    '/edit' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId\Edit::class,
                    '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId\View::class,
                    '/delete' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId\Delete::class,
                    '/view_subscriptions' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId\ViewSubscriptions::class,
                    '/send_email' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Courses\CourseId\SendEmail::class
                ]
            ],
            '/certificates' => fn() =>
            [
                '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Certificates\Home::class,
                '/set_bg_image' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Certificates\SetBgImage::class
            ],
            '/students' => fn() =>
            [
                '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Students\Home::class,
                '/[studentId]' => fn() =>
                [
                    '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Students\StudentId\View::class,
                    '/edit' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Students\StudentId\Edit::class,
                    '/delete' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Students\StudentId\Delete::class
                ]
            ],
            '/subscriptions' => fn() =>
            [
                '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Subscriptions\Home::class,
                '/[subscriptionId]' => fn() =>
                [
                    '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Subscriptions\SubscriptionId\View::class,
                    '/delete' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Subscriptions\SubscriptionId\Delete::class
                ]
            ],
            '/settings' => fn() =>
            [
                '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Settings\Home::class
            ]
        ]
    ],
    '/certificate' => fn() =>
    [
        '/auth' => \VictorOpusculo\Parlaflix\App\Certificate\Auth::class
    ],
    '__layout' => \VictorOpusculo\Parlaflix\App\BaseLayout::class,
	'__error' => \VictorOpusculo\Parlaflix\App\BaseError::class
];