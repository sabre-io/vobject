<?php

namespace Sabre\VObject\ITip\TodoBroker;

use Sabre\VObject\ITip\BrokerTester;

class NewTodoTest extends BrokerTester {

    function testNoAttendee() {

        $before = null;
        $after = <<<ICS
BEGIN:VCALENDAR
BEGIN:VTODO
SUMMARY:Do dishes
UID:foo
END:VTODO
END:VCALENDAR
ICS;
        $expected = [];


        $this->assertICalendarChange($before, $after, $expected);

    }

    function testAttendee() {

        $before = null;
        $after = <<<ICS
BEGIN:VCALENDAR
BEGIN:VTODO
SUMMARY:Do dishes
ORGANIZER:mailto:one@example.org
ATTENDEE:mailto:two@example.org
UID:foo
END:VTODO
END:VCALENDAR
ICS;
        $expected = [
            [
                'uid'               => 'foo',
                'component'         => 'VTODO',
                'method'            => 'REQUEST',
                'sequence'          => 1,
                'sender'            => 'mailto:one@example.org',
                'senderName'        => null,
                'recipient'         => 'mailto:two@example.org',
                'recipientName'     => null,
                'scheduleStatus'    => null,
                'significantChange' => true,
                'message'           => <<<ICS
BEGIN:VCALENDAR
METHOD:REQUEST
BEGIN:VTODO
SUMMARY:Do dishes
ORGANIZER:mailto:one@example.org
ATTENDEE:mailto:two@example.org
UID:foo
END:VTODO
END:VCALENDAR
ICS

            ],
        ];


        $this->assertICalendarChange($before, $after, $expected);


    }



}
