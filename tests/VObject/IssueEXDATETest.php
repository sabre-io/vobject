<?php

namespace Sabre\VObject;

use
    DateTime,
    DateTimeZone;

class IssueEXDATETest extends \PHPUnit_Framework_TestCase {

    function testRecurrence() {

        $input = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
DTSTART;TZID=Europe/Berlin:20130329T140000
DTEND;TZID=Europe/Berlin:20130329T153000
RRULE:FREQ=WEEKLY;BYDAY=FR;UNTIL=20130412T115959Z
EXDATE;TZID=Europe/Berlin:20130405T140000
EXDATE;TZID=Europe/Berlin:20130329T140000
DTSTAMP:20140916T201215Z
UID:foo
SEQUENCE:1
SUMMARY:foo
END:VEVENT
END:VCALENDAR
ICS;

        $vcal = Reader::read($input);
        $this->assertInstanceOf('Sabre\\VObject\\Component\\VCalendar', $vcal);

        $it = new Recur\EventIterator($vcal, 'foo');

        $end = $it->getDtEnd();

        while($it->valid()) {
            $end = $it->getDtEnd();
            $it->next();
        }

        $end->getTimeStamp();

    }

}
