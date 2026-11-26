<?php

namespace Sabre\VObject\Recur\EventIterator;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;
use Sabre\VObject\Recur\EventIterator;

/**
 * An RDATE with its own TZID.
 *
 * @see https://github.com/sabre-io/vobject/issues/819
 */
class RDateTimezoneTest extends TestCase
{
    /**
     * @return array<string, array{string, string[]}>
     */
    public static function startProvider(): array
    {
        return [
            // 10:00 in Paris is 09:00 UTC in January.
            'TZID' => [
                "DTSTART:20270104T100000Z\nRDATE;TZID=Europe/Paris:20270108T100000",
                ['2027-01-04 10:00', '2027-01-08 09:00'],
            ],
            // 04:00 in New York is 09:00 UTC, 17:00 in Tokyo is 08:00 UTC.
            'several TZID' => [
                "DTSTART:20270104T100000Z\nRDATE;TZID=America/New_York:20270108T040000\nRDATE;TZID=Asia/Tokyo:20270108T170000",
                ['2027-01-04 10:00', '2027-01-08 08:00', '2027-01-08 09:00'],
            ],
            'UTC and TZID' => [
                "DTSTART;TZID=Europe/Paris:20270104T100000\nRDATE:20270108T090000Z\nRDATE;TZID=Asia/Tokyo:20270108T170000",
                ['2027-01-04 09:00', '2027-01-08 08:00', '2027-01-08 09:00'],
            ],
            // Without a TZID, the RDATE is in the timezone of DTSTART.
            'no TZID' => [
                "DTSTART;TZID=Europe/Paris:20270104T100000\nRDATE:20270108T100000",
                ['2027-01-04 09:00', '2027-01-08 09:00'],
            ],
            'all day' => [
                "DTSTART;VALUE=DATE:20270104\nRDATE;VALUE=DATE:20270108",
                ['2027-01-04 00:00', '2027-01-08 00:00'],
            ],
        ];
    }

    /**
     * @param string[] $expected start of each instance, in UTC
     */
    #[DataProvider('startProvider')]
    public function testStart(string $dates, array $expected): void
    {
        $iterator = new EventIterator($this->calendar($dates), 'test');

        $utc = new \DateTimeZone('UTC');
        $starts = [];
        foreach ($iterator as $_) {
            $starts[] = $iterator->getDtStart()?->setTimezone($utc)->format('Y-m-d H:i');
        }

        self::assertSame($expected, $starts);
    }

    public function testStartKeepsTheTimezoneOfDtstart(): void
    {
        $iterator = new EventIterator(
            $this->calendar("DTSTART;TZID=Europe/Paris:20270104T100000\nRDATE;TZID=America/New_York:20270108T040000"),
            'test'
        );
        $iterator->next();

        self::assertSame('2027-01-08 10:00 Europe/Paris', $iterator->getDtStart()?->format('Y-m-d H:i e'));
    }

    /**
     * The RDATE starts at 00:30 EST, before the move to summer time. It keeps
     * the exact duration of 3 hours.
     */
    public function testEndAcrossDst(): void
    {
        $iterator = new EventIterator(
            $this->calendar("DTSTART;TZID=America/New_York:20270306T120000\nDTEND;TZID=America/New_York:20270306T150000\nRDATE:20270314T053000Z"),
            'test'
        );
        $iterator->next();

        self::assertSame('2027-03-14 00:30 EST', $iterator->getDtStart()?->format('Y-m-d H:i T'));
        self::assertSame('2027-03-14 04:30 EDT', $iterator->getDtEnd()?->format('Y-m-d H:i T'));
    }

    private function calendar(string $dates): VCalendar
    {
        $calendar = Reader::read(<<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject//EN
BEGIN:VEVENT
UID:test
DTSTAMP:20260101T000000Z
$dates
SUMMARY:Test
END:VEVENT
END:VCALENDAR
ICS);
        self::assertInstanceOf(VCalendar::class, $calendar);

        return $calendar;
    }
}
