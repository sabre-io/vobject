<?php

namespace Sabre\VObject\ITip;

class BrokerDeleteEventTest extends \PHPUnit_Framework_TestCase {

    function testOrganizerDelete() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
ATTENDEE;CN=Two:mailto:two@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;


        $newMessage = null;

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
                'method' => 'CANCEL',
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
METHOD:CANCEL
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
ATTENDEE;CN=Two:mailto:two@example.org
ORGANIZER;CN=Strunk:mailto:strunk@example.org
END:VEVENT
END:VCALENDAR
ICS

            ),
        );

        $result = $this->parse($oldMessage, $newMessage, $expected, 'mailto:strunk@example.org');

    }

    function testAttendeeDelete() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
ATTENDEE;CN=Two:mailto:two@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;


        $newMessage = null;

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
SEQUENCE:2
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=DECLINED;CN=One:mailto:one@example.org
END:VEVENT
END:VCALENDAR
ICS
            ),
        );

        $result = $this->parse($oldMessage, $newMessage, $expected, 'mailto:one@example.org');


    }

    function testNoCalendar() {

        $this->parse(null, null, array(), 'mailto:one@example.org');

    }

    function testVTodo() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VTODO
UID:foobar
SEQUENCE:1
END:VTODO
END:VCALENDAR
ICS;
        $this->parse($oldMessage, null, array(), 'mailto:one@example.org');

    }

    function parse($oldMessage, $newMessage, $expected = array(), $currentUser) {

        $broker = new Broker();
        $result = $broker->parseEvent($newMessage, $currentUser, $oldMessage);

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
