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
SUMMARY:foo
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
SUMMARY:foo
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
                'significantChange' => true,
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:CANCEL
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
SUMMARY:foo
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
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
                'significantChange' => false,
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
SUMMARY:foo
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
                'significantChange' => true,
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
SUMMARY:foo
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

    /**
     * This test is identical to the first test, except this time we change the
     * DURATION property.
     *
     * This should ensure that the message is significant for every attendee,
     */
    function testInviteChangeSignificantChange() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
DURATION:PT1H
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
DURATION:PT2H
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
                'significantChange' => true,
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:CANCEL
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
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
                'significantChange' => true,
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
UID:foobar
DURATION:PT2H
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
                'significantChange' => true,
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
UID:foobar
DURATION:PT2H
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

    function testInviteNoChange() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=Strunk:mailto:strunk@example.org
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
SEQUENCE:2
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=Strunk:mailto:strunk@example.org
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
                'significantChange' => false,
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
ATTENDEE;CN=One:mailto:one@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS

            ),

        );

        $result = $this->parse($oldMessage, $newMessage, $expected);

    }

    function testInviteNoChangeForceSend() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=Strunk:mailto:strunk@example.org
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
SEQUENCE:2
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=Strunk:mailto:strunk@example.org
ATTENDEE;SCHEDULE-FORCE-SEND=REQUEST;CN=One:mailto:one@example.org
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
                'significantChange' => true,
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
ATTENDEE;CN=One:mailto:one@example.org
DTSTART:20140716T120000Z
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

    function testInviteRemoveAttendees() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
SUMMARY:foo
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
SUMMARY:foo
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
                'significantChange' => true,
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:CANCEL
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
SUMMARY:foo
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=One:mailto:one@example.org
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
                'significantChange' => true,
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:CANCEL
BEGIN:VEVENT
UID:foobar
SEQUENCE:2
SUMMARY:foo
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;CN=Two:mailto:two@example.org
END:VEVENT
END:VCALENDAR
ICS

            ),
        );

        $result = $this->parse($oldMessage, $newMessage, $expected);

    }
}
