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
                '/create' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Media\Create::class,
                '/[mediaId]' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Media\MediaId::class
            ],
            '/pages' => fn() =>
            [
                '/create' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Pages\Create::class,
                '/[pageId]' => \VictorOpusculo\Parlaflix\Api\Administrator\Panel\Pages\PageId::class
            ]
        ]
    ]
];