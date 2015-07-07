<?php

namespace Sabre\VObject;

class VCardBirthdayTest extends TestCase {

    function testValidBirthday() {

        $input = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
BDAY:19850407
END:VCARD
VCF;

        $version = Version::VERSION;
        $event = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
SUMMARY:Forrest Gump's Birthday
DTSTART:19850407T000000Z
RRULE:FREQ=YEARLY
TRANSP:TRANSPARENT
END:VEVENT
END:VCALENDAR
ICS;

        $vcard = Reader::read($input);
        $expected = Reader::read($event)->serialize();
        $output = $vcard->getBirthdayEvent()->serialize();

        $this->assertEquals(
            $expected,
            $output
        );

    }

    function testLocalizedValidBirthday() {

        $input = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
BDAY:19850407
END:VCARD
VCF;

        $version = Version::VERSION;
        $event = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject $version//EN
CALSCALE:GREGORIAN
BEGIN:VEVENT
SUMMARY:Forrest Gump's Geburtstag
DTSTART:19850407T000000Z
RRULE:FREQ=YEARLY
TRANSP:TRANSPARENT
END:VEVENT
END:VCALENDAR
ICS;

        $vcard = Reader::read($input);
        $expected = Reader::read($event)->serialize();
        $output = $vcard->getBirthdayEvent('%s\'s Geburtstag')->serialize();

        $this->assertEquals(
            $expected,
            $output
        );

    }

    function testInvalidBirthday() {

        $input = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
BDAY:foo
END:VCARD
VCF;

        $vcard = Reader::read($input);

        $this->assertEquals(
            false,
            $vcard->getBirthdayEvent()
        );

    }

    function testNoBirthday() {

        $input = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
END:VCARD
VCF;

        $vcard = Reader::read($input);

        $this->assertEquals(
            false,
            $vcard->getBirthdayEvent()
        );

    }

}
