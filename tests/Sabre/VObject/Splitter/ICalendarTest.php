<?php

namespace Sabre\VObject;

class ICalendarSplitterTest extends \PHPUnit_Framework_TestCase {

    function createStream($data) {

        $stream = fopen('php://memory','r+');
        fwrite($stream, $data);
        rewind($stream);
        return $stream;

    }

    function testICalendarImportValidEvent() {

        $data = <<<EOT
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
END:VEVENT
END:VCALENDAR
EOT;
        $tempFile = $this->createStream($data);
        
        $objects = new Splitter\ICalendar($tempFile);

        $return = "";
        while($object=$objects->getNext()) {
            $return .= $object->serialize();
        }
        $this->assertEquals(array(), Reader::read($return)->validate());
    }

    function testICalendarImportEndOfData() {
        $data = <<<EOT
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
END:VEVENT
END:VCALENDAR
EOT;
        $tempFile = $this->createStream($data);
        
        $objects = new Splitter\ICalendar($tempFile);
        
        $return = "";
        while($object=$objects->getNext()) {
            $return .= $object->serialize();
        }
        $this->assertNull($object=$objects->getNext());
    }

    /**
     * @expectedException        Sabre\VObject\ParseException
     */
    function testICalendarImportInvalidEvent() {
        $data = <<<EOT
EOT;
        $tempFile = $this->createStream($data);
        
        $objects = new Splitter\ICalendar($tempFile);
    }

    function testICalendarImportMultipleValidEvents() {

        $data = <<<EOT
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foo
END:VEVENT
BEGIN:VEVENT
UID:foo
END:VEVENT
END:VCALENDAR
EOT;
        $tempFile = $this->createStream($data);
        
        $objects = new Splitter\ICalendar($tempFile);

        $return = "";
        while($object=$objects->getNext()) {
            $return .= $object->serialize();
        }
        $this->assertEquals(array(), Reader::read($return)->validate());
    }

    function testICalendarImportEventWithoutUID() {

        $data = <<<EOT
BEGIN:VCALENDAR
BEGIN:VEVENT
END:VEVENT
END:VCALENDAR
EOT;
        $tempFile = $this->createStream($data);
        
        $objects = new Splitter\ICalendar($tempFile);

        $return = "";
        while($object=$objects->getNext()) {
            $return .= $object->serialize();
        }

        $this->assertEquals(array(), Reader::read($return)->validate());
    }

    function testICalendarImportMultipleVTIMEZONESAndMultipleValidEvents() {

        $data = <<<EOT
BEGIN:VCALENDAR
BEGIN:VTIMEZONE
TZID:Europe/Berlin
BEGIN:DAYLIGHT
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
DTSTART:19810329T020000
TZNAME:MESZ
TZOFFSETTO:+0200
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0200
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
DTSTART:19961027T030000
TZNAME:MEZ
TZOFFSETTO:+0100
END:STANDARD
END:VTIMEZONE
BEGIN:VTIMEZONE
TZID:Europe/London
BEGIN:DAYLIGHT
TZOFFSETFROM:+0000
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
DTSTART:19810329T010000
TZNAME:GMT+01:00
TZOFFSETTO:+0100
END:DAYLIGHT
BEGIN:STANDARD
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
DTSTART:19961027T020000
TZNAME:GMT
TZOFFSETTO:+0000
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
UID:foo
END:VEVENT
BEGIN:VEVENT
UID:foo
END:VEVENT
BEGIN:VEVENT
UID:foo
END:VEVENT
BEGIN:VEVENT
UID:foo
END:VEVENT
BEGIN:VEVENT
UID:foo
END:VEVENT
END:VCALENDAR
EOT;
        $tempFile = $this->createStream($data);
        
        $objects = new Splitter\ICalendar($tempFile);

        $return = "";
        while($object=$objects->getNext()) {
            $return .= $object->serialize();
        }
        $this->assertTrue(array_key_exists("Europe/Berlin", $objects->vtimezones));
        $this->assertTrue(array_key_exists("Europe/London", $objects->vtimezones));
        
        $this->assertEquals(array(), Reader::read($return)->validate());
    }

    function testICalendarImportWithOutVTIMEZONES() {

        $data = <<<EOT
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Apple Inc.//Mac OS X 10.8//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
CREATED:20120605T072109Z
UID:D6716295-C10F-4B20-82F9-E1A3026C7DCF
DTEND;VALUE=DATE:20120717
TRANSP:TRANSPARENT
SUMMARY:Start Vorbereitung
DTSTART;VALUE=DATE:20120716
DTSTAMP:20120605T072115Z
SEQUENCE:2
BEGIN:VALARM
X-WR-ALARMUID:A99EDA6A-35EB-4446-B8BC-CDA3C60C627D
UID:A99EDA6A-35EB-4446-B8BC-CDA3C60C627D
TRIGGER:-PT15H
X-APPLE-DEFAULT-ALARM:TRUE
ATTACH;VALUE=URI:Basso
ACTION:AUDIO
END:VALARM
END:VEVENT
END:VCALENDAR
EOT;
        $tempFile = $this->createStream($data);
        
        $objects = new Splitter\ICalendar($tempFile);

        $return = "";
        while($object=$objects->getNext()) {
            $return .= $object->serialize();
        }

        $this->assertEquals(array(), Reader::read($return)->validate());
    }

}
