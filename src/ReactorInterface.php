<?php

declare(strict_types=1);

namespace Loner\Reactor;

use Loner\Reactor\Timer\TimerInterface;

/**
 * 事件循环
 *
 * @package Loner\Reactor
 */
interface ReactorInterface
{
    /**
     * 添加任务
     *
     * @param callable $listener
     */
    public function addTask(callable $listener): void;

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
     * @param int $interval
     * @param callable $listener
     * @param bool $periodic
     * @return TimerInterface
     */
    public function setTimer(float $interval, callable $listener, bool $periodic = false): TimerInterface;

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
     * @param TimerInterface $timer
     * @return bool
     */
    public function delTimer(TimerInterface $timer): bool;

    /**
     * 主回路
     */
    public function loop(): void;

    /**
     * 破坏回路
     */
    public function destroy(): void;
}
