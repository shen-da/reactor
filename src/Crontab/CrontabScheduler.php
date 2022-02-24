<?php

declare(strict_types=1);

namespace Loner\Reactor\Crontab;

/**
 * 定时任务调度程序
 *
 * @package Loner\Reactor\Crontab
 */
class CrontabScheduler
{
    /**
     * 上次检测时间
     *
     * @var int
     */
    private int $lastTime = 0;

    /**
     * 定时任务列表
     *
     * @var Crontab[]
     */
    private array $crontab = [];

    /**
     * 添加定时任务
     *
     * @param Crontab $crontab
     */
    public function add(Crontab $crontab): void
    {
        $this->crontab[spl_object_hash($crontab)] = $crontab;
    }

    /**
     * 删除定时任务
     *
     * @param Crontab $crontab
     */
    public function del(Crontab $crontab): void
    {
        unset($this->crontab[spl_object_hash($crontab)]);
    }

    /**
     * 返回定时任务列表是否为空
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->crontab);
    }

    /**
     * 通过检索时间判断，并执行触发的定时任务回调
     */
    public function tick(): void
    {
        $time = time();
        if ($time - $this->lastTime < 60) {
            return;
        }

        $this->lastTime = $time - ($time % 60);

        $format = Crontab::getFormat($time);
        foreach ($this->crontab as $crontab) {
            $crontab->tick($format);
        }
    }

    /**
     * 获取下次检索剩余时间
     *
     * @return int|null
     */
    public function howLong(): ?int
    {
        return $this->isEmpty() ? null : max($this->lastTime + 60 - time(), 0);
    }
}
