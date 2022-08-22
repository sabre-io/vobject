<?php

namespace Sabre\VObject\ITip;

use PHPUnit\Framework\TestCase;

class MessageTest extends TestCase
{
    public function testNoScheduleStatus(): void
    {
        $message = new Message();
        $this->assertFalse($message->getScheduleStatus());
    }

    public function testScheduleStatus(): void
    {
        $message = new Message();
        $message->scheduleStatus = '1.2;Delivered';

        $this->assertEquals('1.2', $message->getScheduleStatus());
    }

    public function testUnexpectedScheduleStatus(): void
    {
        $message = new Message();
        $message->scheduleStatus = '9.9.9';

        $this->assertEquals('9.9.9', $message->getScheduleStatus());
    }
}
