<?php

namespace Sabre\VObject\Recur\EventIterator;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Reader;
use Sabre\VObject\Recur\EventIterator;

class OverrideDurationTest extends TestCase
{
    use \Sabre\VObject\PHPUnitAssertions;

    public function testOverrideDuration(): void
    {
        $ics = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:1
SUMMARY:9-10Uhr
RRULE:FREQ=DAILY
DTSTART;TZID=Europe/Berlin:20210517T090000
DTEND;TZID=Europe/Berlin:20210517T100000
END:VEVENT
BEGIN:VEVENT
UID:2
SUMMARY:9-12Uhr
DTSTART;TZID=Europe/Berlin:20210519T090000
DTEND;TZID=Europe/Berlin:20210519T120000
RECURRENCE-ID;TZID=Europe/Berlin:20210519T090000
END:VEVENT
END:VCALENDAR
ICS;

        $vCalendar = Reader::read($ics);
        $eventIterator = new EventIterator($vCalendar->getComponents());

        $this->assertEquals('2021-05-17 09:00:00', $eventIterator->current()->format('Y-m-d H:i:s'), 'recur event start time');
        $this->assertEquals('2021-05-17 10:00:00', $eventIterator->getDtEnd()->format('Y-m-d H:i:s'), 'recur event end time');

        $eventIterator->next();
        $this->assertEquals('2021-05-18 09:00:00', $eventIterator->current()->format('Y-m-d H:i:s'), 'recur event start time');
        $this->assertEquals('2021-05-18 10:00:00', $eventIterator->getDtEnd()->format('Y-m-d H:i:s'), 'recur event end time');

        $eventIterator->next();
        $this->assertEquals('2021-05-19 09:00:00', $eventIterator->current()->format('Y-m-d H:i:s'), 'overridden event start time');
        $this->assertEquals('2021-05-19 12:00:00', $eventIterator->getDtEnd()->format('Y-m-d H:i:s'), 'overridden event end time');

        $eventIterator->next();
        $this->assertEquals('2021-05-20 09:00:00', $eventIterator->current()->format('Y-m-d H:i:s'), 'recur event start time');
        $this->assertEquals('2021-05-20 10:00:00', $eventIterator->getDtEnd()->format('Y-m-d H:i:s'), 'recur event end time');
    }
}
