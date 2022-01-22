<?php

declare(strict_types=1);

namespace Loner\Reactor;

/**
 * 构造器
 *
 * @package Loner\Reactor
 */
class Builder
{
    /**
     * 创建事件反应器实例
     *
     * @return ReactorInterface
     */
    public static function create(): ReactorInterface
    {
        return new Select();
    }
}
