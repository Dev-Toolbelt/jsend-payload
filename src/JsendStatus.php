<?php

declare(strict_types=1);

namespace DevToolbelt\JsendPayload\Enums;

enum JsendStatus: string
{
    case SUCCESS = 'success';
    case FAIL = 'fail';
    case ERROR = 'error';
}
