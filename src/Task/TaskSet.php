<?php

declare(strict_types=1);

namespace Loner\Reactor\Task;

use SplQueue;

/**
 * 任务集
 *
 * @package Loner\Reactor\Task
 */
class TaskSet
{
    /**
     * 任务队列
     *
     * @var SplQueue
     */
    private SplQueue $queue;

    /**
     * 初始化任务队列
     */
    public function __construct()
    {
        $this->queue = new SplQueue();
    }

    /**
     * 添加任务
     *
     * @param callable $task
     */
    public function add(callable $task): void
    {
        $this->queue->enqueue($task);
    }

    /**
     * 刷新并执行队列回调
     */
    public function tick(): void
    {
        $count = $this->queue->count();

        while ($count--) {
            call_user_func($this->queue->dequeue());
        }
    }

    /**
     * 检测任务队列是否为空
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->queue->isEmpty();
    }
}
