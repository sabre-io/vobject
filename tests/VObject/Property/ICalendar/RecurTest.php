<?php

namespace Sabre\VObject\Property\ICalendar;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Node;
use Sabre\VObject\Reader;

class RecurTest extends TestCase
{
    use \Sabre\VObject\PHPUnitAssertions;

    public function testParts(): void
    {
        $vcal = new VCalendar();
        $recur = $vcal->add('RRULE', 'FREQ=Daily');

        self::assertInstanceOf(Recur::class, $recur);

        self::assertEquals(['FREQ' => 'DAILY'], $recur->getParts());
        $recur->setParts(['freq' => 'MONTHLY']);

        self::assertEquals(['FREQ' => 'MONTHLY'], $recur->getParts());
    }

    public function testSetValueBadVal(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $vcal = new VCalendar();
        $recur = $vcal->add('RRULE', 'FREQ=Daily');
        $recur->setValue(new \Exception());
    }

    public function testSetValueWithCount(): void
    {
        $vcal = new VCalendar();
        $recur = $vcal->add('RRULE', 'FREQ=Daily');
        $recur->setValue(['COUNT' => 3]);
        self::assertEquals(3, $recur->getParts()['COUNT']);
    }

    public function testGetJSONWithCount(): void
    {
        $input = 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:908d53c0-e1a3-4883-b69f-530954d6bd62
TRANSP:OPAQUE
DTSTART;TZID=Europe/Berlin:20160301T150000
DTEND;TZID=Europe/Berlin:20160301T170000
SUMMARY:test
RRULE:FREQ=DAILY;COUNT=3
ORGANIZER;CN=robert pipo:mailto:robert@example.org
END:VEVENT
END:VCALENDAR
';

        $vcal = Reader::read($input);
        $rrule = $vcal->VEVENT->RRULE;
        $count = $rrule->getJsonValue()[0]['count'];
        self::assertTrue(is_int($count));
        self::assertEquals(3, $count);
    }

    public function testSetSubParts(): void
    {
        $vcal = new VCalendar();
        $recur = $vcal->add('RRULE', ['FREQ' => 'DAILY', 'BYDAY' => 'mo,tu', 'BYMONTH' => [0, 1]]);

        self::assertEquals([
            'FREQ' => 'DAILY',
            'BYDAY' => ['MO', 'TU'],
            'BYMONTH' => [0, 1],
        ], $recur->getParts());
    }

    public function testGetJSONWithUntil(): void
    {
        $input = 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:908d53c0-e1a3-4883-b69f-530954d6bd62
TRANSP:OPAQUE
DTSTART;TZID=Europe/Berlin:20160301T150000
DTEND;TZID=Europe/Berlin:20160301T170000
SUMMARY:test
RRULE:FREQ=DAILY;UNTIL=20160305T230000Z
ORGANIZER;CN=robert pipo:mailto:robert@example.org
END:VEVENT
END:VCALENDAR
';

        $vcal = Reader::read($input);
        $rrule = $vcal->VEVENT->RRULE;
        $untilJsonString = $rrule->getJsonValue()[0]['until'];
        self::assertEquals('2016-03-05T23:00:00Z', $untilJsonString);
    }

    public function testValidateStripEmpties(): void
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foobar
BEGIN:VEVENT
UID:908d53c0-e1a3-4883-b69f-530954d6bd62
TRANSP:OPAQUE
DTSTART;TZID=Europe/Berlin:20160301T150000
DTEND;TZID=Europe/Berlin:20160301T170000
SUMMARY:test
RRULE:FREQ=DAILY;BYMONTH=;UNTIL=20160305T230000Z
ORGANIZER;CN=robert pipo:mailto:robert@example.org
DTSTAMP:20160312T183800Z
END:VEVENT
END:VCALENDAR
';

        $vcal = Reader::read($input);
        self::assertCount(
            1,
            $vcal->validate()
        );
        self::assertCount(
            1,
            $vcal->validate($vcal::REPAIR)
        );

        $expected = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foobar
BEGIN:VEVENT
UID:908d53c0-e1a3-4883-b69f-530954d6bd62
TRANSP:OPAQUE
DTSTART;TZID=Europe/Berlin:20160301T150000
DTEND;TZID=Europe/Berlin:20160301T170000
SUMMARY:test
RRULE:FREQ=DAILY;UNTIL=20160305T230000Z
ORGANIZER;CN=robert pipo:mailto:robert@example.org
DTSTAMP:20160312T183800Z
END:VEVENT
END:VCALENDAR
';

