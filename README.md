## 事件反应器
事件反应器，用于事件的侦听与响应，可处理事件：信号、流读写、定时器。
### 运行环境
- PHP >= 8.0 && PHP < 9.0
### 安装
```
composer require loner/reactor
```
### 应用说明
* 创建实例

    ```php
    <?php
  
    use Loner\Reactor\Builder;
  
    // composer 自加载，路径视具体情况而定
    require __DIR__ . '/vendor/autoload.php';
  
    // 事件反应驱动对象
    $reactor = Builder::create();
* 事件侦听
    * 添加优先任务（下次事件循环优先处理）

        ```php
        $reactor->addSooner(callable $listener): void;
          # 内部回调
          $listener();
        ```
    * 套接字读

        ```php
        # 增/改
        $reactor->setRead(resource $stream, callable $listener): bool;
          # 内部回调
          $listener(resource $stream);
      
        # 删
        $reactor->delRead(resource $stream): bool;
        ```
    * 套接字写

        ```php
        # 增/改
        $reactor->setWrite(resource $stream, callable $listener): bool;
          # 内部回调
          $listener(resource $stream);
      
        # 删
        $reactor->delWrite(resource $stream): bool;
        ```
    * 信号

        ```php
        # 增/改
        $reactor->setSignal(int $signal, callable $listener): bool;
          # 内部回调
          $listener($signal);
      
        # 删
        $reactor->delSignal(int $signal): bool;
        ```
    * 定时器

        ```php
        # 增：秒数、回调、是否周期性
        # Loner\Reactor\Timer\TimerInterface
        $reactor->setTimer(float $interval, callable $listener, bool $periodic = false): TimerInterface;
          # 内部回调
          $listener(TimerInterface $timer);
      
        # 删
        $reactor->delTimer(TimerInterface $timer): bool;
        ```
* 事件轮询

    ```php
    $reactor->loop(): void;
    ```
* 退出事件轮询

    ```php
    $reactor->destroy(): void;
    ```
