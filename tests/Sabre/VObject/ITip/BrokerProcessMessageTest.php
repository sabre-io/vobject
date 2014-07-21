<?php

namespace Sabre\VObject\ITip;

use Sabre\VObject\Reader;

class BrokerProcessMessageTest extends \PHPUnit_Framework_TestCase {

    function testRequestNew() {

        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:REQUEST
BEGIN:VEVENT
SEQUENCE:1
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $expected = <<<ICS
BEGIN:VCALENDAR
%foo%
BEGIN:VEVENT
SEQUENCE:1
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $result = $this->process($itip, null, $expected);

    }
    function testRequestUpdate() {

        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:REQUEST
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $old = <<<ICS
BEGIN:VCALENDAR
%foo%
BEGIN:VEVENT
SEQUENCE:1
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $expected = <<<ICS
BEGIN:VCALENDAR
%foo%
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $result = $this->process($itip, $old, $expected);

    }

    function testCancel() {

        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:CANCEL
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $old = <<<ICS
BEGIN:VCALENDAR
%foo%
BEGIN:VEVENT
SEQUENCE:1
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $expected = <<<ICS
BEGIN:VCALENDAR
%foo%
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
STATUS:CANCELLED
END:VEVENT
END:VCALENDAR
ICS;

        $result = $this->process($itip, $old, $expected);

    }

    function testCancelNoExistingEvent() {

        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:CANCEL
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $old = null;
        $expected = null;

        $result = $this->process($itip, $old, $expected);

    }

    function process($input, $existingObject = null, $expected = false) {

        $version = \Sabre\VObject\Version::VERSION;

        $vcal = Reader::read($input);
        $message = new Message();
        $message->message = $vcal;
        $message->method = $vcal->METHOD;
        $message->component = 'VEVENT';
        $message->sequence = $vcal->VEVENT[0]->SEQUENCE;
        $broker = new Broker();

        if (is_string($existingObject)) {
            $existingObject = str_replace(
                '%foo%',
                "VERSION:2.0\nPRODID:-//Sabre//Sabre VObject $version//EN\nCALSCALE:GREGORIAN",
                $existingObject
            );
            $existingObject = Reader::read($existingObject);
        }

        $result = $broker->processMessage($message, $existingObject);

        if (is_string($expected)) {
            $expected = str_replace(
                '%foo%',
                "VERSION:2.0\nPRODID:-//Sabre//Sabre VObject $version//EN\nCALSCALE:GREGORIAN",
                $expected
            );
            $expected = str_replace("\n", "\r\n", $expected);

        }
        if ($result instanceof \Sabre\VObject\Component\VCalendar) {
            $result = $result->serialize();
            $result = rtrim($result,"\r\n");
        }

        $this->assertEquals(
            $expected,
            $result
        );

    }

}
