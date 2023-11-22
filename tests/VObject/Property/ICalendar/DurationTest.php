<?php

namespace Sabre\VObject\Property\ICalendar;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;

class DurationTest extends TestCase
{
    public function testGetDateInterval(): void
    {
        $vcal = new VCalendar();
        /** @var VEvent<int, mixed> $event */
        $event = $vcal->add('VEVENT', ['DURATION' => ['PT1H']]);

        self::assertEquals(
            new \DateInterval('PT1H'),
            $event->{'DURATION'}->getDateInterval()
        );
    }
}
