<?php
return 
[
    '/administrator' => fn() =>
    [
        '/login' => \VictorOpusculo\Parlaflix\Api\Administrator\Login::class,
        '/logout' => \VictorOpusculo\Parlaflix\Api\Administrator\Logout::class,
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
                '/create' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Pages\Create::class,
                '/[pageId]' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Pages\PageId::class
            ],
            '/categories' => fn() =>
            [
                '/create' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Categories\Create::class,
                '/[categoryId]' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Categories\CatId::class
            ],
            '/courses' => fn() =>
            [
                '/create' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Courses\Create::class
            ]
        ]
    ]
];