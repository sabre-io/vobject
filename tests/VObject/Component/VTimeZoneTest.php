<?php

namespace Sabre\VObject\Component;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Reader;

class VTimeZoneTest extends TestCase {

    function testValidateFails() {

        $input = <<<HI
BEGIN:VCALENDAR
VERSION:2.0
PRODID:YoYo
BEGIN:VTIMEZONE
TZID:America/Toronto
END:VTIMEZONE
END:VCALENDAR
HI;

        $obj = Reader::read($input);

        $warnings = $obj->validate();
        $messages = [];
        foreach ($warnings as $warning) {
            $messages[] = $warning['message'];
        }

        $this->assertEquals([
            'DTSTART MUST appear exactly once in a VTIMEZONE component',
            'TZOFFSETTO MUST appear exactly once in a VTIMEZONE component',
            'TZOFFSETFROM MUST appear exactly once in a VTIMEZONE component',
        ], $messages);

    }


    function testValidatePasses() {
        $input = <<<HI
BEGIN:VCALENDAR
VERSION:2.0
PRODID:YoYo
BEGIN:VTIMEZONE
TZID:America/New_York
DTSTART:20080309T070000
TZOFFSETFROM:-0500
TZOFFSETTO:-0400
END:VTIMEZONE
END:VCALENDAR
HI;

        $obj = Reader::read($input);

        $warnings = $obj->validate();
        $messages = [];
        foreach ($warnings as $warning) {
            $messages[] = $warning['message'];
        }

        $this->assertEquals([], $messages);
    }


    function testGetTimeZone() {

        $input = <<<HI
BEGIN:VCALENDAR
VERSION:2.0
PRODID:YoYo
BEGIN:VTIMEZONE
TZID:America/Toronto
END:VTIMEZONE
END:VCALENDAR
HI;

        $obj = Reader::read($input);

        $tz = new \DateTimeZone('America/Toronto');

        $this->assertEquals(
            $tz,
            $obj->VTIMEZONE->getTimeZone()
        );

    }

}
