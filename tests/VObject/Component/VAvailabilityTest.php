<?php

namespace Sabre\VObject\Component;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;
use Sabre\VObject\Reader;

/**
 * We use `RFCxxx` has a placeholder for the
 * https://tools.ietf.org/html/draft-daboo-calendar-availability-05 name.
 */
class VAvailabilityTest extends TestCase
{
    public function testVAvailabilityComponent(): void
    {
        $vcal = <<<VCAL
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
END:VAVAILABILITY
END:VCALENDAR
VCAL;
        $document = Reader::read($vcal);

        self::assertInstanceOf(VAvailability::class, $document->VAVAILABILITY);
    }

    public function testGetEffectiveStartEnd(): void
    {
        $vcal = <<<VCAL
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
DTSTART:20150717T162200Z
DTEND:20150717T172200Z
END:VAVAILABILITY
END:VCALENDAR
VCAL;

        /** @var VCalendar<int, mixed> $document */
        $document = Reader::read($vcal);
        $tz = new \DateTimeZone('UTC');
        /**
         * @var VAvailability<int, mixed> $availability
         */
        $availability = $document->VAVAILABILITY;
        self::assertEquals(
            [
                new \DateTimeImmutable('2015-07-17 16:22:00', $tz),
                new \DateTimeImmutable('2015-07-17 17:22:00', $tz),
            ],
            $availability->getEffectiveStartEnd()
        );
    }

    public function testGetEffectiveStartDuration(): void
    {
        $vcal = <<<VCAL
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
DTSTART:20150717T162200Z
DURATION:PT1H
END:VAVAILABILITY
END:VCALENDAR
VCAL;

        /** @var VCalendar<int, mixed> $document */
        $document = Reader::read($vcal);
        $tz = new \DateTimeZone('UTC');
        /**
         * @var VAvailability<int, mixed> $availability
         */
        $availability = $document->VAVAILABILITY;
        self::assertEquals(
            [
                new \DateTimeImmutable('2015-07-17 16:22:00', $tz),
                new \DateTimeImmutable('2015-07-17 17:22:00', $tz),
            ],
            $availability->getEffectiveStartEnd()
        );
    }

    public function testGetEffectiveStartEndUnbound(): void
    {
        $vcal = <<<VCAL
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
END:VAVAILABILITY
END:VCALENDAR
VCAL;

        /** @var VCalendar<int, mixed> $document */
        $document = Reader::read($vcal);
        /**
         * @var VAvailability<int, mixed> $availability
         */
        $availability = $document->VAVAILABILITY;
        self::assertEquals(
            [
                null,
                null,
            ],
            $availability->getEffectiveStartEnd()
        );
    }

    public function testIsInTimeRangeUnbound(): void
    {
        $vcal = <<<VCAL
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
END:VAVAILABILITY
END:VCALENDAR
VCAL;

        /** @var VCalendar<int, mixed> $document */
        $document = Reader::read($vcal);
        /**
         * @var VAvailability<int, mixed> $availability
         */
        $availability = $document->VAVAILABILITY;
        self::assertTrue(
            $availability->isInTimeRange(new \DateTimeImmutable('2015-07-17'), new \DateTimeImmutable('2015-07-18'))
        );
    }

    public function testIsInTimeRangeOutside(): void
    {
        $vcal = <<<VCAL
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
DTSTART:20140101T000000Z
DTEND:20140102T000000Z
END:VAVAILABILITY
END:VCALENDAR
VCAL;

        /** @var VCalendar<int, mixed> $document */
        $document = Reader::read($vcal);
        /**
         * @var VAvailability<int, mixed> $availability
         */
        $availability = $document->VAVAILABILITY;
        self::assertFalse(
            $availability->isInTimeRange(new \DateTimeImmutable('2015-07-17'), new \DateTimeImmutable('2015-07-18'))
        );
    }

