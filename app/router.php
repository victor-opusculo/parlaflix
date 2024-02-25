<?php

namespace VictorOpusculo\Parlaflix\App;

return
[
    '/' => \VictorOpusculo\Parlaflix\App\HomePage::class,
    '/admin' => fn() =>
    [
        '/' => \VictorOpusculo\Parlaflix\App\Admin\Login::class,
        '/login' => \VictorOpusculo\Parlaflix\App\Admin\Login::class,
        '/panel' => fn() =>
        [
            '/' => \VictorOpusculo\Parlaflix\App\Admin\Panel\Home::class,
            '__layout' => \VictorOpusculo\Parlaflix\App\Admin\Panel\PanelLayout::class
        ]
    ],
    '__layout' => \VictorOpusculo\Parlaflix\App\BaseLayout::class
];