<?php

namespace Sabre\VObject;

class FreeBusyGeneratorTest extends TestCase {

    function testGeneratorBaseObject() {

        $obj = new Component\VCalendar();
        $obj->METHOD = 'PUBLISH';

        $gen = new FreeBusyGenerator();
        $gen->setObjects([]);
        $gen->setBaseObject($obj);

        $result = $gen->getResult();

        $this->assertEquals('PUBLISH', $result->METHOD->getValue());

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testInvalidArg() {

        $gen = new FreeBusyGenerator(
            new \DateTime('2012-01-01'),
            new \DateTime('2012-12-31'),
            new \StdClass()
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
     * @param string $expected
     * @param array $input
     * @param string|null $timeZone
     * @param string $vavailability
     * @return void
     */
    function assertFreeBusyReport($expected, $input, $timeZone = null, $vavailability = null) {

        $gen = new FreeBusyGenerator(
            new \DateTime('20110101T110000Z', new \DateTimeZone('UTC')),
            new \DateTime('20110103T110000Z', new \DateTimeZone('UTC')),
            $input,
            $timeZone
        );

        if ($vavailability) {
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

        $this->assertVObjEquals($expected, $output);

    }

    function testSimple() {

        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar
DTSTART:20110101T120000Z
DTEND:20110101T130000Z
END:VEVENT
END:VCALENDAR
ICS;


        $this->assertFreeBusyReport(
            "FREEBUSY:20110101T120000Z/20110101T130000Z",
            $blob
        );

    }

    /**
     * Testing TRANSP:OPAQUE
     */
    function testOpaque() {

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

        $this->assertFreeBusyReport(
            "FREEBUSY:20110101T130000Z/20110101T140000Z",
            $blob
        );

    }

    /**
     * Testing TRANSP:TRANSPARENT
     */
    function testTransparent() {

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

        $this->assertFreeBusyReport(
            "",
            $blob
        );

    }

    /**
     * Testing STATUS:CANCELLED
     */
    function testCancelled() {

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

        $this->assertFreeBusyReport(
            "",
            $blob
        );

    }

    /**
     * Testing STATUS:TENTATIVE
     */
    function testTentative() {

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

        $this->assertFreeBusyReport(
            'FREEBUSY;FBTYPE=BUSY-TENTATIVE:20110101T180000Z/20110101T190000Z',
            $blob
        );

    }

    /**
     * Testing an event that falls outside of the report time-range.
     */
    function testOutsideTimeRange() {

        // outside of time-range, hidden
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar6
DTSTART:20110101T090000Z
DTEND:20110101T100000Z
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertFreeBusyReport(
            '',
            $blob
        );

    }

    /**
     * Testing an event that falls outside of the report time-range.
     */
    function testOutsideTimeRange2() {

        // outside of time-range, hidden
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar7
DTSTART:20110104T090000Z
DTEND:20110104T100000Z
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertFreeBusyReport(
            '',
            $blob
        );

    }

    /**
     * Testing an event that uses DURATION
     */
    function testDuration() {

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

        $this->assertFreeBusyReport(
            'FREEBUSY:20110101T190000Z/20110101T200000Z',
            $blob
        );

    }

    /**
     * Testing an all-day event
     */
    function testAllDay() {

        // Day-long event, shows up
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar9
DTSTART;VALUE=DATE:20110102
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertFreeBusyReport(
            'FREEBUSY:20110102T000000Z/20110103T000000Z',
            $blob
        );

    }

    /**
     * Testing an event that has no end or duration.
     */
    function testNoDuration() {

        // No duration, does not show up
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar10
DTSTART:20110101T200000Z
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertFreeBusyReport(
            '',
            $blob
        );

    }

    /**
     * Testing feeding the freebusy generator an object instead of a string.
     */
    function testObject() {

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

        $this->assertFreeBusyReport(
            'FREEBUSY:20110101T210000Z/20110101T220000Z',
            Reader::read($blob)
        );


    }

    /**
     * Testing feeding VFREEBUSY objects instead of VEVENT
     */
    function testVFreeBusy() {

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

        $this->assertFreeBusyReport(
            'FREEBUSY:20110103T010000Z/20110103T020000Z' . "\n" .
                'FREEBUSY:20110103T030000Z/20110103T060000Z',
            $blob
        );

    }

    function testYearlyRecurrence() {

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

        $this->assertFreeBusyReport(
            'FREEBUSY:20110101T220000Z/20110101T230000Z',
            $blob
        );

    }

    function testYearlyRecurrenceDuration() {

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

        $this->assertFreeBusyReport(
            'FREEBUSY:20110101T230000Z/20110102T000000Z',
            $blob
        );

    }

    function testFloatingTime() {

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

        $this->assertFreeBusyReport(
            "FREEBUSY:20110101T120000Z/20110101T130000Z",
            $blob
        );

    }

    function testFloatingTimeReferenceTimeZone() {

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

        $this->assertFreeBusyReport(
            "FREEBUSY:20110101T170000Z/20110101T180000Z",
            $blob,
            new \DateTimeZone('America/Toronto')
        );

    }

    function testAllDay2() {

        // All-day event, slightly outside of the VFREEBUSY range.
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar
DTSTART;VALUE=DATE:20110101
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertFreeBusyReport(
            "FREEBUSY:20110101T110000Z/20110102T000000Z",
            $blob
        );

    }

    function testAllDayReferenceTimeZone() {

        // All-day event + reference timezone
        $blob = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar
DTSTART;VALUE=DATE:20110101
END:VEVENT
END:VCALENDAR
ICS;

        $this->assertFreeBusyReport(
            "FREEBUSY:20110101T110000Z/20110102T050000Z",
            $blob,
            new \DateTimeZone('America/Toronto')
        );

    }

    function testNoValidInstances() {

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

        $this->assertFreeBusyReport(
            "",
            $blob
        );


    }

}
