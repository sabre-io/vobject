<?php

namespace Sabre\VObject\Recur\EventIterator;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Reader;

class ByMonthInDailyTest extends TestCase
{
    /**
     * This tests the expansion of dates with DAILY frequency in RRULE with BYMONTH restrictions.
     */
    public function testExpand(): void
    {
        $ics = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Apple Inc.//iCal 4.0.4//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
TRANSP:OPAQUE
DTEND:20070925T183000Z
UID:uuid
DTSTAMP:19700101T000000Z
LOCATION:
DESCRIPTION:
STATUS:CONFIRMED
SEQUENCE:18
SUMMARY:Stuff
DTSTART:20070925T160000Z
CREATED:20071004T144642Z
RRULE:FREQ=DAILY;BYMONTH=9,10;BYDAY=SU
END:VEVENT
END:VCALENDAR
ICS;

        /** @var VCalendar<int, mixed> $vcal */
        $vcal = Reader::read($ics);
        self::assertInstanceOf(VCalendar::class, $vcal);

        $vcal = $vcal->expand(new \DateTime('2013-09-28'), new \DateTime('2014-09-11'));

        $dates = [];
        /** @var array<int, VEvent> $events */
        $events = iterator_to_array($vcal->VEVENT);
        foreach ($events as $event) {
            $dates[] = $event->DTSTART->getValue();
        }

        $expectedDates = [
            '20130929T160000Z',
            '20131006T160000Z',
            '20131013T160000Z',
            '20131020T160000Z',
            '20131027T160000Z',
            '20140907T160000Z',
        ];

        self::assertEquals($expectedDates, $dates, 'Recursed dates are restricted by month');
    }
}
