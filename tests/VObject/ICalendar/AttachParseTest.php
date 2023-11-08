<?php

namespace Sabre\VObject\ICalendar;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;
use Sabre\VObject\Property;
use Sabre\VObject\Property\Uri;
use Sabre\VObject\Reader;

class AttachParseTest extends TestCase
{
    /**
     * See issue #128 for more info.
     */
    public function testParseAttach(): void
    {
        $vcalString = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
ATTACH;FMTTYPE=application/postscript:ftp://example.com/pub/reports/r-960812.ps
END:VEVENT
END:VCALENDAR
ICS;

        /** @var VCalendar<int, mixed> $vcal */
        $vcal = Reader::read($vcalString);
        /** @var VEvent<int, mixed> $event */
        $event = $vcal->VEVENT;
        /**
         * @var Property<int, mixed> $prop
         */
        $prop = $event->ATTACH;

        self::assertInstanceOf(Uri::class, $prop);
        self::assertEquals('ftp://example.com/pub/reports/r-960812.ps', $prop->getValue());
    }
}
