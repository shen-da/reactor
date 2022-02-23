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
     * 依次执行触发的计时器回调
     */
    public function tick(): void
    {
        $format = Crontab::getFormat();
        foreach ($this->crontab as $crontab) {
            $crontab->tick($format);
        }
    }
}