    public function testRFCxxxSection3Part1AvailabilityPropRequired(): void
    {
        // UID and DTSTAMP are present.
        self::assertIsValid(Reader::read(
            <<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
UID:foo@test
DTSTAMP:20111005T133225Z
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));

        // UID and DTSTAMP are missing.
        self::assertIsNotValid(Reader::read(
            <<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));

        // DTSTAMP is missing.
        self::assertIsNotValid(Reader::read(
            <<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
UID:foo@test
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));

        // UID is missing.
        self::assertIsNotValid(Reader::read(
            <<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
DTSTAMP:20111005T133225Z
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));
    }

    public function testRFCxxxSection3Part1AvailabilityPropOptionalOnce(): void
    {
        $properties = [
            'BUSYTYPE:BUSY',
            'CLASS:PUBLIC',
            'CREATED:20111005T135125Z',
            'DESCRIPTION:Long bla bla',
            'DTSTART:20111005T020000',
            'LAST-MODIFIED:20111005T135325Z',
            'ORGANIZER:mailto:foo@example.com',
            'PRIORITY:1',
            'SEQUENCE:0',
            'SUMMARY:Bla bla',
            'URL:http://example.org/',
        ];

        // They are all present, only once.
        self::assertIsValid(Reader::read($this->template($properties)));

        // We duplicate each one to see if it fails.
        foreach ($properties as $property) {
            self::assertIsNotValid(Reader::read($this->template([
                $property,
                $property,
            ])));
        }
    }

    public function testRFCxxxSection3Part1AvailabilityPropDtendDuration(): void
    {
        // Only DTEND.
        self::assertIsValid(Reader::read($this->template([
            'DTEND:21111005T133225Z',
        ])));

        // Only DURATION.
        self::assertIsValid(Reader::read($this->template([
            'DURATION:PT1H',
        ])));

        // Both (not allowed).
        self::assertIsNotValid(Reader::read($this->template([
            'DTEND:21111005T133225Z',
            'DURATION:PT1H',
        ])));
    }

    public function testAvailableSubComponent(): void
    {
        $vcal = <<<VCAL
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
BEGIN:AVAILABLE
END:AVAILABLE
END:VAVAILABILITY
END:VCALENDAR
VCAL;
        /** @var VCalendar<int, mixed> $document */
        $document = Reader::read($vcal);
        /**
         * @var VAvailability<int, mixed> $availability
         */
        $availability = $document->VAVAILABILITY;

        self::assertInstanceOf(Available::class, $availability->AVAILABLE);
    }

    public function testRFCxxxSection3Part1AvailablePropRequired(): void
    {
        // UID, DTSTAMP and DTSTART are present.
        self::assertIsValid(Reader::read(
            <<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
UID:foo@test
DTSTAMP:20111005T133225Z
BEGIN:AVAILABLE
UID:foo@test
DTSTAMP:20111005T133225Z
DTSTART:20111005T133225Z
END:AVAILABLE
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));

        // UID, DTSTAMP and DTSTART are missing.
        self::assertIsNotValid(Reader::read(
            <<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
UID:foo@test
DTSTAMP:20111005T133225Z
BEGIN:AVAILABLE
END:AVAILABLE
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));

        // UID is missing.
        self::assertIsNotValid(Reader::read(
            <<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
UID:foo@test
DTSTAMP:20111005T133225Z
BEGIN:AVAILABLE
DTSTAMP:20111005T133225Z
DTSTART:20111005T133225Z
END:AVAILABLE
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));

        // DTSTAMP is missing.
        self::assertIsNotValid(Reader::read(
            <<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
UID:foo@test
DTSTAMP:20111005T133225Z
BEGIN:AVAILABLE
UID:foo@test
DTSTART:20111005T133225Z
END:AVAILABLE
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));

        // DTSTART is missing.
        self::assertIsNotValid(Reader::read(
            <<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
UID:foo@test
DTSTAMP:20111005T133225Z
BEGIN:AVAILABLE
UID:foo@test
DTSTAMP:20111005T133225Z
END:AVAILABLE
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));
    }

    public function testRFCxxxSection3Part1AvailableDtendDuration(): void
    {
        // Only DTEND.
        self::assertIsValid(Reader::read($this->templateAvailable([
            'DTEND:21111005T133225Z',
        ])));

        // Only DURATION.
        self::assertIsValid(Reader::read($this->templateAvailable([
            'DURATION:PT1H',
        ])));

        // Both (not allowed).
        self::assertIsNotValid(Reader::read($this->templateAvailable([
            'DTEND:21111005T133225Z',
            'DURATION:PT1H',
        ])));
    }

