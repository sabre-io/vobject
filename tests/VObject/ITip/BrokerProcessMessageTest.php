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

    function testReplyPartyCrasher() {

        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:REPLY
BEGIN:VEVENT
ATTENDEE;PARTSTAT=ACCEPTED:mailto:crasher@example.org
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
ATTENDEE:mailto:foo@example.org
ORGANIZER:mailto:bar@example.org
ATTENDEE;PARTSTAT=ACCEPTED:mailto:crasher@example.org
END:VEVENT
END:VCALENDAR
ICS;

        $result = $this->process($itip, $old, $expected);

    }

    function testReplyNewException() {

        // This is a reply to 1 instance of a recurring event. This should
        // automatically create an exception.
        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:REPLY
BEGIN:VEVENT
ATTENDEE;PARTSTAT=ACCEPTED:mailto:foo@example.org
ORGANIZER:mailto:bar@example.org
SEQUENCE:2
RECURRENCE-ID:20140725T000000Z
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
RRULE:FREQ=DAILY
DTSTART:20140724T000000Z
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
RRULE:FREQ=DAILY
DTSTART:20140724T000000Z
ATTENDEE:mailto:foo@example.org
ORGANIZER:mailto:bar@example.org
END:VEVENT
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
DTSTART:20140725T000000Z
ATTENDEE;PARTSTAT=ACCEPTED:mailto:foo@example.org
ORGANIZER:mailto:bar@example.org
RECURRENCE-ID:20140725T000000Z
END:VEVENT
END:VCALENDAR
ICS;

        $result = $this->process($itip, $old, $expected);

    }

    function testReplyNewExceptionTz() {

        // This is a reply to 1 instance of a recurring event. This should
        // automatically create an exception.
        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:REPLY
BEGIN:VEVENT
ATTENDEE;PARTSTAT=ACCEPTED:mailto:foo@example.org
ORGANIZER:mailto:bar@example.org
SEQUENCE:2
RECURRENCE-ID;TZID=America/Toronto:20140725T000000
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
RRULE:FREQ=DAILY
DTSTART;TZID=America/Toronto:20140724T000000
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
RRULE:FREQ=DAILY
DTSTART;TZID=America/Toronto:20140724T000000
ATTENDEE:mailto:foo@example.org
ORGANIZER:mailto:bar@example.org
END:VEVENT
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
DTSTART;TZID=America/Toronto:20140725T000000
ATTENDEE;PARTSTAT=ACCEPTED:mailto:foo@example.org
ORGANIZER:mailto:bar@example.org
RECURRENCE-ID;TZID=America/Toronto:20140725T000000
END:VEVENT
END:VCALENDAR
ICS;

        $result = $this->process($itip, $old, $expected);

    }

    function testReplyPartyCrashCreateExcepton() {

        // IN this test there's a recurring event that has an exception. The
        // exception is missing the attendee.
        //
        // The attendee party crashes the instance, so it should show up in the
        // resulting object.
        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:REPLY
BEGIN:VEVENT
ATTENDEE;PARTSTAT=ACCEPTED;CN=Crasher!:mailto:crasher@example.org
ORGANIZER:mailto:bar@example.org
SEQUENCE:2
RECURRENCE-ID:20140725T000000Z
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
RRULE:FREQ=DAILY
DTSTART:20140724T000000Z
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
RRULE:FREQ=DAILY
DTSTART:20140724T000000Z
ORGANIZER:mailto:bar@example.org
END:VEVENT
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
DTSTART:20140725T000000Z
ORGANIZER:mailto:bar@example.org
RECURRENCE-ID:20140725T000000Z
ATTENDEE;PARTSTAT=ACCEPTED;CN=Crasher!:mailto:crasher@example.org
END:VEVENT
END:VCALENDAR
ICS;

        $result = $this->process($itip, $old, $expected);

    }

    function testReplyNewExceptionNoMasterEvent() {

        /**
         * This iTip message would normally create a new exception, but the
         * server is not able to create this new instance, because there's no
         * master event to clone from.
         *
         * This test checks if the message is ignored.
         */
        $itip = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
METHOD:REPLY
BEGIN:VEVENT
ATTENDEE;PARTSTAT=ACCEPTED;CN=Crasher!:mailto:crasher@example.org
ORGANIZER:mailto:bar@example.org
SEQUENCE:2
RECURRENCE-ID:20140725T000000Z
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
RRULE:FREQ=DAILY
DTSTART:20140724T000000Z
RECURRENCE-ID:20140724T000000Z
ORGANIZER:mailto:bar@example.org
END:VEVENT
END:VCALENDAR
ICS;

        $expected = null;
        $result = $this->process($itip, $old, $expected);

    }
    function process($input, $existingObject = null, $expected = false) {

        $version = \Sabre\VObject\Version::VERSION;

        $vcal = Reader::read($input);

        foreach($vcal->getComponents() as $mainComponent) {
            break;
        }

        $message = new Message();
        $message->message = $vcal;
        $message->method = isset($vcal->METHOD)?$vcal->METHOD->getValue():null;
        $message->component = $mainComponent->name;
        $message->uid = $mainComponent->uid->getValue();
        $message->sequence = isset($vcal->VEVENT[0])?(string)$vcal->VEVENT[0]->SEQUENCE:null;

        if ($message->method === 'REPLY') {

            $message->sender = $mainComponent->ATTENDEE->getValue();
            $message->senderName = isset($mainComponent->ATTENDEE['CN'])?$mainComponent->ATTENDEE['CN']->getValue():null;
            $message->recipient = $mainComponent->ORGANIZER->getValue();
            $message->recipientName = isset($mainComponent->ORGANIZER['CN'])?$mainComponent->ORGANIZER['CN']:null;

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
