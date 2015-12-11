<?php

namespace Sabre\VObject\Recur\EventIterator;

use DateTime;
use DateTimeImmutable;
use DateTimeZone;
use Sabre\VObject\Reader;

/**
 * This is a unittest for Issue #53.
 */
class HandleRDateExpandTest extends \PHPUnit_Framework_TestCase {

    function testExpand() {

        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:2CD5887F7CF4600F7A3B1F8065099E40-240BDA7121B61224
DTSTAMP;VALUE=DATE-TIME:20151014T110604Z
CREATED;VALUE=DATE-TIME:20151014T110245Z
LAST-MODIFIED;VALUE=DATE-TIME:20151014T110541Z
DTSTART;VALUE=DATE-TIME;TZID=Europe/Berlin:20151012T020000
DTEND;VALUE=DATE-TIME;TZID=Europe/Berlin:20151012T013000
SUMMARY:Test
SEQUENCE:2
RDATE;VALUE=DATE-TIME;TZID=Europe/Berlin:20151015T020000,20151017T020000,20
 151018T020000,20151020T020000
TRANSP:OPAQUE
CLASS:PUBLIC
END:VEVENT
END:VCALENDAR
ICS;

        $vcal = Reader::read($input);
        $this->assertInstanceOf('Sabre\\VObject\\Component\\VCalendar', $vcal);

        $vcal = $vcal->expand(new DateTime('2015-01-01'), new DateTime('2015-12-01'));

        $result = iterator_to_array($vcal->vevent);

        $this->assertEquals(5, count($result));

        $utc = new DateTimeZone('UTC');
        $expected = [
            new DateTimeImmutable("2015-10-12", $utc),
            new DateTimeImmutable("2015-10-15", $utc),
            new DateTimeImmutable("2015-10-17", $utc),
            new DateTimeImmutable("2015-10-18", $utc),
            new DateTimeImmutable("2015-10-20", $utc),
        ];

        $result = array_map(function($ev) {return $ev->dtstart->getDateTime();}, $result);
        $this->assertEquals($expected, $result);

    }


    function testExpandRDateWithoutTime() {
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:BDF720338B3EC8D2C09CE95CFF85C0B1-A26036CE0B219773
DTSTAMP;VALUE=DATE-TIME:20151211T121402Z
CREATED;VALUE=DATE-TIME:20151013T061627Z
LAST-MODIFIED;VALUE=DATE-TIME:20151014T093742Z
DTSTART;VALUE=DATE-TIME;TZID=Europe/Berlin:20151028T124500
DTEND;VALUE=DATE-TIME;TZID=Europe/Berlin:20151028T141500
SUMMARY:test summary
DESCRIPTION:test desc
SEQUENCE:1
RDATE;VALUE=DATE:20151028,20151111,20151125,20151209,20160113,20160120,2016
 0127
TRANSP:OPAQUE
CLASS:PUBLIC
ORGANIZER;CN=:mailto:sb@somewhere.net
END:VEVENT
END:VCALENDAR
ICS;

        $vcal = Reader::read($input);

        $this->assertInstanceOf('Sabre\\VObject\\Component\\VCalendar', $vcal);

        $vcal = $vcal->expand(new DateTime('2015-01-01'), new DateTime('2016-12-01'));

        $result = iterator_to_array($vcal->vevent);

        $this->assertEquals(7, count($result));

        $utc = new DateTimeZone('UTC');
        $expected = [
            new DateTimeImmutable("2015-10-28T11:45", $utc),
            new DateTimeImmutable("2015-11-11T11:45", $utc),
            new DateTimeImmutable("2015-11-25T11:45", $utc),
            new DateTimeImmutable("2015-12-09T11:45", $utc),
            new DateTimeImmutable("2016-01-13T11:45", $utc),
            new DateTimeImmutable("2016-01-20T11:45", $utc),
            new DateTimeImmutable("2016-01-27T11:45", $utc),
        ];

        $result = array_map(function($ev) {return $ev->dtstart->getDateTime();}, $result);

        $this->assertEquals($expected, $result);

    }

    function testExpandRdateExdateAndRrule() {
        /*
         * This is leagal according to the specs. It should:
         * http://www.kanzaki.com/docs/ical/rdate.html
         * 1. expand rrule and rdate
         * 2. build union
         * 3. remove and exdate
         */

        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:BDF720338B3EC8D2C09CE95CFF85C0B1-A26036CE0B219773
DTSTAMP;VALUE=DATE-TIME:20151211T121402Z
CREATED;VALUE=DATE-TIME:20151013T061627Z
LAST-MODIFIED;VALUE=DATE-TIME:20151014T093742Z
DTSTART;VALUE=DATE-TIME;TZID=Europe/Berlin:20151028T124500
DTEND;VALUE=DATE-TIME;TZID=Europe/Berlin:20151028T141500
SUMMARY:test summary
DESCRIPTION:test desc
SEQUENCE:1
RRULE:FREQ=WEEKLY;INTERVAL=1;UNTIL=20160127T114500Z
EXDATE:20151104T114500Z
EXDATE:20151118T114500Z
EXDATE:20151202T114500Z
EXDATE:20151216T114500Z
EXDATE:20151223T114500Z
EXDATE:20151230T114500Z
EXDATE:20160106T114500Z
RDATE;VALUE=DATE:20151028,20151111,20151125,20151209,20160113,20160120,2016
 0127
TRANSP:OPAQUE
CLASS:PUBLIC
ORGANIZER;CN=:mailto:sb@somewhere.net
END:VEVENT
END:VCALENDAR
ICS;

        $vcal = Reader::read($input);

        $this->assertInstanceOf('Sabre\\VObject\\Component\\VCalendar', $vcal);

        $vcal = $vcal->expand(new DateTime('2015-01-01'), new DateTime('2016-12-01'));

        $result = iterator_to_array($vcal->vevent);

        $this->assertEquals(7, count($result));

        $utc = new DateTimeZone('UTC');
        $expected = [
            new DateTimeImmutable("2015-10-28T11:45", $utc),
            new DateTimeImmutable("2015-11-11T11:45", $utc),
            new DateTimeImmutable("2015-11-25T11:45", $utc),
            new DateTimeImmutable("2015-12-09T11:45", $utc),
            new DateTimeImmutable("2016-01-13T11:45", $utc),
            new DateTimeImmutable("2016-01-20T11:45", $utc),
            new DateTimeImmutable("2016-01-27T11:45", $utc),
        ];


        $result = array_map(function($ev) {return $ev->dtstart->getDateTime();}, $result);

        $this->assertEquals($expected, $result);

    }

}
