<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class TimeZoneUtilTest extends TestCase
{
    public function setUp(): void
    {
        TimeZoneUtil::clean();
    }

    /**
     * @dataProvider getMapping
     */
    public function testCorrectTZ(string $timezoneName): void
    {
        try {
            $tz = new \DateTimeZone($timezoneName);
            self::assertInstanceOf('DateTimeZone', $tz);
        } catch (\Exception $e) {
            if (false !== strpos($e->getMessage(), 'Unknown or bad timezone')) {
                $this->markTestSkipped($timezoneName.' is not (yet) supported in this PHP version. Update pecl/timezonedb');
            } else {
                throw $e;
            }
        }
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function getMapping(): array
    {
        $map = array_merge(
            include __DIR__.'/../../lib/timezonedata/windowszones.php',
            include __DIR__.'/../../lib/timezonedata/lotuszones.php',
            include __DIR__.'/../../lib/timezonedata/exchangezones.php',
            include __DIR__.'/../../lib/timezonedata/php-workaround.php'
        );

        // PHPUNit requires an array of arrays
        return array_map(
            function ($value) {
                return [$value];
            },
            $map
        );
    }

    public function testExchangeMap(): void
    {
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
 000
END:VEVENT
END:VCALENDAR
HI;

        $tz = TimeZoneUtil::getTimeZone('foo', Reader::read($vobj));
        $ex = new \DateTimeZone('Europe/Lisbon');

        self::assertEquals($ex->getName(), $tz->getName());
    }

    public function testWhetherMicrosoftIsStillInsane(): void
    {
        $vobj = <<<HI
BEGIN:VCALENDAR
METHOD:REQUEST
VERSION:2.0
BEGIN:VTIMEZONE
TZID:(GMT+01.00) Sarajevo/Warsaw/Zagreb
X-MICROSOFT-CDO-TZID:2
BEGIN:STANDARD
DTSTART:16010101T030000
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
RRULE:FREQ=YEARLY;WKST=MO;INTERVAL=1;BYMONTH=10;BYDAY=-1SU
END:STANDARD
END:VTIMEZONE
END:VCALENDAR
HI;

        $tz = TimeZoneUtil::getTimeZone('(GMT+01.00) Sarajevo/Warsaw/Zagreb', Reader::read($vobj));
        $ex = new \DateTimeZone('Europe/Sarajevo');

        self::assertEquals($ex->getName(), $tz->getName());
    }

    public function testUnknownExchangeId(): void
    {
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
        self::assertEquals($ex->getName(), $tz->getName());
    }

    public function testEmptyTimeZone(): void
    {
        $tz = TimeZoneUtil::getTimeZone('');
        $ex = new \DateTimeZone('UTC');
        self::assertEquals($ex->getName(), $tz->getName());
    }

    public function testWindowsTimeZone(): void
    {
        $tz = TimeZoneUtil::getTimeZone('Eastern Standard Time');
        $ex = new \DateTimeZone('America/New_York');
        self::assertEquals($ex->getName(), $tz->getName());
    }

    /**
     * @dataProvider getPHPTimeZoneIdentifiers
     */
    public function testTimeZoneIdentifiers(string $tzid): void
    {
        $tz = TimeZoneUtil::getTimeZone($tzid);
        $ex = new \DateTimeZone($tzid);

        self::assertEquals($ex->getName(), $tz->getName());
    }

    /**
     * @dataProvider getPHPTimeZoneBCIdentifiers
     */
    public function testTimeZoneBCIdentifiers(string $tzid): void
    {
        /*
         * A regression was introduced in PHP 8.1.14 and 8.2.1
         * Timezone ids containing a "+" like "GMT+10" do not work.
         * See https://github.com/php/php-src/issues/10218
         * The regression should be fixed in the next patch releases of PHP
         * that should be released in Feb 2023.
         */
        $versionOfPHP = \phpversion();
        if ((('8.1.14' == $versionOfPHP) || ('8.2.1' == $versionOfPHP)) && \str_contains($tzid, '+')) {
            $this->markTestSkipped("Timezone ids containing '+' do not work on PHP $versionOfPHP");
        }
        $tz = TimeZoneUtil::getTimeZone($tzid);
        $ex = new \DateTimeZone($tzid);

        self::assertEquals($ex->getName(), $tz->getName());
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function getPHPTimeZoneIdentifiers(): array
    {
        // PHPUNit requires an array of arrays
        return array_map(
            function ($value) {
                return [$value];
            },
            \DateTimeZone::listIdentifiers()
        );
    }

    /**
     * @return array<int, array<int, string>>
     */
    public function getPHPTimeZoneBCIdentifiers(): array
    {
        // PHPUnit requires an array of arrays
        return array_map(
            function ($value) {
                return [$value];
            },
            include __DIR__.'/../../lib/timezonedata/php-bc.php'
        );
    }

    public function testTimezoneOffset(): void
    {
        $tz = TimeZoneUtil::getTimeZone('GMT-0400', null, true);

        if (version_compare(PHP_VERSION, '5.5.10', '>=') && !defined('HHVM_VERSION')) {
            $ex = new \DateTimeZone('-04:00');
        } else {
            $ex = new \DateTimeZone('Etc/GMT-4');
        }
        self::assertEquals($ex->getName(), $tz->getName());
    }

    public function testTimezoneFail(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        TimeZoneUtil::getTimeZone('FooBar', null, true);
    }

    public function testFallBack(): void
    {
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
        $ex = new \DateTimeZone(date_default_timezone_get());
        self::assertEquals($ex->getName(), $tz->getName());
    }

    public function testLjubljanaBug(): void
    {
        $vobj = <<<HI
BEGIN:VCALENDAR
CALSCALE:GREGORIAN
PRODID:-//Ximian//NONSGML Evolution Calendar//EN
VERSION:2.0
BEGIN:VTIMEZONE
TZID:/freeassociation.sourceforge.net/Tzfile/Europe/Ljubljana
X-LIC-LOCATION:Europe/Ljubljana
BEGIN:STANDARD
TZNAME:CET
DTSTART:19701028T030000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=10
TZOFFSETFROM:+0200
TZOFFSETTO:+0100
END:STANDARD
BEGIN:DAYLIGHT
TZNAME:CEST
DTSTART:19700325T020000
RRULE:FREQ=YEARLY;BYDAY=-1SU;BYMONTH=3
TZOFFSETFROM:+0100
TZOFFSETTO:+0200
END:DAYLIGHT
END:VTIMEZONE
BEGIN:VEVENT
UID:foo
DTSTART;TZID=/freeassociation.sourceforge.net/Tzfile/Europe/Ljubljana:
 20121003T080000
DTEND;TZID=/freeassociation.sourceforge.net/Tzfile/Europe/Ljubljana:
 20121003T083000
TRANSP:OPAQUE
SEQUENCE:2
SUMMARY:testing
CREATED:20121002T172613Z
LAST-MODIFIED:20121002T172613Z
END:VEVENT
END:VCALENDAR

HI;

        $tz = TimeZoneUtil::getTimeZone('/freeassociation.sourceforge.net/Tzfile/Europe/Ljubljana', Reader::read($vobj));
        $ex = new \DateTimeZone('Europe/Ljubljana');
        self::assertEquals($ex->getName(), $tz->getName());
    }

    public function testWeirdSystemVLICs(): void
    {
        $vobj = <<<HI
BEGIN:VCALENDAR
CALSCALE:GREGORIAN
PRODID:-//Ximian//NONSGML Evolution Calendar//EN
VERSION:2.0
BEGIN:VTIMEZONE
TZID:/freeassociation.sourceforge.net/Tzfile/SystemV/EST5EDT
X-LIC-LOCATION:SystemV/EST5EDT
BEGIN:STANDARD
TZNAME:EST
DTSTART:19701104T020000
RRULE:FREQ=YEARLY;BYDAY=1SU;BYMONTH=11
TZOFFSETFROM:-0400
TZOFFSETTO:-0500
END:STANDARD
BEGIN:DAYLIGHT
TZNAME:EDT
DTSTART:19700311T020000
RRULE:FREQ=YEARLY;BYDAY=2SU;BYMONTH=3
TZOFFSETFROM:-0500
TZOFFSETTO:-0400
END:DAYLIGHT
END:VTIMEZONE
BEGIN:VEVENT
UID:20121026T021107Z-6301-1000-1-0@chAir
DTSTAMP:20120905T172126Z
DTSTART;TZID=/freeassociation.sourceforge.net/Tzfile/SystemV/EST5EDT:
 20121026T153000
DTEND;TZID=/freeassociation.sourceforge.net/Tzfile/SystemV/EST5EDT:
 20121026T160000
TRANSP:OPAQUE
SEQUENCE:5
SUMMARY:pick up Ibby
CLASS:PUBLIC
CREATED:20121026T021108Z
LAST-MODIFIED:20121026T021118Z
X-EVOLUTION-MOVE-CALENDAR:1
END:VEVENT
END:VCALENDAR
HI;

        $tz = TimeZoneUtil::getTimeZone('/freeassociation.sourceforge.net/Tzfile/SystemV/EST5EDT', Reader::read($vobj), true);
        $ex = new \DateTimeZone('America/New_York');
        self::assertEquals($ex->getName(), $tz->getName());
    }

    public function testPrefixedOffsetExchangeIdentifier(): void
    {
        $tz = TimeZoneUtil::getTimeZone('(UTC-05:00) Eastern Time (US & Canada)');
        $ex = new \DateTimeZone('America/New_York');
        self::assertEquals($ex->getName(), $tz->getName());
    }
}
