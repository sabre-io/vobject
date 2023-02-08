<?php

namespace Sabre\VObject\ITip;

use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testNoScheduleStatus(): void
    {
        $message = new Message();
        self::assertFalse($message->getScheduleStatus());
    }

    public function testScheduleStatus(): void
    {
        $message = new Message();
        $message->scheduleStatus = '1.2;Delivered';

        self::assertEquals('1.2', $message->getScheduleStatus());
    }

    public function testUnexpectedScheduleStatus(): void
    {
        $message = new Message();
        $message->scheduleStatus = '9.9.9';

        self::assertEquals('9.9.9', $message->getScheduleStatus());
    }
}
