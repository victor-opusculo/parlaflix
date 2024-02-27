<?php

namespace VictorOpusculo\Parlaflix\App;

return
[
    '/' => \VictorOpusculo\Parlaflix\App\HomePage::class,
    '/page' => fn() =>
    [
        '/[pageId]' => \VictorOpusculo\Parlaflix\App\Page\PageId::class
    ],
    '/admin' => fn() =>
    [
        '/' => \VictorOpusculo\Parlaflix\App\Admin\Login::class,
        '/login' => \VictorOpusculo\Parlaflix\App\Admin\Login::class,
        '/panel' => fn() =>
        [
            '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Home::class,
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
                '/create' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Categories\Create::class
            ]
        ]
    ],
    '__layout' => \VictorOpusculo\Parlaflix\App\BaseLayout::class
];