<?php

namespace Sabre\VObject\ITip;

class BrokerAttendeeReplyTest extends BrokerTester {

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

    function testNoRelevantAttendee() {

        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
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
SEQUENCE:1
ORGANIZER;CN=Strunk:mailto:strunk@example.org
ATTENDEE;PARTSTAT=ACCEPTED;CN=Two:mailto:two@example.org
DTSTART:20140716T120000Z
END:VEVENT
END:VCALENDAR
ICS;

        $expected = array();
        $result = $this->parse($oldMessage, $newMessage, $expected);

    }

    /**
     * In this test, an event exists in an attendees calendar. The event
     * is recurring, and the attendee deletes 1 instance of the event.
     * This instance shows up in EXDATE
     *
     * This should automatically generate a DECLINED message for that
     * specific instance.
     */
    function testCreateReplyByException() {


        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
DTSTART:20140811T200000Z
RRULE:FREQ=WEEKLY
ORGANIZER:mailto:organizer@example.org
ATTENDEE:mailto:one@example.org
END:VEVENT
END:VCALENDAR
ICS;

        $newMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
DTSTART:20140811T200000Z
RRULE:FREQ=WEEKLY
ORGANIZER:mailto:organizer@example.org
ATTENDEE:mailto:one@example.org
EXDATE:20140818T200000Z
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
                'senderName' => null,
                'recipient' => 'mailto:organizer@example.org',
                'recipientName' => null,
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:REPLY
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
RECURRENCE-ID:20140818T200000Z
ORGANIZER:mailto:organizer@example.org
ATTENDEE;PARTSTAT=DECLINED:mailto:one@example.org
END:VEVENT
END:VCALENDAR
ICS

            ),
        );
        $result = $this->parse($oldMessage, $newMessage, $expected);

    }

    /**
     * This test is identical to the last, but now we're working with
     * timezones.
     *
     * @depends testCreateReplyByException
     */
    function testCreateReplyByExceptionTz() {


        $oldMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
DTSTART;TZID=America/Toronto:20140811T200000
RRULE:FREQ=WEEKLY
ORGANIZER:mailto:organizer@example.org
ATTENDEE:mailto:one@example.org
END:VEVENT
END:VCALENDAR
ICS;

        $newMessage = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
DTSTART;TZID=America/Toronto:20140811T200000
RRULE:FREQ=WEEKLY
ORGANIZER:mailto:organizer@example.org
ATTENDEE:mailto:one@example.org
EXDATE;TZID=America/Toronto:20140818T200000
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
                'senderName' => null,
                'recipient' => 'mailto:organizer@example.org',
                'recipientName' => null,
                'message' => <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
METHOD:REPLY
BEGIN:VEVENT
UID:foobar
SEQUENCE:1
RECURRENCE-ID;TZID=America/Toronto:20140818T200000
ORGANIZER:mailto:organizer@example.org
ATTENDEE;PARTSTAT=DECLINED:mailto:one@example.org
END:VEVENT
END:VCALENDAR
ICS

            ),
        );
        $result = $this->parse($oldMessage, $newMessage, $expected);

    }

}
