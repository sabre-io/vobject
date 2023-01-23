<?php

namespace Sabre\VObject\Component;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;
use Sabre\VObject\InvalidDataException;
use Sabre\VObject\Property\FlatText;

class VCalendarTest extends TestCase
{
    use VObject\PHPUnitAssertions;

    /**
     * @dataProvider expandData
     */
    public function testExpand(string $input, string $output, string $timeZone = 'UTC', string $start = '2011-12-01', string $end = '2011-12-31'): void
    {
        /** @var VCalendar<int, mixed> $vcal */
        $vcal = VObject\Reader::read($input);

        $timeZone = new \DateTimeZone($timeZone);

        $vcal = $vcal->expand(
            new \DateTime($start),
            new \DateTime($end),
            $timeZone
        );

        // This will normalize the output
        $output = VObject\Reader::read($output)->serialize();

        self::assertVObjectEqualsVObject($output, $vcal->serialize());
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function expandData(): array
    {
        $tests = [];

        // No data
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
END:VCALENDAR
';

        $output = $input;
        $tests[] = [$input, $output];

        // Simple events
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla
SUMMARY:InExpand
DTSTART;VALUE=DATE:20111202
END:VEVENT
BEGIN:VEVENT
UID:bla2
SUMMARY:NotInExpand
DTSTART;VALUE=DATE:20120101
END:VEVENT
END:VCALENDAR
';

        $output = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla
SUMMARY:InExpand
DTSTART;VALUE=DATE:20111202
END:VEVENT
END:VCALENDAR
';

        $tests[] = [$input, $output];

        // Removing timezone info
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VTIMEZONE
TZID:Europe/Paris
END:VTIMEZONE
BEGIN:VEVENT
UID:bla4
SUMMARY:RemoveTZ info
DTSTART;TZID=Europe/Paris:20111203T130102
END:VEVENT
END:VCALENDAR
';

        $output = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla4
SUMMARY:RemoveTZ info
DTSTART:20111203T120102Z
END:VEVENT
END:VCALENDAR
';

        $tests[] = [$input, $output];

        // Removing timezone info from sub-components. See Issue #278
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VTIMEZONE
TZID:Europe/Paris
END:VTIMEZONE
BEGIN:VEVENT
UID:bla4
SUMMARY:RemoveTZ info
DTSTART;TZID=Europe/Paris:20111203T130102
BEGIN:VALARM
TRIGGER;VALUE=DATE-TIME;TZID=America/New_York:20151209T133200
END:VALARM
END:VEVENT
END:VCALENDAR
';

        $output = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla4
SUMMARY:RemoveTZ info
DTSTART:20111203T120102Z
BEGIN:VALARM
TRIGGER;VALUE=DATE-TIME:20151209T183200Z
END:VALARM
END:VEVENT
END:VCALENDAR
';

        $tests[] = [$input, $output];

        // Recurrence rule
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule
DTSTART:20111125T120000Z
DTEND:20111125T130000Z
RRULE:FREQ=WEEKLY
END:VEVENT
END:VCALENDAR
';

        $output = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule
DTSTART:20111202T120000Z
DTEND:20111202T130000Z
RECURRENCE-ID:20111202T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule
DTSTART:20111209T120000Z
DTEND:20111209T130000Z
RECURRENCE-ID:20111209T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule
DTSTART:20111216T120000Z
DTEND:20111216T130000Z
RECURRENCE-ID:20111216T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule
DTSTART:20111223T120000Z
DTEND:20111223T130000Z
RECURRENCE-ID:20111223T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule
DTSTART:20111230T120000Z
DTEND:20111230T130000Z
RECURRENCE-ID:20111230T120000Z
END:VEVENT
END:VCALENDAR
';

        $tests[] = [$input, $output];

        // Recurrence rule + override
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule2
DTSTART:20111125T120000Z
DTEND:20111125T130000Z
RRULE:FREQ=WEEKLY
END:VEVENT
BEGIN:VEVENT
UID:bla6
RECURRENCE-ID:20111209T120000Z
DTSTART:20111209T140000Z
DTEND:20111209T150000Z
SUMMARY:Override!
END:VEVENT
END:VCALENDAR
';

        $output = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule2
DTSTART:20111202T120000Z
DTEND:20111202T130000Z
RECURRENCE-ID:20111202T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
RECURRENCE-ID:20111209T120000Z
DTSTART:20111209T140000Z
DTEND:20111209T150000Z
SUMMARY:Override!
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule2
DTSTART:20111216T120000Z
DTEND:20111216T130000Z
RECURRENCE-ID:20111216T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule2
DTSTART:20111223T120000Z
DTEND:20111223T130000Z
RECURRENCE-ID:20111223T120000Z
END:VEVENT
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule2
DTSTART:20111230T120000Z
DTEND:20111230T130000Z
RECURRENCE-ID:20111230T120000Z
END:VEVENT
END:VCALENDAR
';

        $tests[] = [$input, $output];

        // Floating dates and times.
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:bla1
DTSTART:20141112T195000
END:VEVENT
BEGIN:VEVENT
UID:bla2
DTSTART;VALUE=DATE:20141112
END:VEVENT
BEGIN:VEVENT
UID:bla3
DTSTART;VALUE=DATE:20141112
RRULE:FREQ=DAILY;COUNT=2
END:VEVENT
END:VCALENDAR
ICS;

        $output = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:bla1
DTSTART:20141112T225000Z
END:VEVENT
BEGIN:VEVENT
UID:bla2
DTSTART;VALUE=DATE:20141112
END:VEVENT
BEGIN:VEVENT
UID:bla3
DTSTART;VALUE=DATE:20141112
RECURRENCE-ID;VALUE=DATE:20141112
END:VEVENT
BEGIN:VEVENT
UID:bla3
DTSTART;VALUE=DATE:20141113
RECURRENCE-ID;VALUE=DATE:20141113
END:VEVENT
END:VCALENDAR
ICS;

        $tests[] = [$input, $output, 'America/Argentina/Buenos_Aires', '2014-01-01', '2015-01-01'];

        // Recurrence rule with no valid instances
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
UID:bla6
SUMMARY:Testing RRule3
DTSTART:20111125T120000Z
DTEND:20111125T130000Z
RRULE:FREQ=WEEKLY;COUNT=1
EXDATE:20111125T120000Z
END:VEVENT
END:VCALENDAR
';

        $output = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
END:VCALENDAR
';

        $tests[] = [$input, $output];

        return $tests;
    }

    public function testBrokenEventExpand(): void
    {
        $this->expectException(InvalidDataException::class);
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
RRULE:FREQ=WEEKLY
DTSTART;VALUE=DATE:20111202
END:VEVENT
END:VCALENDAR
';
        /** @var VCalendar<int, mixed> $vcal */
        $vcal = VObject\Reader::read($input);
        $vcal->expand(
            new \DateTime('2011-12-01'),
            new \DateTime('2011-12-31')
        );
    }

    /**
     * This test used to induce an infinite loop.
     * The "medium" annotation means that phpunit will fail the
     * test if it takes longer than a default of 10 seconds.
     *
     * @medium
     */
    public function testEventExpandYearly(): void
    {
        $input = 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:1a093f1012086078fdd3d9df5ff4d7d0
DTSTART;TZID=UTC:20210203T130000
DTEND;TZID=UTC:20210203T140000
RRULE:FREQ=YEARLY;COUNT=7;WKST=MO;BYDAY=MO;BYWEEKNO=13,15,50
END:VEVENT
END:VCALENDAR
';
        /** @var VCalendar<int, mixed> $vcal */
        $vcal = VObject\Reader::read($input);
        $events = $vcal->expand(
            new \DateTime('2021-01-01'),
            new \DateTime('2023-01-01')
        );

        self::assertCount(7, $events->VEVENT);
    }

    public function testGetDocumentType(): void
    {
        $vcard = new VCalendar();
        /** @var FlatText<mixed, mixed> $property */
        $property = $vcard->createProperty('VERSION');
        $property->setValue('2.0');
        $vcard->VERSION = $property;
        self::assertEquals(VCalendar::ICALENDAR20, $vcard->getDocumentType());
    }

    public function testValidateCorrect(): void
    {
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
PRODID:foo
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
DTSTAMP:20140122T233226Z
UID:foo
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        self::assertEquals([], $vcal->validate(), 'Got an error');
    }

    public function testValidateNoVersion(): void
    {
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
PRODID:foo
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        self::assertCount(1, $vcal->validate());
    }

    public function testValidateWrongVersion(): void
    {
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:3.0
PRODID:foo
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        self::assertCount(1, $vcal->validate());
    }

    public function testValidateNoProdId(): void
    {
        $input = 'BEGIN:VCALENDAR
CALSCALE:GREGORIAN
VERSION:2.0
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        self::assertCount(1, $vcal->validate());
    }

    public function testValidateDoubleCalScale(): void
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
CALSCALE:GREGORIAN
CALSCALE:GREGORIAN
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        self::assertCount(1, $vcal->validate());
    }

