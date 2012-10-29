<?php

namespace Sabre\VObject;

class TimezoneUtilTest extends \PHPUnit_Framework_TestCase {

    /**
     * @dataProvider getMapping
     */
    function testCorrectTZ($timezoneName) {

        $tz = new \DateTimeZone($timezoneName);

    }

    function getMapping() {

        // PHPUNit requires an array of arrays
        return array_map(
            function($value) {
                return array($value);
            },
            TimeZoneUtil::$map
        );

    }

    function testExchangeMap() {

        $vobj = <<<HI
BEGIN:VCALENDAR
METHOD:REQUEST
VERSION:2.0
BEGIN:VTIMEZONE
TZID:foo
X-MICROSOFT-CDO-TZID:2
BEGIN:STANDARD
DTSTART:16010101T030000
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=10;BYDAY=-1SU
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:16010101T020000
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=3;BYDAY=-1SU
END:DAYLIGHT
END:VTIMEZONE
BEGIN:VEVENT
DTSTAMP:20120416T092149Z
DTSTART;TZID="foo":20120418T1
 00000
SUMMARY:Begin Unterhaltsreinigung
UID:040000008200E00074C5B7101A82E0080000000010DA091DC31BCD01000000000000000
 0100000008FECD2E607780649BE5A4C9EE6418CBC
DTEND;TZID="Sarajevo, Skopje, Sofija, Vilnius, Warsaw, Zagreb":20120418T103
 000
END:VEVENT
END:VCALENDAR
HI;

        $tz = TimeZoneUtil::getTimeZone('foo', Reader::read($vobj));
        $ex = new \DateTimeZone('Europe/Lisbon');

        $this->assertEquals($ex->getName(), $tz->getName());

    }

    function testUnknownExchangeId() {

        $vobj = <<<HI
BEGIN:VCALENDAR
METHOD:REQUEST
VERSION:2.0
BEGIN:VTIMEZONE
TZID:foo
X-MICROSOFT-CDO-TZID:2000
BEGIN:STANDARD
DTSTART:16010101T030000
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=10;BYDAY=-1SU
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:16010101T020000
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=3;BYDAY=-1SU
END:DAYLIGHT
END:VTIMEZONE
BEGIN:VEVENT
DTSTAMP:20120416T092149Z
DTSTART;TZID="foo":20120418T1
 00000
SUMMARY:Begin Unterhaltsreinigung
UID:040000008200E00074C5B7101A82E0080000000010DA091DC31BCD01000000000000000
 0100000008FECD2E607780649BE5A4C9EE6418CBC
DTEND;TZID="Sarajevo, Skopje, Sofija, Vilnius, Warsaw, Zagreb":20120418T103
 000
END:VEVENT
END:VCALENDAR
HI;

        $tz = TimeZoneUtil::getTimeZone('foo', Reader::read($vobj));
        $ex = new \DateTimeZone(date_default_timezone_get());
        $this->assertEquals($ex->getName(), $tz->getName());

    }

    function testWindowsTimeZone() {

        $tz = TimeZoneUtil::getTimeZone('Eastern Standard Time');
        $ex = new \DateTimeZone('America/New_York');
        $this->assertEquals($ex->getName(), $tz->getName());

    }

    function testTimezoneOffset() {

        $tz = TimeZoneUtil::getTimeZone('GMT-0400');
        $ex = new \DateTimeZone('Etc/GMT-4');
        $this->assertEquals($ex->getName(), $tz->getName());

    }

    function testFallBack() {

        $vobj = <<<HI
BEGIN:VCALENDAR
METHOD:REQUEST
VERSION:2.0
BEGIN:VTIMEZONE
TZID:foo
BEGIN:STANDARD
DTSTART:16010101T030000
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=10;BYDAY=-1SU
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:16010101T020000
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=3;BYDAY=-1SU
END:DAYLIGHT
END:VTIMEZONE
BEGIN:VEVENT
DTSTAMP:20120416T092149Z
DTSTART;TZID="foo":20120418T1
 00000
SUMMARY:Begin Unterhaltsreinigung
UID:040000008200E00074C5B7101A82E0080000000010DA091DC31BCD01000000000000000
 0100000008FECD2E607780649BE5A4C9EE6418CBC
 000
END:VEVENT
END:VCALENDAR
HI;
        $tz = TimeZoneUtil::getTimeZone('foo', Reader::read($vobj));
        $this->assertEquals(new \DateTimeZone(date_default_timezone_get()), $tz);

    }

}
