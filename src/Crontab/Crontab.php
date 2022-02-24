<?php

declare(strict_types=1);

namespace Loner\Reactor\Crontab;

use Loner\Reactor\Exception\CrontabFormatException;

/**
 * 定时任务
 *
 * @package Loner\Reactor\Crontab
 */
class Crontab
{
    /**
     * 时间规则各部分取值范围
     *
     * @var int[][]
     */
    private static array $limits = [
        [0, 59],
        [0, 23],
        [1, 31],
        [1, 12],
        [0, 6],
    ];

    /**
     * 侦听回调
     *
     * @var callable
     */
    private $listener;

    /**
     * 时间表
     *
     * @var array
     */
    private array $timetable;

    /**
     * 上次行动时间格式
     *
     * @var array
     */
    private array $actioned = [];

    /**
     * 是否处于行动中
     *
     * @var bool
     */
    private bool $actioning = false;

    /**
     * 格式化给定（或当前）时间
     *
     * @param int|null $time
     * @return array
     */
    public static function getFormat(int $time = null): array
    {
        return explode(' ', date('i G j n w', $time ?? time()));
    }

    /**
     * 获取时间指令规则
     *
     * @param string ...$times
     * @return array
     * @throws CrontabFormatException
     */
    public static function timetable(string ...$times): array
    {
        $tables = [];
        foreach (self::$limits as $i => $limit) {
            [$min, $max] = $limit;
            $tables[] = self::timeSets($times[$i] ?? '*', $min, $max);
        }
        return $tables;
    }

    /**
     * 解析有效时间集合
     *
     * @param string $part
     * @param int $min
     * @param int $max
     * @return array|null
     * @throws CrontabFormatException
     */
    public static function timeSets(string $part, int $min = 0, int $max = 59): ?array
    {
        if ($part === '*') {
            return null;
        }

        if (str_contains($part, ',')) {
            $hasNull = false;
            $sets = [];
            foreach (explode(',', $part) as $part) {
                if (null === $set = self::timeSet($part, $min, $max)) {
                    $hasNull = true;
                } else {
                    $sets += $set;
                }
            }
            return $hasNull || count($sets) === $max - $min + 1 ? null : $sets;
        } else {
            return self::timeSet($part, $min, $max);
        }
    }

    /**
     * 解析有效时间集合
     *
     * @param string $part
     * @param int $min
     * @param int $max
     * @return array|null
     * @throws CrontabFormatException
     */
    private static function timeSet(string $part, int $min = 0, int $max = 59): ?array
    {
        if ($part === '*' || $part === '*/1') {
            return null;
        }

        if (preg_match('#^\*/([2-9]|[1-9]\d*)$#', $part, $match)) {
            $start = $min;
            $end = $max;
            $step = $match[1];
        } elseif (preg_match('#^(\d{1,2})(?:-(\d{1,2}))?(?:/([1-9]\d*))?$#', $part, $match, PREG_UNMATCHED_AS_NULL)) {
            $start = (int)$match[1];
            $end = (int)$match[2];
            $step = $match[3] ?? 1;
        } else {
            throw new CrontabFormatException('', CrontabFormatException::FORMAT_ERROR);
        }

        if ($start < $min || $end > $max || $step > $max) {
            throw new CrontabFormatException('', CrontabFormatException::OUT_OF_RANGE);
        }

        if ($step === 1 && $start === $min && $end === $max) {
            return null;
        }

        $set = [];
        for ($i = $start; $i <= $end; $i += $step) {
            $set[$i] = 1;
        }
        return $set;
    }

    /**
     * 初始化任务时间表
     *
     * @param callable $listener
     * @param string ...$timeRules
     * @throws CrontabFormatException
     */
    public function __construct(callable $listener, string ...$timeRules)
    {
        $this->listener = $listener;
        $this->timetable = self::timetable(...$timeRules);
    }

    /**
     * 闹钟走时（处于时间表则执行任务）
     *
     * @param array|null $format
     */
    public function tick(?array $format = null): void
    {
        if ($this->inTimetable($format ?? self::getFormat())) {
            $this->action($format);
        }
    }

    /**
     * 验证：指定格式时间是否处于时间表
     *
     * @param int[] $format
     * @return bool
     */
    private function inTimetable(array $format): bool
    {
        if ($this->actioning || $this->actioned === $format) {
            return false;
        }

        foreach ($this->timetable as $index => $rule) {
            if ($rule === null) {
                continue;
            }

            if (!isset($format[$index]) || !isset($rule[$format[$index]])) {
                return false;
            }
        }

        return true;
    }

    /**
     * 执行任务
     *
     * @param array $format
     */
    private function action(array $format): void
    {
        $this->actioning = true;
        ($this->listener)($this);
        $this->actioned = $format;
        $this->actioning = false;
    }
}
