<?php

namespace Sabre\VObject\Recur\EventIterator;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

/**
 * This is a unit test for Issue #53.
 */
class MultipleRDateRRuleTest extends TestCase
{
    public function testExpand(): void
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:2CD5887F7CF4600F7A3B1F8065099E40-240BDA7121B61224
DTSTAMP;VALUE=DATE-TIME:20151014T110604Z
CREATED;VALUE=DATE-TIME:20151014T110245Z
LAST-MODIFIED;VALUE=DATE-TIME:20151014T110541Z
DTSTART;VALUE=DATE-TIME;TZID=Europe/Berlin:20151025T020000
DTEND;VALUE=DATE-TIME;TZID=Europe/Berlin:20151025T013000
SUMMARY:Test
SEQUENCE:2
RRULE:FREQ=DAILY;UNTIL=20151027T225959Z;INTERVAL=1
RDATE;VALUE=DATE-TIME;TZID=Europe/Berlin:20151018T020000,20151020T020000
RDATE;VALUE=DATE-TIME;TZID=Europe/Berlin:20151015T020000,20151017T020000
TRANSP:OPAQUE
CLASS:PUBLIC
END:VEVENT
END:VCALENDAR
ICS;

        $vcal = Reader::read($input);
        self::assertInstanceOf(VCalendar::class, $vcal);

        $vcal = $vcal->expand(new \DateTime('2015-01-01'), new \DateTime('2015-12-01'));

        $result = iterator_to_array($vcal->VEVENT);

        $utc = new \DateTimeZone('UTC');
        $expected = [
            new \DateTimeImmutable('2015-10-15', $utc),
            new \DateTimeImmutable('2015-10-17', $utc),
            new \DateTimeImmutable('2015-10-18', $utc),
            new \DateTimeImmutable('2015-10-20', $utc),
            new \DateTimeImmutable('2015-10-25T01:00:00.000000+0000', $utc),
            new \DateTimeImmutable('2015-10-26T01:00:00.000000+0000', $utc),
            new \DateTimeImmutable('2015-10-27T01:00:00.000000+0000', $utc),
        ];

        $result = array_map(function ($ev) {return $ev->DTSTART->getDateTime(); }, $result);

        self::assertCount(7, $result);

        self::assertEquals($expected, $result);
    }
}
