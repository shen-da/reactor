<?php

declare(strict_types=1);

namespace Loner\Reactor\SignalHandler;

/**
 * 信号处理器
 *
 * @package Loner\Reactor\SignalHandler
 */
class SignalHandler
{
    /**
     * 信号侦听器列表
     *
     * @var callable[]
     */
    private array $signals = [];

    /**
     * 增改信号侦听器
     *
     * @param int $signal
     * @param callable $listener
     */
    public function set(int $signal, callable $listener): void
    {
        $this->signals[$signal] = $listener;
    }

    /**
     * 删除指定信号侦听器
     *
     * @param int $signal
     */
    public function del(int $signal): void
    {
        unset($this->signals[$signal]);
    }

    /**
     * 清空信号侦听器列表
     */
    public function clear(): void
    {
        $this->signals = [];
    }

    /**
     * 调用指定信号侦听器
     *
     * @param int $signal
     */
    public function call(int $signal): void
    {
        if ($this->has($signal)) {
            $this->signals[$signal]($signal);
        }
    }

    /**
     * 返回是否存在指定信号侦听器
     *
     * @param int $signal
     * @return bool
     */
    public function has(int $signal): bool
    {
        return isset($this->signals[$signal]);
    }

    /**
     * 返回信号侦听器列表是否为空
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->signals);
    }
}
