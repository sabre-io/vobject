<?php

namespace Sabre\VObject\Component;

use Sabre\VObject;
use Sabre\VObject\Reader;
use Sabre\VObject\Component\VAvailability;

const CRLF = "\r\n";

/**
 * We use `RFCxxx` has a placeholder for the
 * https://tools.ietf.org/html/draft-daboo-calendar-availability-05 name.
 */
class VAvailabilityTest extends \PHPUnit_Framework_TestCase {

    function testVAvailabilityComponent() {

        $vcal = <<<VCAL
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
END:VAVAILABILITY
END:VCALENDAR
VCAL;
        $document = Reader::read($vcal);

        $this->assertInstanceOf(VAvailability::class, $document->VAVAILABILITY);

    }

    function testRFCxxxSection3_1_availabilityprop_required() {

        // UID and DTSTAMP are present.
        $this->assertIsValid(Reader::read(
<<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
UID:foo@test
DTSTAMP:20111005T133225Z
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));

        // UID and DTSTAMP are missing.
        $this->assertIsNotValid(Reader::read(
<<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));

        // DTSTAMP is missing.
        $this->assertIsNotValid(Reader::read(
<<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
UID:foo@test
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));

        // UID is missing.
        $this->assertIsNotValid(Reader::read(
<<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
DTSTAMP:20111005T133225Z
END:VAVAILABILITY
END:VCALENDAR
VCAL
        ));

    }

    function testRFCxxxSection3_1_availabilityprop_optional_once() {

        $properties = [
            'BUSYTYPE:BUSY',
            'CLASS:PUBLIC',
            'CREATED:20111005T135125Z',
            'DESCRIPTION:Long bla bla',
            'DTSTART:20111005T020000',
            'LAST-MODIFIED:20111005T135325Z',
            'ORGANIZER:mailto:foo@example.com',
            'PRIORITY:1',
            'SEQUENCE:0',
            'SUMMARY:Bla bla',
            'URL:http://exampLe.org/'
        ];

        // They are all present, only once.
        $this->assertIsValid(Reader::read($this->template($properties)));

        // We duplicate each one to see if it fails.
        foreach ($properties as $property) {
            $this->assertIsNotValid(Reader::read($this->template([
                $property,
                $property
            ])));
        }

    }

    function testRFCxxxSection3_1_availabilityprop_dtend_duration() {

        // Only DTEND.
        $this->assertIsValid(Reader::read($this->template([
            'DTEND:21111005T133225Z'
        ])));

        // Only DURATION.
        $this->assertIsValid(Reader::read($this->template([
            'DURATION:PT1H'
        ])));

        // Both (not allowed).
        $this->assertIsNotValid(Reader::read($this->template([
            'DTEND:21111005T133225Z',
            'DURATION:PT1H'
        ])));
    }

    protected function assertIsValid(VObject\Document $document) {

        $this->assertEmpty($document->validate());

    }

    protected function assertIsNotValid(VObject\Document $document) {

        $this->assertNotEmpty($document->validate());

    }

    protected function template(array $properties) {

        $template = <<<VCAL
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//id
BEGIN:VAVAILABILITY
UID:foo@test
DTSTAMP:20111005T133225Z
…
END:VAVAILABILITY
END:VCALENDAR
VCAL;

        return str_replace('…', implode(CRLF, $properties), $template);

    }

}
