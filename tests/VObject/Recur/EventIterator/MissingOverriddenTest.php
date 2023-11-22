<?php

namespace Sabre\VObject\Recur\EventIterator;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

class MissingOverriddenTest extends TestCase
{
    use \Sabre\VObject\PHPUnitAssertions;

    public function testExpand(): void
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foo
DTSTART:20130727T120000Z
DURATION:PT1H
RRULE:FREQ=DAILY;COUNT=2
SUMMARY:A
END:VEVENT
BEGIN:VEVENT
RECURRENCE-ID:20130728T120000Z
UID:foo
DTSTART:20140101T120000Z
DURATION:PT1H
SUMMARY:B
END:VEVENT
END:VCALENDAR
ICS;

        /** @var VCalendar<int, mixed> $vcal */
        $vcal = Reader::read($input);
        self::assertInstanceOf(VCalendar::class, $vcal);

        $vcal = $vcal->expand(new \DateTime('2011-01-01'), new \DateTime('2015-01-01'));

        $output = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foo
DTSTART:20130727T120000Z
DURATION:PT1H
SUMMARY:A
RECURRENCE-ID:20130727T120000Z
END:VEVENT
BEGIN:VEVENT
RECURRENCE-ID:20130728T120000Z
UID:foo
DTSTART:20140101T120000Z
DURATION:PT1H
SUMMARY:B
END:VEVENT
END:VCALENDAR
ICS;
        self::assertVObjectEqualsVObject($output, $vcal);
    }
}
