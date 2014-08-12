<?php

namespace Sabre\VObject\ITip;

class BrokerUpdateTest extends \PHPUnit_Framework_TestCase {

    function testInviteChange() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
ATTENDEE;CN=Two:mailto:two@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;


        $newMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=Two:mailto:two@example.org
ATTENDEE;CN=Three:mailto:three@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;

        $version = \Sabre\VObject\Version::VERSION;

        $expected = array(
            array(
                'uid' => 'foobar',
                'method' => 'CANCEL',
                'component' => 'VEVENT',
                'sender' => 'mailto:strunk@example.org',
                'senderName' => 'Strunk',
                'recipient' => 'mailto:one@example.org',
                'recipientName' => 'One',
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:CANCEL
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
ATTENDEE;CN=One:mailto:one@example.org
ORGANIZER;CN=Strunk:mailto:strunk@example.org
END:VEVENT
END:VCALENDAR
ICS

            ),
            array(
                'uid' => 'foobar',
                'method' => 'REQUEST',
                'component' => 'VEVENT',
                'sender' => 'mailto:strunk@example.org',
                'senderName' => 'Strunk',
                'recipient' => 'mailto:two@example.org',
                'recipientName' => 'Two',
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=Two:mailto:two@example.org
ATTENDEE;CN=Three:mailto:three@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS

            ),
            array(
                'uid' => 'foobar',
                'method' => 'REQUEST',
                'component' => 'VEVENT',
                'sender' => 'mailto:strunk@example.org',
                'senderName' => 'Strunk',
                'recipient' => 'mailto:three@example.org',
                'recipientName' => 'Three',
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=Two:mailto:two@example.org
ATTENDEE;CN=Three:mailto:three@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS

            ),
        );

        $result = $this->parse($oldMessage, $newMessage, $expected);

    }

    function testInviteChangeFromNonSchedulingToSchedulingObject() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;


        $newMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;

        $version = \Sabre\VObject\Version::VERSION;

        $expected = array(
            array(
                'uid' => 'foobar',
                'method' => 'REQUEST',
                'component' => 'VEVENT',
                'sender' => 'mailto:strunk@example.org',
                'senderName' => 'Strunk',
                'recipient' => 'mailto:one@example.org',
                'recipientName' => 'One',
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS

            ),

        );

        $result = $this->parse($oldMessage, $newMessage, $expected);

    }

    function testNoAttendees() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;


        $newMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;

        $version = \Sabre\VObject\Version::VERSION;

        $expected = array();
        $result = $this->parse($oldMessage, $newMessage, $expected);

    }

    function testRemoveInstance() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
DTSTART;TZID=America/Toronto:20140716T120000
RRULE:FREQ=WEEKLY
END:VEVENT
END:VCALENDAR
ICS;


        $newMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
DTSTART;TZID=America/Toronto:20140716T120000
RRULE:FREQ=WEEKLY
EXDATE;TZID=America/Toronto:20140724T120000
END:VEVENT
END:VCALENDAR
ICS;

        $version = \Sabre\VObject\Version::VERSION;

        $expected = array(
            array(
                'uid' => 'foobar',
                'method' => 'REQUEST',
                'component' => 'VEVENT',
                'sender' => 'mailto:strunk@example.org',
                'senderName' => 'Strunk',
                'recipient' => 'mailto:one@example.org',
                'recipientName' => 'One',
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
DTSTART;TZID=America/Toronto:20140716T120000
RRULE:FREQ=WEEKLY
EXDATE;TZID=America/Toronto:20140724T120000
END:VEVENT
END:VCALENDAR
ICS

            ),
        );

        $result = $this->parse($oldMessage, $newMessage, $expected);

    }

    function parse($oldMessage, $newMessage, $expected = array()) {

        $broker = new Broker();
        $result = $broker->parseEvent($newMessage, 'mailto:strunk@example.org', $oldMessage);

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
