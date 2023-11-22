<?php

namespace Sabre\VObject\Component;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Reader;

/**
 * We use `RFCxxx` has a placeholder for the
 * https://tools.ietf.org/html/draft-daboo-calendar-availability-05 name.
 */
class AvailableTest extends TestCase
{
    public function testAvailableComponent(): void
    {
        $vcal = <<<VCAL
BEGIN:VCALENDAR
BEGIN:AVAILABLE
END:AVAILABLE
END:VCALENDAR
VCAL;
        $document = Reader::read($vcal);
        self::assertInstanceOf(Available::class, $document->AVAILABLE);
    }

    public function testGetEffectiveStartEnd(): void
    {
        $vcal = <<<VCAL
BEGIN:VCALENDAR
BEGIN:AVAILABLE
DTSTART:20150717T162200Z
DTEND:20150717T172200Z
END:AVAILABLE
END:VCALENDAR
VCAL;

        /** @var VCalendar<int, mixed> $document */
        $document = Reader::read($vcal);
        $tz = new \DateTimeZone('UTC');
        /**
         * @var Available<int, mixed> $available
         */
        $available = $document->AVAILABLE;
        self::assertEquals(
            [
                new \DateTimeImmutable('2015-07-17 16:22:00', $tz),
                new \DateTimeImmutable('2015-07-17 17:22:00', $tz),
            ],
            $available->getEffectiveStartEnd()
        );
    }

    public function testGetEffectiveStartEndDuration(): void
    {
        $vcal = <<<VCAL
BEGIN:VCALENDAR
BEGIN:AVAILABLE
DTSTART:20150717T162200Z
DURATION:PT1H
END:AVAILABLE
END:VCALENDAR
VCAL;

        /** @var VCalendar<int, mixed> $document */
        $document = Reader::read($vcal);
        $tz = new \DateTimeZone('UTC');
        /**
         * @var Available<int, mixed> $available
         */
        $available = $document->AVAILABLE;
        self::assertEquals(
            [
                new \DateTimeImmutable('2015-07-17 16:22:00', $tz),
                new \DateTimeImmutable('2015-07-17 17:22:00', $tz),
            ],
            $available->getEffectiveStartEnd()
        );
    }
}
