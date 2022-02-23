<?php

declare(strict_types=1);

namespace Loner\Reactor\Timer;

/**
 * 计时器
 *
 * @package Loner\Reactor\Timer
 */
class Timer
{
    /**
     * 最小间隙时间（1us）
     */
    public const MIN_INTERVAL = 1e-6;

    /**
     * 间隔时间
     *
     * @var float
     */
    private float $interval;

    /**
     * 侦听回调
     *
     * @var callable
     */
    private $listener;

    /**
     * 是否周期性
     *
     * @var bool
     */
    private bool $periodic;

    /**
     * 构造信息
     *
     * @param float $interval
     * @param callable $listener
     * @param bool $periodic
     */
    public function __construct(float $interval, callable $listener, bool $periodic = false)
    {
        $this->interval = $interval < self::MIN_INTERVAL ? self::MIN_INTERVAL : $interval;
        $this->listener = $listener;
        $this->periodic = $periodic;
    }

    /**
     * 返回间隔时间（微秒）
     *
     * @return float
     */
    public function getInterval(): float
    {
        return $this->interval;
    }

    /**
     * 返回侦听回调
     *
     * @return callable
     */
    public function getListener(): callable
    {
        return $this->listener;
    }

    /**
     * 返回是否周期性
     *
     * @return bool
     */
    public function isPeriodic(): bool
    {
        return $this->periodic;
    }
}
