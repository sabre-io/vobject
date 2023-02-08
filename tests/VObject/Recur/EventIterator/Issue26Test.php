<?php

namespace Sabre\VObject\Recur\EventIterator;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\InvalidDataException;
use Sabre\VObject\Reader;
use Sabre\VObject\Recur\EventIterator;

class Issue26Test extends TestCase
{
    public function testExpand(): void
    {
        $this->expectException(InvalidDataException::class);
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:bae5d57a98
RRULE:FREQ=MONTHLY;BYDAY=0MO,0TU,0WE,0TH,0FR;INTERVAL=1
DTSTART;VALUE=DATE:20130401
DTEND;VALUE=DATE:20130402
END:VEVENT
END:VCALENDAR
ICS;

        $vcal = Reader::read($input);
        self::assertInstanceOf(VCalendar::class, $vcal);

        new EventIterator($vcal, 'bae5d57a98');
    }
}
