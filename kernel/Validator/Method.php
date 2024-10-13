<?php
declare (strict_types=1);

namespace Kernel\Validator;

interface Method
{
    public const POST = 0;
    public const GET = 2;
    public const COOKIE = 4;
    public const JSON = 8;
    public const HEADER = 16;
}