    public function testRFCxxxSection3Part1AvailableOptionalOnce(): void
    {
        $properties = [
            'CREATED:20111005T135125Z',
            'DESCRIPTION:Long bla bla',
            'LAST-MODIFIED:20111005T135325Z',
            'RECURRENCE-ID;RANGE=THISANDFUTURE:19980401T133000Z',
            'RRULE:FREQ=WEEKLY;BYDAY=MO,TU,WE,TH,FR',
            'SUMMARY:Bla bla',
        ];

        // They are all present, only once.
        self::assertIsValid(Reader::read($this->templateAvailable($properties)));

        // We duplicate each one to see if it fails.
        foreach ($properties as $property) {
            self::assertIsNotValid(Reader::read($this->templateAvailable([
                $property,
                $property,
            ])));
        }
    }

    public function testRFCxxxSection3Part2(): void
    {
        /** @var VCalendar<int, mixed> $document */
        $document = Reader::read($this->templateAvailable(['BUSYTYPE:BUSY']));
        /**
         * @var VAvailability<int, mixed> $availability
         */
        $availability = $document->VAVAILABILITY;
        /**
         * @var Available<int, mixed> $available
         */
        $available = $availability->AVAILABLE;
        self::assertEquals(
            'BUSY',
            $available->BUSYTYPE->getValue()
        );

        /** @var VCalendar<int, mixed> $document */
        $document = Reader::read($this->templateAvailable(['BUSYTYPE:BUSY-UNAVAILABLE']));
        /**
         * @var VAvailability<int, mixed> $availability
         */
        $availability = $document->VAVAILABILITY;
        /**
         * @var Available<int, mixed> $available
         */
        $available = $availability->AVAILABLE;
        self::assertEquals(
            'BUSY-UNAVAILABLE',
            $available->BUSYTYPE->getValue()
        );

        /** @var VCalendar<int, mixed> $document */
        $document = Reader::read($this->templateAvailable(['BUSYTYPE:BUSY-TENTATIVE']));
        /**
         * @var VAvailability<int, mixed> $availability
         */
        $availability = $document->VAVAILABILITY;
        /**
         * @var Available<int, mixed> $available
         */
        $available = $availability->AVAILABLE;
        self::assertEquals(
            'BUSY-TENTATIVE',
            $available->BUSYTYPE->getValue()
        );
    }

    /**
     * @param VObject\Document<int, mixed> $document
     */
    protected static function assertIsValid(VObject\Document $document): void
    {
        $validationResult = $document->validate();
        if ($validationResult) {
            $messages = array_map(function ($item) { return $item['message']; }, $validationResult);
            self::fail('Failed to assert that the supplied document is a valid document. Validation messages: '.implode(', ', $messages));
        }
        self::assertEmpty($document->validate());
    }

    /**
     * @param VObject\Document<int, mixed> $document
     */
    protected static function assertIsNotValid(VObject\Document $document): void
    {
        self::assertNotEmpty($document->validate());
    }

    /**
     * @param array<int, string> $properties
     */
    protected function template(array $properties): string
    {
        return $this->_template(
            <<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
UID:foo@test
DTSTAMP:20111005T133225Z
…
END:VAVAILABILITY
END:VCALENDAR
VCAL
            ,
            $properties
        );
    }

    /**
     * @param array<int, string> $properties
     */
    protected function templateAvailable(array $properties): string
    {
        return $this->_template(
            <<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
UID:foo@test
DTSTAMP:20111005T133225Z
BEGIN:AVAILABLE
UID:foo@test
DTSTAMP:20111005T133225Z
DTSTART:20111005T133225Z
…
END:AVAILABLE
END:VAVAILABILITY
END:VCALENDAR
VCAL
            ,
            $properties
        );
    }

    /**
     * @param array<int, string> $properties
     */
    protected function _template(string $template, array $properties): string
    {
        return str_replace('…', implode("\r\n", $properties), $template);
    }
}
