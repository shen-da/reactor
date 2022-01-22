<?php

declare(strict_types=1);

namespace Loner\Reactor\Timer;

/**
 * 计时器
 *
 * @package Loner\Reactor\Timer
 */
interface TimerInterface
{
    /**
     * 返回间隔时间
     *
     * @return float
     */
    public function getInterval(): float;

    /**
     * 返回侦听回调
     *
     * @return callable
     */
    public function getListener(): callable;

    /**
     * 返回是否周期性
     *
     * @return bool
     */
    public function isPeriodic(): bool;
}
