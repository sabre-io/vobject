<?php

namespace Sabre\VObject\Recur\EventIterator;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;
use Sabre\VObject\Recur\MaxInstancesExceededException;
use Sabre\VObject\Settings;

class MaxInstancesTest extends TestCase
{
    public function testExceedMaxRecurrences(): void
    {
        $this->expectException(MaxInstancesExceededException::class);
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
DTSTART:20140803T120000Z
RRULE:FREQ=WEEKLY
SUMMARY:Original
END:VEVENT
END:VCALENDAR
ICS;

        $temp = Settings::$maxRecurrences;
        Settings::$maxRecurrences = 4;
        try {
            /** @var VCalendar<int, mixed> $vcal */
            $vcal = Reader::read($input);
            $vcal->expand(new \DateTime('2014-08-01'), new \DateTime('2014-09-01'));
        } finally {
            Settings::$maxRecurrences = $temp;
        }
    }
}
