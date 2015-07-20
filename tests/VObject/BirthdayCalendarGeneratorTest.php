<?php

namespace Sabre\VObject;

class BirthdayCalendarGeneratorTest extends TestCase {

    function setUp() {
        $this->generator = new BirthdayCalendarGenerator();
    }

    function testVcardStringWithValidBirthday() {

        $input = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
BDAY:19850407
UID:foo
END:VCARD
VCF;

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
SUMMARY:Forrest Gump's Birthday
DTSTART:19850407T000000Z
RRULE:FREQ=YEARLY
TRANSP:TRANSPARENT
X-SABRE-BDAY;X-SABRE-VCARD-UID=foo;X-SABRE-VCARD-FN=Forrest Gump:BDAY
END:VEVENT
END:VCALENDAR
ICS;

        $this->generator->setObjects($input);
        $output = $this->generator->getResult();

        $this->assertVObjEquals(
            $expected,
            $output
        );

    }

    function testArrayOfVcardStringsWithValidBirthdays() {

        $input = [];

        $input[] = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
BDAY:19850407
UID:foo
END:VCARD
VCF;

        $input[] = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Doe;John;;Mr.
FN:John Doe
BDAY:19820210
UID:bar
END:VCARD
VCF;

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
SUMMARY:Forrest Gump's Birthday
DTSTART:19850407T000000Z
RRULE:FREQ=YEARLY
TRANSP:TRANSPARENT
X-SABRE-BDAY;X-SABRE-VCARD-UID=foo;X-SABRE-VCARD-FN=Forrest Gump:BDAY
END:VEVENT
BEGIN:VEVENT
SUMMARY:John Doe's Birthday
DTSTART:19820210T000000Z
RRULE:FREQ=YEARLY
TRANSP:TRANSPARENT
X-SABRE-BDAY;X-SABRE-VCARD-UID=bar;X-SABRE-VCARD-FN=John Doe:BDAY
END:VEVENT
END:VCALENDAR
ICS;

        $this->generator->setObjects($input);
        $output = $this->generator->getResult();

        $this->assertVObjEquals(
            $expected,
            $output
        );

    }

    function testVcardObjectWithValidBirthday() {

        $input = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
BDAY:19850407
UID:foo
END:VCARD
VCF;

        $input = Reader::read($input);

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
SUMMARY:Forrest Gump's Birthday
DTSTART:19850407T000000Z
RRULE:FREQ=YEARLY
TRANSP:TRANSPARENT
X-SABRE-BDAY;X-SABRE-VCARD-UID=foo;X-SABRE-VCARD-FN=Forrest Gump:BDAY
END:VEVENT
END:VCALENDAR
ICS;

        $this->generator->setObjects($input);
        $output = $this->generator->getResult();

        $this->assertVObjEquals(
            $expected,
            $output
        );

    }

    function testArrayOfVcardObjectsWithValidBirthdays() {

        $input = [];

        $input[] = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
BDAY:19850407
UID:foo
END:VCARD
VCF;

        $input[] = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Doe;John;;Mr.
FN:John Doe
BDAY:19820210
UID:bar
END:VCARD
VCF;

        foreach ($input as $key => $value) {
            $input[$key] = Reader::read($value);
        }

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
SUMMARY:Forrest Gump's Birthday
DTSTART:19850407T000000Z
RRULE:FREQ=YEARLY
TRANSP:TRANSPARENT
X-SABRE-BDAY;X-SABRE-VCARD-UID=foo;X-SABRE-VCARD-FN=Forrest Gump:BDAY
END:VEVENT
BEGIN:VEVENT
SUMMARY:John Doe's Birthday
DTSTART:19820210T000000Z
RRULE:FREQ=YEARLY
TRANSP:TRANSPARENT
X-SABRE-BDAY;X-SABRE-VCARD-UID=bar;X-SABRE-VCARD-FN=John Doe:BDAY
END:VEVENT
END:VCALENDAR
ICS;

        $this->generator->setObjects($input);
        $output = $this->generator->getResult();

        $this->assertVObjEquals(
            $expected,
            $output
        );

    }

    function testVcardStringWithValidBirthdayWithXAppleOmitYear() {

        $input = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
BDAY;X-APPLE-OMIT-YEAR=1604:1604-04-07
UID:foo
END:VCARD
VCF;

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
SUMMARY:Forrest Gump's Birthday
DTSTART:20000407T000000Z
RRULE:FREQ=YEARLY
TRANSP:TRANSPARENT
X-SABRE-BDAY;X-SABRE-VCARD-UID=foo;X-SABRE-VCARD-FN=Forrest Gump;X-SABRE-OMIT-YEAR=2000:BDAY
END:VEVENT
END:VCALENDAR
ICS;

        $this->generator->setObjects($input);
        $output = $this->generator->getResult();

        $this->assertVObjEquals(
            $expected,
            $output
        );

    }

    function testVcardStringWithValidBirthdayWithoutYear() {

        $input = <<<VCF
BEGIN:VCARD
VERSION:4.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
BDAY:--04-07
UID:foo
END:VCARD
VCF;

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
SUMMARY:Forrest Gump's Birthday
DTSTART:20000407T000000Z
RRULE:FREQ=YEARLY
TRANSP:TRANSPARENT
X-SABRE-BDAY;X-SABRE-VCARD-UID=foo;X-SABRE-VCARD-FN=Forrest Gump;X-SABRE-OMIT-YEAR=2000:BDAY
END:VEVENT
END:VCALENDAR
ICS;

        $this->generator->setObjects($input);
        $output = $this->generator->getResult();

        $this->assertVObjEquals(
            $expected,
            $output
        );

    }

    function testVcardStringWithInvalidBirthday() {

        $input = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
BDAY:foo
X-SABRE-BDAY;X-SABRE-VCARD-UID=foo;X-SABRE-VCARD-FN=Forrest Gump:BDAY
END:VCARD
VCF;

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
END:VCALENDAR
ICS;

        $this->generator->setObjects($input);
        $output = $this->generator->getResult();

        $this->assertVObjEquals(
            $expected,
            $output
        );

    }

    function testVcardStringWithNoBirthday() {

        $input = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
UID:foo
END:VCARD
VCF;

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
END:VCALENDAR
ICS;

        $this->generator->setObjects($input);
        $output = $this->generator->getResult();

        $this->assertVObjEquals(
            $expected,
            $output
        );

    }

    function testVcardStringWithValidBirthdayLocalized() {

        $input = <<<VCF
BEGIN:VCARD
VERSION:3.0
N:Gump;Forrest;;Mr.
FN:Forrest Gump
BDAY:19850407
UID:foo
END:VCARD
VCF;

        $expected = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
SUMMARY:Forrest Gump's Geburtstag
DTSTART:19850407T000000Z
RRULE:FREQ=YEARLY
TRANSP:TRANSPARENT
X-SABRE-BDAY;X-SABRE-VCARD-UID=foo;X-SABRE-VCARD-FN=Forrest Gump:BDAY
END:VEVENT
END:VCALENDAR
ICS;

        $this->generator->setObjects($input);
        $this->generator->setFormat('%1$s\'s Geburtstag');
        $output = $this->generator->getResult();

        $this->assertVObjEquals(
            $expected,
            $output
        );

        // Reset to default format
        $this->generator->setFormat('%1$s\'s Birthday');

    }

}
