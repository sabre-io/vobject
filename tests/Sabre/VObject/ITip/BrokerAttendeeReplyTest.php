<?php

namespace Sabre\VObject\ITip;

class BrokerAttendeeReplyTest extends \PHPUnit_Framework_TestCase {

    function testAccepted() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;


        $newMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=ACCEPTED;CN=One:mailto:one@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;

        $version = \Sabre\VObject\Version::VERSION;

        $expected = array(
            array(
                'uid' => 'foobar',
                'method' => 'REPLY',
                'component' => 'VEVENT',
                'sender' => 'mailto:one@example.org',
                'senderName' => 'One',
                'recipient' => 'mailto:strunk@example.org',
                'recipientName' => 'Strunk',
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:REPLY
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=ACCEPTED;CN=One:mailto:one@example.org
END:VEVENT
END:VCALENDAR
ICS

            ),

        );

        $result = $this->parse($oldMessage, $newMessage, $expected);

    }

    function testRecurringReply() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
DTSTART:20140724T120000Z
RRULE;FREQ=DAILY
END:VEVENT
END:VCALENDAR
ICS;


        $newMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=NEEDS-ACTION;CN=One:mailto:one@example.org
DTSTART:20140724T120000Z
END:VEVENT
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=ACCEPTED;CN=One:mailto:one@example.org
DTSTART:20140726T120000Z
RECURRENCE-ID:20140726T120000Z
END:VEVENT
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=DECLINED;CN=One:mailto:one@example.org
DTSTART:20140724T120000Z
RECURRENCE-ID:20140724T120000Z
END:VEVENT
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=TENTATIVE;CN=One:mailto:one@example.org
DTSTART:20140728T120000Z
RECURRENCE-ID:20140728T120000Z
END:VEVENT
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=ACCEPTED;CN=One:mailto:one@example.org
DTSTART:20140729T120000Z
RECURRENCE-ID:20140729T120000Z
END:VEVENT
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=DECLINED;CN=One:mailto:one@example.org
DTSTART:20140725T120000Z
RECURRENCE-ID:20140725T120000Z
END:VEVENT
END:VCALENDAR
ICS;

        $version = \Sabre\VObject\Version::VERSION;

        $expected = array(
            array(
                'uid' => 'foobar',
                'method' => 'REPLY',
                'component' => 'VEVENT',
                'sender' => 'mailto:one@example.org',
                'senderName' => 'One',
                'recipient' => 'mailto:strunk@example.org',
                'recipientName' => 'Strunk',
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:REPLY
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
RECURRENCE-ID:20140726T120000Z
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=ACCEPTED;CN=One:mailto:one@example.org
END:VEVENT
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
RECURRENCE-ID:20140724T120000Z
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=DECLINED;CN=One:mailto:one@example.org
END:VEVENT
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
RECURRENCE-ID:20140728T120000Z
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=TENTATIVE;CN=One:mailto:one@example.org
END:VEVENT
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
RECURRENCE-ID:20140729T120000Z
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=ACCEPTED;CN=One:mailto:one@example.org
END:VEVENT
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
RECURRENCE-ID:20140725T120000Z
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=DECLINED;CN=One:mailto:one@example.org
END:VEVENT
END:VCALENDAR
ICS

            ),

        );

        $result = $this->parse($oldMessage, $newMessage, $expected);

    }

    function testNoChange() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;


        $newMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=NEEDS-ACTION;CN=One:mailto:one@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;

        $expected = array();
        $result = $this->parse($oldMessage, $newMessage, $expected);

    }

    function parse($oldMessage, $newMessage, $expected = array()) {

        $broker = new Broker();
        $result = $broker->parseEvent($newMessage, 'mailto:one@example.org', $oldMessage);

        $this->assertEquals(count($expected), count($result));

        foreach($expected as $index=>$ex) {

            $message = $result[$index];

            foreach($ex as $key=>$val) {

                if ($key==='message') {
                    $this->assertEquals(
                        str_replace("\n", "\r\n", $val),
                        rtrim($message->message->serialize(), "\r\n")
                    );
                } else {
                    $this->assertEquals($val, $message->$key);
                }

            }

        }

    }

}
