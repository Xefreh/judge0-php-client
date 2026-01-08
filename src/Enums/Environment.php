<?php

declare(strict_types=1);

namespace Xefreh\Judge0PhpClient\Enums;

enum Environment: string
{
    case Development = 'development';
    case Production = 'production';
}
