<?php

declare(strict_types=1);

namespace Loner\Reactor;

use Loner\Reactor\Crontab\Crontab;
use Loner\Reactor\Exception\CrontabFormatException;
use Loner\Reactor\Timer\Timer;

/**
 * 事件循环
 *
 * @package Loner\Reactor
 */
interface ReactorInterface
{
    /**
     * 初始化处理程序
     */
    public function initialize(): void;

    /**
     * 添加优先任务侦听器
     *
     * @param callable $listener
     */
    public function addSooner(callable $listener): void;

    /**
     * 设置读事件侦听器
     *
     * @param resource $stream
     * @param callable $listener
     * @return bool
     */
    public function setRead($stream, callable $listener): bool;

    /**
     * 设置写事件侦听器
     *
     * @param resource $stream
     * @param callable $listener
     * @return bool
     */
    public function setWrite($stream, callable $listener): bool;

    /**
     * 设置信号事件侦听器
     *
     * @param int $signal
     * @param callable $listener
     * @return bool
     */
    public function setSignal(int $signal, callable $listener): bool;

    /**
     * 添加计时器事件侦听器
     *
     * @param float $interval
     * @param callable $listener
     * @param bool $periodic
     * @return Timer
     */
    public function addTimer(float $interval, callable $listener, bool $periodic = false): Timer;

    /**
     * 添加定时任务侦听器
     *
     * @param callable $listener
     * @param string ...$timeRules
     * @return Crontab
     * @return CrontabFormatException
     */
    public function addCrontab(callable $listener, string ...$timeRules): Crontab;

    /**
     * 移除读事件侦听器
     *
     * @param resource $stream
     * @return bool
     */
    public function delRead($stream): bool;

    /**
     * 移除写事件侦听器
     *
     * @param resource $stream
     * @return bool
     */
    public function delWrite($stream): bool;

    /**
     * 移除信号事件侦听器
     *
     * @param int $signal
     * @return bool
     */
    public function delSignal(int $signal): bool;

    /**
     * 移除计时器事件侦听器
     *
     * @param Timer $timer
     * @return bool
     */
    public function delTimer(Timer $timer): bool;

    /**
     * 移除定时任务侦听器
     *
     * @param Crontab $crontab
     * @return bool
     */
    public function delCrontab(Crontab $crontab): bool;

    /**
     * 主回路
     */
    public function loop(): void;

    /**
     * 破坏回路
     */
    public function destroy(): void;
}
