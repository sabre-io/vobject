<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Property\FlatText;

class FreeBusyGeneratorTest extends TestCase
{
    use PHPUnitAssertions;

    public function testGeneratorBaseObject(): void
    {
        $obj = new Component\VCalendar();
        /** @var FlatText<mixed, mixed> $property */
        $property = $obj->createProperty('METHOD');
        $property->setValue('PUBLISH');
        $obj->METHOD = $property;

        $gen = new FreeBusyGenerator();
        $gen->setObjects([]);
        $gen->setBaseObject($obj);

        /** @var Component\VCalendar<int, mixed> $result */
        $result = $gen->getResult();

        self::assertEquals('PUBLISH', $result->METHOD->getValue());
    }

    public function testInvalidArg(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new FreeBusyGenerator(
            new \DateTime('2012-01-01'),
            new \DateTime('2012-12-31'),
            new \stdClass()
        );
    }

    /**
     * This function takes a list of objects (icalendar objects), and turns
     * them into a freebusy report.
     *
     * Then it takes the expected output and compares it to what we actually
     * got.
     *
     * It only generates the freebusy report for the following time-range:
     * 2011-01-01 11:00:00 until 2011-01-03 11:11:11
     *
     * @param array<int, mixed>|string|resource|Document<int, mixed> $input
     *
     * @throws ParseException
     */
    public function assertFreeBusyReport(string $expected, $input, \DateTimeZone $timeZone = null, string $vavailability = null): void
    {
        $gen = new FreeBusyGenerator(
            new \DateTime('20110101T110000Z', new \DateTimeZone('UTC')),
            new \DateTime('20110103T110000Z', new \DateTimeZone('UTC')),
            $input,
            $timeZone
        );

        if ($vavailability) {
            if (is_string($vavailability)) {
                $vavailability = Reader::read($vavailability);
            }
            $gen->setVAvailability($vavailability);
        }

        $output = $gen->getResult();

        // Removing DTSTAMP because it changes every time.
        unset($output->VFREEBUSY->DTSTAMP);

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VFREEBUSY
DTSTART:20110101T110000Z
DTEND:20110103T110000Z
$expected
END:VFREEBUSY
END:VCALENDAR
ICS;

        self::assertVObjectEqualsVObject($expected, $output);
    }