        self::assertVObjectEqualsVObject(
            $expected,
            $vcal
        );
    }

    public function testValidateStripNoFreq(): void
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foobar
BEGIN:VEVENT
UID:908d53c0-e1a3-4883-b69f-530954d6bd62
TRANSP:OPAQUE
DTSTART;TZID=Europe/Berlin:20160301T150000
DTEND;TZID=Europe/Berlin:20160301T170000
SUMMARY:test
RRULE:UNTIL=20160305T230000Z
ORGANIZER;CN=robert pipo:mailto:robert@example.org
DTSTAMP:20160312T183800Z
END:VEVENT
END:VCALENDAR
';

        $vcal = Reader::read($input);
        self::assertCount(
            1,
            $vcal->validate()
        );
        self::assertCount(
            1,
            $vcal->validate($vcal::REPAIR)
        );

        $expected = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foobar
BEGIN:VEVENT
UID:908d53c0-e1a3-4883-b69f-530954d6bd62
TRANSP:OPAQUE
DTSTART;TZID=Europe/Berlin:20160301T150000
DTEND;TZID=Europe/Berlin:20160301T170000
SUMMARY:test
ORGANIZER;CN=robert pipo:mailto:robert@example.org
DTSTAMP:20160312T183800Z
END:VEVENT
END:VCALENDAR
';

        self::assertVObjectEqualsVObject(
            $expected,
            $vcal
        );
    }

    public function testValidateInvalidByMonthRruleWithRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=0');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(1, $result);
        self::assertEquals('BYMONTH in RRULE must have value(s) between 1 and 12!', $result[0]['message']);
        self::assertEquals(1, $result[0]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYMONTHDAY=24', $property->getValue());
    }

    public function testValidateInvalidByMonthRruleWithoutRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=0');
        $result = $property->validate();

        self::assertCount(1, $result);
        self::assertEquals('BYMONTH in RRULE must have value(s) between 1 and 12!', $result[0]['message']);
        self::assertEquals(3, $result[0]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=0', $property->getValue());
    }

    public function testValidateInvalidByMonthRruleWithRepair2(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=bla');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(1, $result);
        self::assertEquals('BYMONTH in RRULE must have value(s) between 1 and 12!', $result[0]['message']);
        self::assertEquals(1, $result[0]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYMONTHDAY=24', $property->getValue());
    }

    public function testValidateInvalidByMonthRruleWithoutRepair2(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=bla');
        $result = $property->validate();

        self::assertCount(1, $result);
        self::assertEquals('BYMONTH in RRULE must have value(s) between 1 and 12!', $result[0]['message']);
        self::assertEquals(3, $result[0]['level']);
        // Without repair the invalid BYMONTH is still there, but the value is changed to uppercase
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=BLA', $property->getValue());
    }

    public function testValidateInvalidByMonthRruleValue14WithRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=14');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(1, $result);
        self::assertEquals('BYMONTH in RRULE must have value(s) between 1 and 12!', $result[0]['message']);
        self::assertEquals(1, $result[0]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYMONTHDAY=24', $property->getValue());
    }

    public function testValidateInvalidByMonthRruleMultipleWithRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=0,1,2,3,4,14');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(2, $result);
        self::assertEquals('BYMONTH in RRULE must have value(s) between 1 and 12!', $result[0]['message']);
        self::assertEquals(1, $result[0]['level']);
        self::assertEquals('BYMONTH in RRULE must have value(s) between 1 and 12!', $result[1]['message']);
        self::assertEquals(1, $result[1]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=1,2,3,4', $property->getValue());
    }

    public function testValidateOneOfManyInvalidByMonthRruleWithRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=bla,3,foo');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(2, $result);
        self::assertEquals('BYMONTH in RRULE must have value(s) between 1 and 12!', $result[0]['message']);
        self::assertEquals(1, $result[0]['level']);
        self::assertEquals('BYMONTH in RRULE must have value(s) between 1 and 12!', $result[1]['message']);
        self::assertEquals(1, $result[1]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=3', $property->getValue());
    }

    public function testValidateValidByMonthRrule(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=2,3');
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=2,3', $property->getValue());
    }

    /**
     * test for issue #336.
     */
    public function testValidateRruleBySecondZero(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=DAILY;BYHOUR=10;BYMINUTE=30;BYSECOND=0;UNTIL=20150616T153000Z');
        $result = $property->validate(Node::REPAIR);

        // There should be 0 warnings and the value should be unchanged
        self::assertEmpty($result);
        self::assertEquals('FREQ=DAILY;BYHOUR=10;BYMINUTE=30;BYSECOND=0;UNTIL=20150616T153000Z', $property->getValue());
    }

    public function testValidateValidByWeekNoWithRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYWEEKNO=11');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(0, $result);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYWEEKNO=11', $property->getValue());
    }

    public function testValidateInvalidByWeekNoWithRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYWEEKNO=55;BYDAY=WE');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(1, $result);
        self::assertEquals('BYWEEKNO in RRULE must have value(s) from -53 to -1, or 1 to 53!', $result[0]['message']);
        self::assertEquals(1, $result[0]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYDAY=WE', $property->getValue());
    }

    public function testValidateMultipleInvalidByWeekNoWithRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYWEEKNO=55,2,-80;BYDAY=WE');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(2, $result);
        self::assertEquals('BYWEEKNO in RRULE must have value(s) from -53 to -1, or 1 to 53!', $result[0]['message']);
        self::assertEquals(1, $result[0]['level']);
        self::assertEquals('BYWEEKNO in RRULE must have value(s) from -53 to -1, or 1 to 53!', $result[1]['message']);
        self::assertEquals(1, $result[1]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYWEEKNO=2;BYDAY=WE', $property->getValue());
    }

    public function testValidateAllInvalidByWeekNoWithRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYWEEKNO=55,-80;BYDAY=WE');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(2, $result);
        self::assertEquals('BYWEEKNO in RRULE must have value(s) from -53 to -1, or 1 to 53!', $result[0]['message']);
        self::assertEquals(1, $result[0]['level']);
        self::assertEquals('BYWEEKNO in RRULE must have value(s) from -53 to -1, or 1 to 53!', $result[1]['message']);
        self::assertEquals(1, $result[1]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYDAY=WE', $property->getValue());
    }

    public function testValidateInvalidByWeekNoWithoutRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYWEEKNO=55;BYDAY=WE');
        $result = $property->validate();

        self::assertCount(1, $result);
        self::assertEquals('BYWEEKNO in RRULE must have value(s) from -53 to -1, or 1 to 53!', $result[0]['message']);
        self::assertEquals(3, $result[0]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYWEEKNO=55;BYDAY=WE', $property->getValue());
    }

    public function testValidateValidByYearDayWithRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYYEARDAY=119');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(0, $result);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYYEARDAY=119', $property->getValue());
    }

    public function testValidateInvalidByYearDayWithRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYYEARDAY=367;BYDAY=WE');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(1, $result);
        self::assertEquals('BYYEARDAY in RRULE must have value(s) from -366 to -1, or 1 to 366!', $result[0]['message']);
        self::assertEquals(1, $result[0]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYDAY=WE', $property->getValue());
    }

    public function testValidateMultipleInvalidByYearDayWithRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYYEARDAY=380,2,-390;BYDAY=WE');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(2, $result);
        self::assertEquals('BYYEARDAY in RRULE must have value(s) from -366 to -1, or 1 to 366!', $result[0]['message']);
        self::assertEquals(1, $result[0]['level']);
        self::assertEquals('BYYEARDAY in RRULE must have value(s) from -366 to -1, or 1 to 366!', $result[1]['message']);
        self::assertEquals(1, $result[1]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYYEARDAY=2;BYDAY=WE', $property->getValue());
    }

    public function testValidateAllInvalidByYearDayWithRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYYEARDAY=455,-480;BYDAY=WE');
        $result = $property->validate(Node::REPAIR);

        self::assertCount(2, $result);
        self::assertEquals('BYYEARDAY in RRULE must have value(s) from -366 to -1, or 1 to 366!', $result[0]['message']);
        self::assertEquals(1, $result[0]['level']);
        self::assertEquals('BYYEARDAY in RRULE must have value(s) from -366 to -1, or 1 to 366!', $result[1]['message']);
        self::assertEquals(1, $result[1]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYDAY=WE', $property->getValue());
    }

    public function testValidateInvalidByYearDayWithoutRepair(): void
    {
        $calendar = new VCalendar();
        $property = $calendar->createProperty('RRULE', 'FREQ=YEARLY;COUNT=6;BYYEARDAY=380;BYDAY=WE');
        $result = $property->validate();

        self::assertCount(1, $result);
        self::assertEquals('BYYEARDAY in RRULE must have value(s) from -366 to -1, or 1 to 366!', $result[0]['message']);
        self::assertEquals(3, $result[0]['level']);
        self::assertEquals('FREQ=YEARLY;COUNT=6;BYYEARDAY=380;BYDAY=WE', $property->getValue());
    }
}
