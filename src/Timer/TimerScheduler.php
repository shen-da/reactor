<?php

declare(strict_types=1);

namespace Loner\Reactor\Timer;

/**
 * 计时任务调度器
 *
 * @package Loner\Reactor\Timer
 */
class TimerScheduler
{
    /**
     * 记录时间
     *
     * @var float
     */
    private float $time;

    /**
     * 计时器列表
     *
     * @var Timer[]
     */
    private array $timers = [];

    /**
     * 时间表
     *
     * @var float[]
     */
    private array $schedule = [];

    /**
     * 时间排序
     *
     * @var bool
     */
    private bool $sorted = true;

    /**
     * 更新记录时间并返回
     *
     * @return float
     */
    public function updateTime(): float
    {
        return $this->time = hrtime(true) * 1e-9;
    }

    /**
     * 获取记录时间
     *
     * @return float
     */
    public function getTime(): float
    {
        return $this->time ?? $this->updateTime();
    }

    /**
     * 添加计时器
     *
     * @param Timer $timer
     */
    public function add(Timer $timer): void
    {
        $id = spl_object_hash($timer);
        $this->timers[$id] = $timer;
        $this->schedule[$id] = $timer->getInterval() + $this->updateTime();
        $this->sorted = false;
    }

    /**
     * 返回计时器是否被包含
     *
     * @param Timer $timer
     * @return bool
     */
    public function contains(Timer $timer): bool
    {
        return isset($this->timers[spl_object_hash($timer)]);
    }

    /**
     * 删除计时器
     *
     * @param Timer $timer
     */
    public function del(Timer $timer): void
    {
        $id = spl_object_hash($timer);
        unset($this->timers[$id], $this->schedule[$id]);
    }

    /**
     * 获取下次触发剩余时间
     *
     * @return float|null
     */
    public function howLong(): ?float
    {
        $this->sort();
        if (false === $atTime = reset($this->schedule)) {
            return null;
        }

        return max($atTime - $this->getTime(), 0);
    }

    /**
     * 返回计时器列表是否为空
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->timers);
    }

    /**
     * 依次执行触发的计时器回调
     */
    public function tick(): void
    {
        $this->sort();

        $time = $this->updateTime();

        foreach ($this->schedule as $id => $scheduled) {
            // 日程表已排序，循环到未到计划时间日程，则退出
            if ($scheduled > $time) {
                break;
            }

            // 计时器可能被删，或调整计划时间，则跳过
            if (!isset($this->schedule[$id]) || $this->schedule[$id] !== $scheduled) {
                continue;
            }

            // 计时器侦听回调
            $timer = $this->timers[$id];
            $timer->getListener()($timer);

            // 周期性计时器，且未被删除，则重新安排时间；否则删除
            if ($timer->isPeriodic() && isset($this->timers[$id])) {
                $this->schedule[$id] = $timer->getInterval() + $time;
                $this->sorted = false;
            } else {
                unset($this->timers[$id], $this->schedule[$id]);
            }
        }
    }

    /**
     * 时间表排序
     */
    private function sort(): void
    {
        if (!$this->sorted) {
            $this->sorted = true;
            asort($this->schedule);
        }
    }
}
