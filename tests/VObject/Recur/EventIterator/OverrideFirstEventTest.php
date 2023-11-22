<?php

namespace Sabre\VObject\Recur\EventIterator;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

class OverrideFirstEventTest extends TestCase
{
    use \Sabre\VObject\PHPUnitAssertions;

    public function testOverrideFirstEvent(): void
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
DTSTART:20140803T120000Z
RRULE:FREQ=WEEKLY
SUMMARY:Original
END:VEVENT
BEGIN:VEVENT
UID:foobar
RECURRENCE-ID:20140803T120000Z
DTSTART:20140803T120000Z
SUMMARY:Overridden
END:VEVENT
END:VCALENDAR
ICS;

        /** @var VCalendar<int, mixed> $vcal */
        $vcal = Reader::read($input);
        $vcal = $vcal->expand(new \DateTime('2014-08-01'), new \DateTime('2014-09-01'));

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
RECURRENCE-ID:20140803T120000Z
DTSTART:20140803T120000Z
SUMMARY:Overridden
END:VEVENT
BEGIN:VEVENT
UID:foobar
DTSTART:20140810T120000Z
SUMMARY:Original
RECURRENCE-ID:20140810T120000Z
END:VEVENT
BEGIN:VEVENT
UID:foobar
DTSTART:20140817T120000Z
SUMMARY:Original
RECURRENCE-ID:20140817T120000Z
END:VEVENT
BEGIN:VEVENT
UID:foobar
DTSTART:20140824T120000Z
SUMMARY:Original
RECURRENCE-ID:20140824T120000Z
END:VEVENT
BEGIN:VEVENT
UID:foobar
DTSTART:20140831T120000Z
SUMMARY:Original
RECURRENCE-ID:20140831T120000Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertVObjectEqualsVObject(
            $expected,
            $vcal
        );
    }

    public function testRemoveFirstEvent(): void
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
DTSTART:20140803T120000Z
RRULE:FREQ=WEEKLY
EXDATE:20140803T120000Z
SUMMARY:Original
END:VEVENT
END:VCALENDAR
ICS;

        /** @var VCalendar<int, mixed> $vcal */
        $vcal = Reader::read($input);
        $vcal = $vcal->expand(new \DateTime('2014-08-01'), new \DateTime('2014-08-19'));

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
DTSTART:20140810T120000Z
SUMMARY:Original
RECURRENCE-ID:20140810T120000Z
END:VEVENT
BEGIN:VEVENT
UID:foobar
DTSTART:20140817T120000Z
SUMMARY:Original
RECURRENCE-ID:20140817T120000Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertVObjectEqualsVObject(
            $expected,
            $vcal
        );
    }
}
