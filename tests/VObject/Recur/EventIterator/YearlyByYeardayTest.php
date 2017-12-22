<?php

namespace Sabre\VObject\Recur;

use DateTime;
use PHPUnit\Framework\TestCase;
use Sabre\VObject\Reader;

class YearlyByYeardayTest extends TestCase {

    /**
     * This tests the expansion of dates with DAILY frequency in RRULE with BYMONTH restrictions
     */
    function testExpand() {

        $ics = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Apple Inc.//iCal 4.0.4//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
TRANSP:OPAQUE
DTSTART:20070102T160000Z
DTEND:20070102T183000Z
RRULE:FREQ=YEARLY;BYYEARDAY=1
UID:uuid
DTSTAMP:19700101T000000Z
LOCATION:
DESCRIPTION:
STATUS:CONFIRMED
SEQUENCE:18
SUMMARY:Stuff
CREATED:20071004T144642Z
END:VEVENT
END:VCALENDAR
ICS;

        $vcal = Reader::read($ics);
        $this->assertInstanceOf('Sabre\\VObject\\Component\\VCalendar', $vcal);

        $vcal = $vcal->expand(new DateTime('2007-01-01'), new DateTime('2008-01-03'));

        foreach ($vcal->VEVENT as $event) {
            $dates[] = $event->DTSTART->getValue();
        }

        $expectedDates = [
            "20070102T160000Z",
            "20080102T160000Z"
        ];

        $this->assertEquals($expectedDates, $dates, 'Recursed dates are restricted by year day');
    }

}
