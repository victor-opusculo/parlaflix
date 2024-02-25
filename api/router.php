<?php
return 
[
    '/administrator' => fn() =>
    [
        '/login' => \VictorOpusculo\Parlaflix\Api\Administrator\Login::class,
        '/logout' => \VictorOpusculo\Parlaflix\Api\Administrator\Logout::class
    ]
];