    public function testValidateDoubleMethod(): void
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
METHOD:REQUEST
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        self::assertCount(1, $vcal->validate());
    }

    public function testValidateTwoMasterEvents(): void
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        self::assertCount(1, $vcal->validate());
    }

    public function testValidateOneMasterEvent(): void
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
RECURRENCE-ID;VALUE=DATE:20111202
END:VEVENT
END:VCALENDAR
';

        $vcal = VObject\Reader::read($input);
        self::assertCount(0, $vcal->validate());
    }

    public function testGetBaseComponent(): void
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
BEGIN:VEVENT
SUMMARY:test
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
RECURRENCE-ID;VALUE=DATE:20111202
END:VEVENT
END:VCALENDAR
';

        /** @var VCalendar<int, mixed> $vcal */
        $vcal = VObject\Reader::read($input);

        /** @var VEvent<int, mixed> $result */
        $result = $vcal->getBaseComponent();
        self::assertEquals('test', $result->SUMMARY->getValue());
    }

    public function testGetBaseComponentNoResult(): void
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
BEGIN:VEVENT
SUMMARY:test
RECURRENCE-ID;VALUE=DATE:20111202
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
RECURRENCE-ID;VALUE=DATE:20111202
END:VEVENT
END:VCALENDAR
';

        /** @var VCalendar<int, mixed> $vcal */
        $vcal = VObject\Reader::read($input);

        $result = $vcal->getBaseComponent();
        self::assertNull($result);
    }

    public function testGetBaseComponentWithFilter(): void
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
BEGIN:VEVENT
SUMMARY:test
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
END:VEVENT
BEGIN:VEVENT
DTSTART;VALUE=DATE:20111202
UID:foo
DTSTAMP:20140122T234434Z
RECURRENCE-ID;VALUE=DATE:20111202
END:VEVENT
END:VCALENDAR
';

        /** @var VCalendar<int, mixed> $vcal */
        $vcal = VObject\Reader::read($input);

        /** @var VEvent<int, mixed> $result */
        $result = $vcal->getBaseComponent('VEVENT');
        self::assertEquals('test', $result->SUMMARY->getValue());
    }

    public function testGetBaseComponentWithFilterNoResult(): void
    {
        $input = 'BEGIN:VCALENDAR
VERSION:2.0
PRODID:foo
METHOD:REQUEST
BEGIN:VTODO
SUMMARY:test
UID:foo
DTSTAMP:20140122T234434Z
END:VTODO
END:VCALENDAR
';

        /** @var VCalendar<int, mixed> $vcal */
        $vcal = VObject\Reader::read($input);

        /** @var VEvent<int, mixed> $result */
        $result = $vcal->getBaseComponent('VEVENT');
        self::assertNull($result);
    }

    public function testNoComponents(): void
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:vobject
END:VCALENDAR
ICS;

        self::assertValidate(
            $input,
            0,
            3,
            'An iCalendar object must have at least 1 component.'
        );
    }

    public function testCalDAVNoComponents(): void
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:vobject
BEGIN:VTIMEZONE
TZID:America/Toronto
END:VTIMEZONE
END:VCALENDAR
ICS;

        self::assertValidate(
            $input,
            VCalendar::PROFILE_CALDAV,
            3,
            'A calendar object on a CalDAV server must have at least 1 component (VTODO, VEVENT, VJOURNAL).'
        );
    }

    public function testCalDAVMultiUID(): void
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:vobject
BEGIN:VEVENT
UID:foo
DTSTAMP:20150109T184500Z
DTSTART:20150109T184500Z
END:VEVENT
BEGIN:VEVENT
UID:bar
DTSTAMP:20150109T184500Z
DTSTART:20150109T184500Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertValidate(
            $input,
            VCalendar::PROFILE_CALDAV,
            3,
            'A calendar object on a CalDAV server may only have components with the same UID.'
        );
    }

    public function testCalDAVMultiComponent(): void
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:vobject
BEGIN:VEVENT
UID:foo
RECURRENCE-ID:20150109T185200Z
DTSTAMP:20150109T184500Z
DTSTART:20150109T184500Z
END:VEVENT
BEGIN:VTODO
UID:foo
DTSTAMP:20150109T184500Z
DTSTART:20150109T184500Z
END:VTODO
END:VCALENDAR
ICS;

        self::assertValidate(
            $input,
            VCalendar::PROFILE_CALDAV,
            3,
            'A calendar object on a CalDAV server may only have 1 type of component (VEVENT, VTODO or VJOURNAL).'
        );
    }

    public function testCalDAVMETHOD(): void
    {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:PUBLISH
PRODID:vobject
BEGIN:VEVENT
UID:foo
RECURRENCE-ID:20150109T185200Z
DTSTAMP:20150109T184500Z
DTSTART:20150109T184500Z
END:VEVENT
END:VCALENDAR
ICS;

        self::assertValidate(
            $input,
            VCalendar::PROFILE_CALDAV,
            3,
            'A calendar object on a CalDAV server MUST NOT have a METHOD property.'
        );
    }

    public function assertValidate(string $ics, int $options, int $expectedLevel, ?string $expectedMessage = null): void
    {
        $vcal = VObject\Reader::read($ics);
        $result = $vcal->validate($options);

        self::assertValidateResult($result, $expectedLevel, $expectedMessage);
    }

    /**
     * @param array<int, array<string, mixed>> $input
     */
    public function assertValidateResult(array $input, int $expectedLevel, ?string $expectedMessage = null): void
    {
        $messages = [];
        foreach ($input as $warning) {
            $messages[] = $warning['message'];
        }

        if (0 === $expectedLevel) {
            self::assertCount(0, $input, 'No validation messages were expected. We got: '.implode(', ', $messages));
        } else {
            self::assertCount(1, $input, 'We expected exactly 1 validation message, We got: '.implode(', ', $messages));

            self::assertEquals($expectedMessage, $input[0]['message']);
            self::assertEquals($expectedLevel, $input[0]['level']);
        }
    }
}
