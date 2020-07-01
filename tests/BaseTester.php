<?php

use PHPUnit\Framework\TestCase;

/**
 * 测试基类
 *
 * Class BaseTester
 *
 * @internal
 * @coversNothing
 */
class BaseTester extends TestCase
{
    protected function setUp(): void
    {
    }

    /**
     * @param string $msg
     */
    public function outlog($msg)
    {
        $time = date('Y-m-d H:i:s', time());
        echo "{$time} {$msg}" . PHP_EOL;
    }
}
