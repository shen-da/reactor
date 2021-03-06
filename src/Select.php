<?php

declare(strict_types=1);

namespace Loner\Reactor;

use Loner\Reactor\Crontab\{Crontab, CrontabScheduler};
use Loner\Reactor\SignalHandler\SignalHandler;
use Loner\Reactor\Sooner\SoonerScheduler;
use Loner\Reactor\Timer\{TimerScheduler, Timer};

/**
 * 备用事件循环
 *
 * @package Loner\Reactor
 */
class Select implements ReactorInterface
{
    /**
     * 优先任务调度程序
     *
     * @var SoonerScheduler
     */
    private SoonerScheduler $soonerScheduler;

    /**
     * 计时任务调度程序
     *
     * @var TimerScheduler
     */
    private TimerScheduler $timerScheduler;

    /**
     * 计时任务调度程序
     *
     * @var CrontabScheduler
     */
    private CrontabScheduler $crontabScheduler;

    /**
     * 信号处理程序
     *
     * @var SignalHandler
     */
    private SignalHandler $signalHandler;

    /**
     * 信号是否可用
     *
     * @var bool
     */
    private bool $signalAvailable;

    /**
     * 侦听读事件流列表
     *
     * @var resource[]
     */
    private array $readStreams = [];

    /**
     * 侦听写事件流列表
     *
     * @var resource[]
     */
    private array $writeStreams = [];

    /**
     * 读事件流侦听器列表
     *
     * @var callable[]
     */
    private array $readListeners = [];

    /**
     * 写事件流侦听器列表
     *
     * @var callable[]
     */
    private array $writeListeners = [];

    /**
     * 是否处于循环状态
     *
     * @var bool
     */
    private bool $looping = false;

    /**
     * 初始化处理程序
     */
    public function __construct()
    {
        $this->initialize();

        $this->signalAvailable = function_exists('pcntl_signal') && function_exists('pcntl_signal_dispatch');

        if ($this->signalAvailable && function_exists('pcntl_async_signals')) {
            pcntl_async_signals(true);
        }
    }

    /**
     * @inheritDoc
     */
    public function initialize(): void
    {
        $this->soonerScheduler = new SoonerScheduler();
        $this->timerScheduler = new TimerScheduler();
        $this->crontabScheduler = new CrontabScheduler();
        $this->signalHandler = new SignalHandler();
    }

    /**
     * @inheritDoc
     */
    public function addSooner(callable $listener): void
    {
        $this->soonerScheduler->add($listener);
    }

    /**
     * @inheritDoc
     */
    public function setRead($stream, callable $listener): bool
    {
        $key = (int)$stream;
        $this->readStreams[$key] = $stream;
        $this->readListeners[$key] = $listener;
        return true;
    }

    /**
     * @inheritDoc
     */
    public function setWrite($stream, callable $listener): bool
    {
        $key = (int)$stream;
        $this->writeStreams[$key] = $stream;
        $this->writeListeners[$key] = $listener;
        return true;
    }

