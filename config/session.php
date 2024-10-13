<?php
declare (strict_types=1);

return [
    "handler" => \Kernel\Session\Handler\File::class,
    "options" => [
        'lifetime' => 86400
    ]
];