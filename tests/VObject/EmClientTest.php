<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Property\ICalendar\DateTime;

class EmClientTest extends TestCase
{
    public function testParseTz(): void
    {
        $str = 'BEGIN:VCALENDAR
X-WR-CALNAME:Blackhawks Schedule 2011-12
X-APPLE-CALENDAR-COLOR:#E51717
X-WR-TIMEZONE:America/Chicago
CALSCALE:GREGORIAN
PRODID:-//eM Client/4.0.13961.0
VERSION:2.0
BEGIN:VTIMEZONE
TZID:America/Chicago
BEGIN:DAYLIGHT
TZOFFSETFROM:-0600
RRULE:FREQ=YEARLY;BYDAY=2SU;BYMONTH=3
DTSTART:20070311T020000
TZNAME:CDT
TZOFFSETTO:-0500
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:-0500
RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=11
DTSTART:20071104T020000
TZNAME:CST
TZOFFSETTO:-0600
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
CREATED:20110624T181236Z
UID:be3bbfff-96e8-4c66-9908-ab791a62231d
DTEND;TZID="America/Chicago":20111008T223000
TRANSP:OPAQUE
SUMMARY:Stars @ Blackhawks (Home Opener)
DTSTART;TZID="America/Chicago":20111008T193000
DTSTAMP:20120330T013232Z
SEQUENCE:2
X-MICROSOFT-CDO-BUSYSTATUS:BUSY
LAST-MODIFIED:20120330T013237Z
CLASS:PUBLIC
END:VEVENT
END:VCALENDAR';

        /** @var VCalendar<int, mixed> $vObject */
        $vObject = Reader::read($str);
        /** @var VEvent<int, mixed> $event */
        $event = $vObject->VEVENT;
        /** @var DateTime<int, mixed> $dateTime */
        $dateTime = $event->DTSTART;
        $dt = $dateTime->getDateTime();
        self::assertEquals(new \DateTimeImmutable('2011-10-08 19:30:00', new \DateTimeZone('America/Chicago')), $dt);
    }
}
