<?php

declare(strict_types=1);

namespace Loner\Reactor\Exception;

use Exception;
use Throwable;

/**
 * 异常：定时任务时间规则错误
 *
 * @package Loner\Reactor\Exception
 */
class CrontabFormatException extends Exception implements Throwable
{
    /**
     * 错误码：格式错误
     */
    public const FORMAT_ERROR = 1;

    /**
     * 错误码：超出值范围
     */
    public const OUT_OF_RANGE = 2;
}
