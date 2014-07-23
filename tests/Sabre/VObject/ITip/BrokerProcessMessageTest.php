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

    function testUnsupportedComponent() {

        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VTODO
SEQUENCE:2
UID:foobar
END:VTODO
END:VCALENDAR
ICS;

        $old = null;
        $expected = null;

        $result = $this->process($itip, $old, $expected);

    }

    function testUnsupportedMethod() {

        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:PUBLISH
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

    function testReplyNoOriginal() {

        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:REPLY
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
ATTENDEE;PARTSTAT=ACCEPTED:mailto:foo@example.org
ORGANIZER:mailto:bar@example.org
END:VEVENT
END:VCALENDAR
ICS;

        $old = null;
        $expected = null;

        $result = $this->process($itip, $old, $expected);

    }

    function testReplyAccept() {

        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:REPLY
BEGIN:VEVENT
ATTENDEE;PARTSTAT=ACCEPTED:mailto:foo@example.org
ORGANIZER:mailto:bar@example.org
SEQUENCE:2
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $old = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
ATTENDEE:mailto:foo@example.org
ORGANIZER:mailto:bar@example.org
END:VEVENT
END:VCALENDAR
ICS;

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
ATTENDEE;PARTSTAT=ACCEPTED:mailto:foo@example.org
ORGANIZER:mailto:bar@example.org
END:VEVENT
END:VCALENDAR
ICS;

        $result = $this->process($itip, $old, $expected);

    }

    function process($input, $existingObject = null, $expected = false) {

        $version = \Sabre\VObject\Version::VERSION;

        $vcal = Reader::read($input);

        $mainComponent = $vcal->getBaseComponent();

        $message = new Message();
        $message->message = $vcal;
        $message->method = isset($vcal->METHOD)?$vcal->METHOD->getValue():null;
        $message->component = $mainComponent->name;
        $message->sequence = isset($vcal->VEVENT[0])?$vcal->VEVENT[0]->SEQUENCE:null;

        if ($message->method === 'REPLY') {

            $message->sender = $mainComponent->ATTENDEE->getValue();
            $message->senderName = isset($mainComponent->ATTENDEE['CN'])?$mainComponent->ATTENDEE['CN']:null;
            $message->recipient = $mainComponent->ORGANIZER->getValue();
            $message->senderName = isset($mainComponent->ORGANIZER['CN'])?$mainComponent->ORGANIZER['CN']:null;

        }

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