    /**
     * @inheritDoc
     */
    public function setSignal(int $signal, callable $listener): bool
    {
        if ($this->signalAvailable) {
            $has = $this->signalHandler->has($signal);
            $this->signalHandler->set($signal, $listener);
            return $has ?: pcntl_signal($signal, [$this->signalHandler, 'call']);
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function addTimer(float $interval, callable $listener, bool $periodic = false): Timer
    {
        $timer = new Timer($interval, $listener, $periodic);
        $this->timerScheduler->add($timer);
        return $timer;
    }

    /**
     * @inheritDoc
     */
    public function addCrontab(callable $listener, string ...$timeRules): Crontab
    {
        $crontab = new Crontab($listener, ...$timeRules);
        $this->crontabScheduler->add($crontab);
        return $crontab;
    }

    /**
     * @inheritDoc
     */
    public function delRead($stream): bool
    {
        $key = (int)$stream;
        unset($this->readStreams[$key], $this->readListeners[$key]);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function delWrite($stream): bool
    {
        $key = (int)$stream;
        unset($this->writeStreams[$key], $this->writeListeners[$key]);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function delSignal(int $signal): bool
    {
        if ($this->signalAvailable) {
            if ($this->signalHandler->has($signal)) {
                $this->signalHandler->del($signal);
                return pcntl_signal($signal, SIG_DFL);
            }
            return true;
        }
        return false;
    }

    /**
     * @inheritDoc
     */
    public function delTimer(Timer $timer): bool
    {
        $this->timerScheduler->del($timer);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function delCrontab(Crontab $crontab): bool
    {
        $this->crontabScheduler->del($crontab);
        return true;
    }

    /**
     * @inheritDoc
     */
    public function loop(): void
    {
        $this->looping = true;

        while ($this->looping) {
            $this->soonerScheduler->tick();
            $this->timerScheduler->tick();
            $this->crontabScheduler->tick();

            // 上述时间性任务整体走完流程后，若循环状态异常，则立即处理流和信号事件
            if (!$this->looping) {
                $timeout = 0;
            } else {
                // 默认流和信号事件监听等待时间不限制（阻塞等待）
                $timeout = null;

                // 有计时器计算触发剩余时间，缩短流和信号事件监听等待时间
                if (null !== $howLong = $this->timerScheduler->howLong()) {
                    $howLong *= 1000000;
                    $timeout = $howLong > PHP_INT_MAX ? PHP_INT_MAX : (int)$howLong;
                }

                // 有定时任务检测剩余时间，缩短流和信号事件监听等待时间
                if (null !== $howLong = $this->crontabScheduler->howLong()) {
                    $howLong *= 1000000;
                    $timeout = $timeout ?? PHP_INT_MAX;
                    $timeout = $howLong > $timeout ? $timeout : (int)$howLong;
                }

                // 没有计时器和定时任务，也没有读写流、信号，直接跳出循环（避免空闲死循环）
                if ($timeout === null && !$this->readStreams && !$this->writeStreams && $this->signalHandler->isEmpty()) {
                    break;
                }
            }

            $this->listenToStreamOrSignal($timeout);
        }
    }

    /**
     * @inheritDoc
     */
    public function destroy(): void
    {
        $this->looping = false;
    }

    /**
     * 监听流事件或信号
     *
     * @param int|null $timeout
     */
    private function listenToStreamOrSignal(?int $timeout)
    {
        $read = $this->readStreams;
        $write = $this->writeStreams;

        if ($read || $write) {
            $changed = $this->streamSelect($read, $write, $timeout);
        } else {
            // 存在计时器或定时任务，睡眠到触发时间
            if ($timeout > 0) {
                usleep($timeout);
            } // 仅有信号侦听，长眠
            elseif ($timeout === null) {
                sleep(PHP_INT_MAX);
            }
        }

        if ($this->signalAvailable) {
            pcntl_signal_dispatch();
        }

        if (!empty($changed)) {
            foreach ($read as $stream) {
                $key = (int)$stream;
                if (isset($this->readListeners[$key])) {
                    $this->readListeners[$key]($stream);
                }
            }

            foreach ($write as $stream) {
                $key = (int)$stream;
                if (isset($this->writeListeners[$key])) {
                    $this->writeListeners[$key]($stream);
                }
            }
        }
    }

    /**
     * 等待流事件，返回修改后数组包含资源流数量，被信号打断返回 false
     *
     * @param array $read
     * @param array $write
     * @param int|null $timeout
     * @return int|false
     */
    private function streamSelect(array &$read, array &$write, ?int $timeout): int|false
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $except = [];
            foreach ($write as $key => $socket) {
                if (!isset($read[$key]) && @ftell($socket) === 0) {
                    $except[$key] = $socket;
                }
            }
        } else {
            $except = null;
        }

        // 等待流事件，屏蔽被信号中断时的警告
        $ret = @stream_select($read, $write, $except, $timeout === null ? null : 0, (int)$timeout);

        if ($except) {
            $write = array_merge($write, $except);
        }

        return $ret;
    }
}
