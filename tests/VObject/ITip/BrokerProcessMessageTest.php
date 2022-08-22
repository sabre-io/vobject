<?php

namespace Sabre\VObject\ITip;

class BrokerProcessMessageTest extends BrokerTester
{
    public function testRequestNew(): void
    {
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
VERSION:2.0
BEGIN:VEVENT
SEQUENCE:1
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $this->process($itip, null, $expected);
    }

    public function testRequestUpdate(): void
    {
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
BEGIN:VEVENT
SEQUENCE:1
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $expected = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
SEQUENCE:2
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $this->process($itip, $old, $expected);
    }

    public function testCancel(): void
    {
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
BEGIN:VEVENT
SEQUENCE:1
UID:foobar
END:VEVENT
END:VCALENDAR
ICS;

        $expected = <<<ICS
BEGIN:VCALENDAR
BEGIN:VEVENT
UID:foobar
STATUS:CANCELLED
SEQUENCE:2
END:VEVENT
END:VCALENDAR
ICS;

        $this->process($itip, $old, $expected);
    }

    public function testCancelNoExistingEvent(): void
    {
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

        $this->process($itip, $old, $expected);
    }

    public function testUnsupportedComponent(): void
    {
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

        $this->process($itip, $old, $expected);
    }

    public function testUnsupportedMethod(): void
    {
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

        $this->process($itip, $old, $expected);
    }
}