    public function testSimple(): void
    {
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar
DTSTART:20110101T120000Z
DTEND:20110101T130000Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T120000Z/20110101T130000Z',
            $blob
        );
    }

    public function testSource(): void
    {
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar
DTSTART:20110101T120000Z
DTEND:20110101T130000Z
END:VEVENT
END:VCALENDAR
ICS;
        $h = fopen('php://memory', 'r+');
        fwrite($h, $blob);
        rewind($h);

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T120000Z/20110101T130000Z',
            $h
        );
    }

    /**
     * Testing TRANSP:OPAQUE.
     */
    public function testOpaque(): void
    {
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar2
TRANSP:OPAQUE
DTSTART:20110101T130000Z
DTEND:20110101T140000Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T130000Z/20110101T140000Z',
            $blob
        );
    }

    /**
     * Testing TRANSP:TRANSPARENT.
     */
    public function testTransparent(): void
    {
        // transparent, hidden
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar3
TRANSP:TRANSPARENT
DTSTART:20110101T140000Z
DTEND:20110101T150000Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            '',
            $blob
        );
    }

    /**
     * Testing STATUS:CANCELLED.
     */
    public function testCancelled(): void
    {
        // transparent, hidden
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar4
STATUS:CANCELLED
DTSTART:20110101T160000Z
DTEND:20110101T170000Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            '',
            $blob
        );
    }

    /**
     * Testing STATUS:TENTATIVE.
     */
    public function testTentative(): void
    {
        // tentative, shows up
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar5
STATUS:TENTATIVE
DTSTART:20110101T180000Z
DTEND:20110101T190000Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY;FBTYPE=BUSY-TENTATIVE:20110101T180000Z/20110101T190000Z',
            $blob
        );
    }

    /**
     * Testing an event that falls outside the report time-range.
     */
    public function testOutsideTimeRange(): void
    {
        // outside time-range, hidden
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar6
DTSTART:20110101T090000Z
DTEND:20110101T100000Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            '',
            $blob
        );
    }

    /**
     * Testing an event that falls outside the report time-range.
     */
    public function testOutsideTimeRange2(): void
    {
        // outside time-range, hidden
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar7
DTSTART:20110104T090000Z
DTEND:20110104T100000Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            '',
            $blob
        );
    }

    /**
     * Testing an event that uses DURATION.
     */
    public function testDuration(): void
    {
        // using duration, shows up
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar8
DTSTART:20110101T190000Z
DURATION:PT1H
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T190000Z/20110101T200000Z',
            $blob
        );
    }

    /**
     * Testing an all-day event.
     */
    public function testAllDay(): void
    {
        // Day-long event, shows up
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar9
DTSTART;VALUE=DATE:20110102
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110102T000000Z/20110103T000000Z',
            $blob
        );
    }

    /**
     * Testing an event that has no end or duration.
     */
    public function testNoDuration(): void
    {
        // No duration, does not show up
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar10
DTSTART:20110101T200000Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            '',
            $blob
        );
    }

    /**
     * Testing feeding the freebusy generator an object instead of a string.
     */
    public function testObject(): void
    {
        // encoded as object, shows up
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar11
DTSTART:20110101T210000Z
DURATION:PT1H
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T210000Z/20110101T220000Z',
            Reader::read($blob)
        );
    }

    /**
     * Testing feeding VFREEBUSY objects instead of VEVENT.
     */
    public function testVFreeBusy(): void
    {
        // Freebusy. Some parts show up
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VFREEBUSY
FREEBUSY:20110103T010000Z/20110103T020000Z
FREEBUSY;FBTYPE=FREE:20110103T020000Z/20110103T030000Z
FREEBUSY:20110103T030000Z/20110103T040000Z,20110103T040000Z/20110103T050000Z
FREEBUSY:20120101T000000Z/20120101T010000Z
FREEBUSY:20110103T050000Z/PT1H
END:VFREEBUSY
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            "FREEBUSY:20110103T010000Z/20110103T020000Z\n".
            'FREEBUSY:20110103T030000Z/20110103T060000Z',
            $blob
        );
    }

    public function testYearlyRecurrence(): void
    {
        // Yearly recurrence rule, shows up
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar13
DTSTART:20100101T220000Z
DTEND:20100101T230000Z
RRULE:FREQ=YEARLY
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T220000Z/20110101T230000Z',
            $blob
        );
    }

    public function testYearlyRecurrenceDuration(): void
    {
        // Yearly recurrence rule + duration, shows up
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar14
DTSTART:20100101T230000Z
DURATION:PT1H
RRULE:FREQ=YEARLY
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T230000Z/20110102T000000Z',
            $blob
        );
    }

    public function testFloatingTime(): void
    {
        // Floating time, no timezone
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar
DTSTART:20110101T120000
DTEND:20110101T130000
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T120000Z/20110101T130000Z',
            $blob
        );
    }

    public function testFloatingTimeReferenceTimeZone(): void
    {
        // Floating time + reference timezone
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar
DTSTART:20110101T120000
DTEND:20110101T130000
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T170000Z/20110101T180000Z',
            $blob,
            new \DateTimeZone('America/Toronto')
        );
    }

    public function testAllDay2(): void
    {
        // All-day event, slightly outside the VFREEBUSY range.
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar
DTSTART;VALUE=DATE:20110101
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T110000Z/20110102T000000Z',
            $blob
        );
    }

    public function testAllDayReferenceTimeZone(): void
    {
        // All-day event + reference timezone
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar
DTSTART;VALUE=DATE:20110101
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T110000Z/20110102T050000Z',
            $blob,
            new \DateTimeZone('America/Toronto')
        );
    }

    public function testNoValidInstances(): void
    {
        // Recurrence rule with no valid instances
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar
DTSTART:20110101T100000Z
DTEND:20110103T120000Z
RRULE:FREQ=WEEKLY;COUNT=1
EXDATE:20110101T100000Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            '',
            $blob
        );
    }

    /**
     * This VAVAILABILITY object overlaps with the time-range, but we're just
     * busy the entire time.
     */
    public function testVAvailabilitySimple(): void
    {
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:lalala
DTSTART:20110101T120000Z
DTEND:20110101T130000Z
END:VEVENT
END:VCALENDAR
ICS;

        $vavail = <<<ICS
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
DTSTART:20110101T000000Z
DTEND:20120101T000000Z
BEGIN:AVAILABLE
DTSTART:20110101T000000Z
DTEND:20110101T010000Z
END:AVAILABLE
END:VAVAILABILITY
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            "FREEBUSY;FBTYPE=BUSY-UNAVAILABLE:20110101T110000Z/20110101T120000Z\n".
            "FREEBUSY:20110101T120000Z/20110101T130000Z\n".
            'FREEBUSY;FBTYPE=BUSY-UNAVAILABLE:20110101T130000Z/20110103T110000Z',
            $blob,
            null,
            $vavail
        );
    }

    /**
     * This VAVAILABILITY object does not overlap at all with the freebusy
     * report, so it should be ignored.
     */
    public function testVAvailabilityIrrelevant(): void
    {
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:lalala
DTSTART:20110101T120000Z
DTEND:20110101T130000Z
END:VEVENT
END:VCALENDAR
ICS;

        $vavail = <<<ICS
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
DTSTART:20150101T000000Z
DTEND:20160101T000000Z
BEGIN:AVAILABLE
DTSTART:20150101T000000Z
DTEND:20150101T010000Z
END:AVAILABLE
END:VAVAILABILITY
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T120000Z/20110101T130000Z',
            $blob,
            null,
            $vavail
        );
    }

    /**
     * This VAVAILABILITY object has a 9am-5pm AVAILABLE object for office
     * hours.
     */
    public function testVAvailabilityOfficeHours(): void
    {
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:lalala
DTSTART:20110101T120000Z
DTEND:20110101T130000Z
END:VEVENT
END:VCALENDAR
ICS;

        $vavail = <<<ICS
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
DTSTART:20100101T000000Z
DTEND:20120101T000000Z
BUSYTYPE:BUSY-TENTATIVE
BEGIN:AVAILABLE
DTSTART:20101213T090000Z
DTEND:20101213T170000Z
RRULE:FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR
END:AVAILABLE
END:VAVAILABILITY
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            "FREEBUSY;FBTYPE=BUSY-TENTATIVE:20110101T110000Z/20110101T120000Z\n".
            "FREEBUSY:20110101T120000Z/20110101T130000Z\n".
            "FREEBUSY;FBTYPE=BUSY-TENTATIVE:20110101T130000Z/20110103T090000Z\n",
            $blob,
            null,
            $vavail
        );
    }

    /**
     * This test has the same office hours, but has a vacation blocked off for
     * the relevant time, using a higher priority. (lower number).
     */
    public function testVAvailabilityOfficeHoursVacation(): void
    {
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:lalala
DTSTART:20110101T120000Z
DTEND:20110101T130000Z
END:VEVENT
END:VCALENDAR
ICS;

        $vavail = <<<ICS
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
DTSTART:20100101T000000Z
DTEND:20120101T000000Z
BUSYTYPE:BUSY-TENTATIVE
PRIORITY:2
BEGIN:AVAILABLE
DTSTART:20101213T090000Z
DTEND:20101213T170000Z
RRULE:FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR
END:AVAILABLE
END:VAVAILABILITY
BEGIN:VAVAILABILITY
PRIORITY:1
DTSTART:20101214T000000Z
DTEND:20110107T000000Z
BUSYTYPE:BUSY
END:VAVAILABILITY
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            'FREEBUSY:20110101T110000Z/20110103T110000Z',
            $blob,
            null,
            $vavail
        );
    }

    /**
     * This test has the same input as the last, except somebody mixed up the
     * PRIORITY values.
     *
     * The end-result is that the vacation VAVAILABILITY is completely ignored.
     */
    public function testVAvailabilityOfficeHoursVacation2(): void
    {
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:lalala
DTSTART:20110101T120000Z
DTEND:20110101T130000Z
END:VEVENT
END:VCALENDAR
ICS;

        $vavail = <<<ICS
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
DTSTART:20100101T000000Z
DTEND:20120101T000000Z
BUSYTYPE:BUSY-TENTATIVE
PRIORITY:1
BEGIN:AVAILABLE
DTSTART:20101213T090000Z
DTEND:20101213T170000Z
RRULE:FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR
END:AVAILABLE
END:VAVAILABILITY
BEGIN:VAVAILABILITY
PRIORITY:2
DTSTART:20101214T000000Z
DTEND:20110107T000000Z
BUSYTYPE:BUSY
END:VAVAILABILITY
END:VCALENDAR
ICS;

        self::assertFreeBusyReport(
            "FREEBUSY;FBTYPE=BUSY-TENTATIVE:20110101T110000Z/20110101T120000Z\n".
            "FREEBUSY:20110101T120000Z/20110101T130000Z\n".
            "FREEBUSY;FBTYPE=BUSY-TENTATIVE:20110101T130000Z/20110103T090000Z\n",
            $blob,
            null,
            $vavail
        );
    }
}
