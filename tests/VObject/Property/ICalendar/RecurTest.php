<?php

namespace Sabre\VObject\Property\ICalendar;

use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Reader;

class RecurTest extends \PHPUnit_Framework_TestCase {

    function testParts() {

        $vcal = new VCalendar();
        $recur = $vcal->add('RRULE', 'FREQ=Daily');

        $this->assertInstanceOf('Sabre\VObject\Property\ICalendar\Recur', $recur);

        $this->assertEquals(['FREQ' => 'DAILY'], $recur->getParts());
        $recur->setParts(['freq'    => 'MONTHLY']);

        $this->assertEquals(['FREQ' => 'MONTHLY'], $recur->getParts());

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testSetValueBadVal() {

        $vcal = new VCalendar();
        $recur = $vcal->add('RRULE', 'FREQ=Daily');
        $recur->setValue(new \Exception());

    }

    function testSetSubParts() {

        $vcal = new VCalendar();
        $recur = $vcal->add('RRULE', ['FREQ' => 'DAILY', 'BYDAY' => 'mo,tu', 'BYMONTH' => [0, 1]]);

        $this->assertEquals([
            'FREQ'    => 'DAILY',
            'BYDAY'   => ['MO', 'TU'],
            'BYMONTH' => [0, 1],
        ], $recur->getParts());

    }

    function testGetJSONWithUntil() {
        $input = 'BEGIN:VCALENDAR
BEGIN:VEVENT
UID:908d53c0-e1a3-4883-b69f-530954d6bd62
TRANSP:OPAQUE
DTSTART;TZID=Europe/Berlin:20160301T150000
DTEND;TZID=Europe/Berlin:20160301T170000
SUMMARY:test
RRULE:FREQ=DAILY;UNTIL=20160305T230000Z
ORGANIZER;CN=robert pipo:mailto:robert@pipo.com
END:VEVENT
END:VCALENDAR
';

        $vcal = Reader::read($input);
        $rrule = $vcal->VEVENT->RRULE;
        $untilJsonString = $rrule->getJsonValue()[0]['until'];
        $this->assertEquals('2016-03-05T23:00:00Z', $untilJsonString);
    }
}
