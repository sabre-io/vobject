<?php

namespace Sabre\VObject\Recur\EventIterator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;
use Sabre\VObject\Recur\EventIterator;

/**
 * The end of an instance that crosses a DST transition.
 *
 * RFC 5545, section 3.8.5.3: with DTEND, all instances have the same exact
 * duration. With DURATION, they have the same nominal duration.
 *
 * @see https://github.com/sabre-io/vobject/issues/818
 */
class DurationAcrossDstTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: string[], 2?: string}>
     */
    public static function endProvider(): array
    {
        return [
            // 23 hours: New York moves to summer time on March 14, 2027.
            'DTEND, to summer time' => [
                "DTSTART;TZID=America/New_York:20270313T120000\nDTEND;TZID=America/New_York:20270314T120000",
                ['2027-03-14 12:00 EDT', '2027-03-21 11:00 EDT'],
            ],
            // 24 hours, and the second instance crosses the transition.
            'DTEND, second instance to summer time' => [
                "DTSTART;TZID=America/New_York:20270306T120000\nDTEND;TZID=America/New_York:20270307T120000",
                ['2027-03-07 12:00 EST', '2027-03-14 13:00 EDT'],
            ],
            // 25 hours: New York moves to winter time on November 7, 2027.
            'DTEND, to winter time' => [
                "DTSTART;TZID=America/New_York:20271106T120000\nDTEND;TZID=America/New_York:20271107T120000",
                ['2027-11-07 12:00 EST', '2027-11-14 13:00 EST'],
            ],
            'DURATION, to summer time' => [
                "DTSTART;TZID=America/New_York:20270313T120000\nDURATION:P1D",
                ['2027-03-14 12:00 EDT', '2027-03-21 12:00 EDT'],
            ],
            'DURATION, second instance to summer time' => [
                "DTSTART;TZID=America/New_York:20270306T120000\nDURATION:P1D",
                ['2027-03-07 12:00 EST', '2027-03-14 12:00 EDT'],
            ],
            'DURATION in hours, to summer time' => [
                "DTSTART;TZID=America/New_York:20270313T120000\nDURATION:PT24H",
                ['2027-03-14 13:00 EDT', '2027-03-21 12:00 EDT'],
            ],
            'DURATION, to winter time' => [
                "DTSTART;TZID=America/New_York:20271106T120000\nDURATION:P1D",
                ['2027-11-07 12:00 EST', '2027-11-14 12:00 EST'],
            ],
            'DURATION in hours, to winter time' => [
                "DTSTART;TZID=America/New_York:20271106T120000\nDURATION:PT24H",
                ['2027-11-07 11:00 EST', '2027-11-14 12:00 EST'],
            ],
            // The day is nominal, then the hours are exact: 01:00 EST plus
            // 2 hours is 04:00 EDT.
            'DURATION in days and hours, to summer time' => [
                "DTSTART;TZID=America/New_York:20270313T010000\nDURATION:P1DT2H",
                ['2027-03-14 04:00 EDT', '2027-03-21 03:00 EDT'],
            ],
            'Floating DURATION in hours, to summer time' => [
                "DTSTART:20270306T120000\nDURATION:PT24H",
                ['2027-03-07 12:00 EST', '2027-03-14 13:00 EDT'],
                'America/New_York',
            ],
            'All-day DURATION, to summer time' => [
                "DTSTART;VALUE=DATE:20270314\nDURATION:P1D",
                ['2027-03-15 00:00 EDT', '2027-03-22 00:00 EDT'],
                'America/New_York',
            ],
        ];
    }

    /**
     * @param string[] $expected
     */
    #[DataProvider('endProvider')]
    public function testGetDtEnd(string $times, array $expected, ?string $timeZone = null): void
    {
        $iterator = new EventIterator(
            $this->calendar($times),
            'test',
            $timeZone ? new \DateTimeZone($timeZone) : null
        );

        $ends = [];
        foreach ($iterator as $_) {
            $ends[] = $iterator->getDtEnd()?->format('Y-m-d H:i T');
        }

        self::assertSame($expected, $ends);
    }

    public function testGetEventObject(): void
    {
        $iterator = new EventIterator(
            $this->calendar("DTSTART;TZID=America/New_York:20270313T120000\nDTEND;TZID=America/New_York:20270314T120000"),
            'test'
        );

        self::assertSame(
            '2027-03-14 12:00 EDT',
            $iterator->getEventObject()->DTEND?->getDateTime()->format('Y-m-d H:i T')
        );
    }

    private function calendar(string $times): VCalendar
    {
        $calendar = Reader::read(<<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject//EN
BEGIN:VEVENT
UID:test
DTSTAMP:20260101T000000Z
$times
RRULE:FREQ=WEEKLY;COUNT=2
SUMMARY:Test
END:VEVENT
END:VCALENDAR
ICS);
        self::assertInstanceOf(VCalendar::class, $calendar);

        return $calendar;
    }
}
