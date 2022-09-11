<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class ReaderTest extends TestCase
{
    public function testReadComponent(): void
    {
        $data = "BEGIN:VCALENDAR\r\nEND:VCALENDAR";

        $result = Reader::read($data);

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCALENDAR', $result->name);
        self::assertCount(0, $result->children());
    }

    public function testReadStream(): void
    {
        $data = "BEGIN:VCALENDAR\r\nEND:VCALENDAR";

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $data);
        rewind($stream);

        $result = Reader::read($stream);

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCALENDAR', $result->name);
        self::assertCount(0, $result->children());
    }

    public function testReadComponentUnixNewLine(): void
    {
        $data = "BEGIN:VCALENDAR\nEND:VCALENDAR";

        $result = Reader::read($data);

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCALENDAR', $result->name);
        self::assertCount(0, $result->children());
    }

    public function testReadComponentLineFold(): void
    {
        $data = "BEGIN:\r\n\tVCALENDAR\r\nE\r\n ND:VCALENDAR";

        $result = Reader::read($data);

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCALENDAR', $result->name);
        self::assertCount(0, $result->children());
    }

    public function testReadCorruptComponent(): void
    {
        $this->expectException(ParseException::class);
        $data = "BEGIN:VCALENDAR\r\nEND:FOO";

        Reader::read($data);
    }

    public function testReadCorruptSubComponent(): void
    {
        $this->expectException(ParseException::class);
        $data = "BEGIN:VCALENDAR\r\nBEGIN:VEVENT\r\nEND:FOO\r\nEND:VCALENDAR";

        Reader::read($data);
    }

    public function testReadProperty(): void
    {
        $data = "BEGIN:VCALENDAR\r\nSUMMARY:propValue\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        $result = $result->SUMMARY;
        self::assertInstanceOf(Property::class, $result);
        self::assertEquals('SUMMARY', $result->name);
        self::assertEquals('propValue', $result->getValue());
    }

    public function testReadPropertyWithNewLine(): void
    {
        $data = "BEGIN:VCALENDAR\r\nSUMMARY:Line1\\nLine2\\NLine3\\\\Not the 4th line!\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        $result = $result->SUMMARY;
        self::assertInstanceOf(Property::class, $result);
        self::assertEquals('SUMMARY', $result->name);
        self::assertEquals("Line1\nLine2\nLine3\\Not the 4th line!", $result->getValue());
    }

    public function testReadMappedProperty(): void
    {
        $data = "BEGIN:VCALENDAR\r\nDTSTART:20110529\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        $result = $result->DTSTART;
        self::assertInstanceOf(Property\ICalendar\DateTime::class, $result);
        self::assertEquals('DTSTART', $result->name);
        self::assertEquals('20110529', $result->getValue());
    }

    public function testReadMappedPropertyGrouped(): void
    {
        $data = "BEGIN:VCALENDAR\r\nfoo.DTSTART:20110529\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        $result = $result->DTSTART;
        self::assertInstanceOf(Property\ICalendar\DateTime::class, $result);
        self::assertEquals('DTSTART', $result->name);
        self::assertEquals('20110529', $result->getValue());
    }

    public function testReadMissingEnd(): void
    {
        $data = "BEGIN:VCALENDAR\r\nPROPNAME:propValue";
        $result = Reader::read($data);
        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCALENDAR', $result->name);
        self::assertCount(1, $result->children());
        self::assertInstanceOf(Property::class, $result->children()[0]);
        self::assertEquals('PROPNAME', $result->children()[0]->name);
        self::assertEquals('propValue', $result->children()[0]->getValue());
    }

    public function testReadPropertyInComponent(): void
    {
        $data = [
            'BEGIN:VCALENDAR',
            'PROPNAME:propValue',
            'END:VCALENDAR',
        ];

        $result = Reader::read(implode("\r\n", $data));

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCALENDAR', $result->name);
        self::assertCount(1, $result->children());
        self::assertInstanceOf(Property::class, $result->children()[0]);
        self::assertEquals('PROPNAME', $result->children()[0]->name);
        self::assertEquals('propValue', $result->children()[0]->getValue());
    }

    public function testReadNestedComponent(): void
    {
        $data = [
            'BEGIN:VCALENDAR',
            'BEGIN:VTIMEZONE',
            'BEGIN:DAYLIGHT',
            'END:DAYLIGHT',
            'END:VTIMEZONE',
            'END:VCALENDAR',
        ];

        $result = Reader::read(implode("\r\n", $data));

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCALENDAR', $result->name);
        self::assertCount(1, $result->children());
        self::assertInstanceOf(Component::class, $result->children()[0]);
        self::assertEquals('VTIMEZONE', $result->children()[0]->name);
        self::assertCount(1, $result->children()[0]->children());
        self::assertInstanceOf(Component::class, $result->children()[0]->children()[0]);
        self::assertEquals('DAYLIGHT', $result->children()[0]->children()[0]->name);
    }

    public function testReadPropertyParameter(): void
    {
        $data = "BEGIN:VCALENDAR\r\nPROPNAME;PARAMNAME=paramvalue:propValue\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        $result = $result->PROPNAME;

        self::assertInstanceOf(Property::class, $result);
        self::assertEquals('PROPNAME', $result->name);
        self::assertEquals('propValue', $result->getValue());
        self::assertCount(1, $result->parameters());
        self::assertEquals('PARAMNAME', $result->parameters['PARAMNAME']->name);
        self::assertEquals('paramvalue', $result->parameters['PARAMNAME']->getValue());
    }

    public function testReadPropertyRepeatingParameter(): void
    {
        $data = "BEGIN:VCALENDAR\r\nPROPNAME;N=1;N=2;N=3,4;N=\"5\",6;N=\"7,8\";N=9,10;N=^'11^':propValue\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        $result = $result->PROPNAME;

        self::assertInstanceOf(Property::class, $result);
        self::assertEquals('PROPNAME', $result->name);
        self::assertEquals('propValue', $result->getValue());
        self::assertCount(1, $result->parameters());
        self::assertEquals('N', $result->parameters['N']->name);
        self::assertEquals('1,2,3,4,5,6,7,8,9,10,"11"', $result->parameters['N']->getValue());
        self::assertEquals([1, 2, 3, 4, 5, 6, '7,8', 9, 10, '"11"'], $result->parameters['N']->getParts());
    }

    public function testReadPropertyRepeatingNamelessGuessedParameter(): void
    {
        $data = "BEGIN:VCALENDAR\r\nPROPNAME;WORK;VOICE;PREF:propValue\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        $result = $result->PROPNAME;

        self::assertInstanceOf(Property::class, $result);
        self::assertEquals('PROPNAME', $result->name);
        self::assertEquals('propValue', $result->getValue());
        self::assertCount(1, $result->parameters());
        self::assertEquals('TYPE', $result->parameters['TYPE']->name);
        self::assertEquals('WORK,VOICE,PREF', $result->parameters['TYPE']->getValue());
        self::assertEquals(['WORK', 'VOICE', 'PREF'], $result->parameters['TYPE']->getParts());
    }

    public function testReadPropertyNoName(): void
    {
        $data = "BEGIN:VCALENDAR\r\nPROPNAME;PRODIGY:propValue\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        $result = $result->PROPNAME;

        self::assertInstanceOf(Property::class, $result);
        self::assertEquals('PROPNAME', $result->name);
        self::assertEquals('propValue', $result->getValue());
        self::assertCount(1, $result->parameters());
        self::assertEquals('TYPE', $result->parameters['TYPE']->name);
        self::assertTrue($result->parameters['TYPE']->noName);
        self::assertEquals('PRODIGY', $result->parameters['TYPE']);
    }

    public function testReadPropertyParameterExtraColon(): void
    {
        $data = "BEGIN:VCALENDAR\r\nPROPNAME;PARAMNAME=paramvalue:propValue:anotherrandomstring\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        $result = $result->PROPNAME;

        self::assertInstanceOf(Property::class, $result);
        self::assertEquals('PROPNAME', $result->name);
        self::assertEquals('propValue:anotherrandomstring', $result->getValue());
        self::assertCount(1, $result->parameters());
        self::assertEquals('PARAMNAME', $result->parameters['PARAMNAME']->name);
        self::assertEquals('paramvalue', $result->parameters['PARAMNAME']->getValue());
    }

    public function testReadProperty2Parameters(): void
    {
        $data = "BEGIN:VCALENDAR\r\nPROPNAME;PARAMNAME=paramvalue;PARAMNAME2=paramvalue2:propValue\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        $result = $result->PROPNAME;

        self::assertInstanceOf(Property::class, $result);
        self::assertEquals('PROPNAME', $result->name);
        self::assertEquals('propValue', $result->getValue());
        self::assertCount(2, $result->parameters());
        self::assertEquals('PARAMNAME', $result->parameters['PARAMNAME']->name);
        self::assertEquals('paramvalue', $result->parameters['PARAMNAME']->getValue());
        self::assertEquals('PARAMNAME2', $result->parameters['PARAMNAME2']->name);
        self::assertEquals('paramvalue2', $result->parameters['PARAMNAME2']->getValue());
    }

    public function testReadPropertyParameterQuoted(): void
    {
        $data = "BEGIN:VCALENDAR\r\nPROPNAME;PARAMNAME=\"paramvalue\":propValue\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        $result = $result->PROPNAME;

        self::assertInstanceOf(Property::class, $result);
        self::assertEquals('PROPNAME', $result->name);
        self::assertEquals('propValue', $result->getValue());
        self::assertCount(1, $result->parameters());
        self::assertEquals('PARAMNAME', $result->parameters['PARAMNAME']->name);
        self::assertEquals('paramvalue', $result->parameters['PARAMNAME']->getValue());
    }

    public function testReadPropertyParameterNewLines(): void
    {
        $data = "BEGIN:VCALENDAR\r\nPROPNAME;PARAMNAME=paramvalue1^nvalue2^^nvalue3:propValue\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        $result = $result->PROPNAME;

        self::assertInstanceOf(Property::class, $result);
        self::assertEquals('PROPNAME', $result->name);
        self::assertEquals('propValue', $result->getValue());

        self::assertCount(1, $result->parameters());
        self::assertEquals('PARAMNAME', $result->parameters['PARAMNAME']->name);
        self::assertEquals("paramvalue1\nvalue2^nvalue3", $result->parameters['PARAMNAME']->getValue());
    }

    public function testReadPropertyParameterQuotedColon(): void
    {
        $data = "BEGIN:VCALENDAR\r\nPROPNAME;PARAMNAME=\"param:value\":propValue\r\nEND:VCALENDAR";
        $result = Reader::read($data);
        $result = $result->PROPNAME;

        self::assertInstanceOf(Property::class, $result);
        self::assertEquals('PROPNAME', $result->name);
        self::assertEquals('propValue', $result->getValue());
        self::assertCount(1, $result->parameters());
        self::assertEquals('PARAMNAME', $result->parameters['PARAMNAME']->name);
        self::assertEquals('param:value', $result->parameters['PARAMNAME']->getValue());
    }

    public function testReadForgiving(): void
    {
        $data = [
            'BEGIN:VCALENDAR',
            'X_PROP:propValue',
            'END:VCALENDAR',
        ];

        $caught = false;
        try {
            Reader::read(implode("\r\n", $data));
        } catch (ParseException $e) {
            $caught = true;
        }

        self::assertEquals(true, $caught);

        $result = Reader::read(implode("\r\n", $data), Reader::OPTION_FORGIVING);

        $expected = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'X_PROP:propValue',
            'END:VCALENDAR',
            '',
        ]);

        self::assertEquals($expected, $result->serialize());
    }

    public function testReadWithInvalidLine(): void
    {
        $data = [
            'BEGIN:VCALENDAR',
            'DESCRIPTION:propValue',
            "Yes, we've actually seen a file with non-indented property values on multiple lines",
            'END:VCALENDAR',
        ];

        $caught = false;
        try {
            Reader::read(implode("\r\n", $data));
        } catch (ParseException $e) {
            $caught = true;
        }

        self::assertEquals(true, $caught);

        $result = Reader::read(implode("\r\n", $data), Reader::OPTION_IGNORE_INVALID_LINES);

        $expected = implode("\r\n", [
            'BEGIN:VCALENDAR',
            'DESCRIPTION:propValue',
            'END:VCALENDAR',
            '',
        ]);

        self::assertEquals($expected, $result->serialize());
    }

    /**
     * Reported as Issue 32.
     */
    public function testReadIncompleteFile(): void
    {
        $this->expectException(ParseException::class);
        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:1.0
BEGIN:VEVENT
X-FUNAMBOL-FOLDER:DEFAULT_FOLDER
X-FUNAMBOL-ALLDAY:0
DTSTART:20111017T110000Z
DTEND:20111017T123000Z
X-MICROSOFT-CDO-BUSYSTATUS:BUSY
CATEGORIES:
LOCATION;ENCODING=QUOTED-PRINTABLE;CHARSET=UTF-8:Netviewer Meeting
PRIORITY:1
STATUS:3
X-MICROSOFT-CDO-REPLYTIME:20111017T064200Z
SUMMARY;ENCODING=QUOTED-PRINTABLE;CHARSET=UTF-8:Kopieren: test
CLASS:PUBLIC
AALARM:
RRULE:
X-FUNAMBOL-BILLINGINFO:
X-FUNAMBOL-COMPANIES:
X-FUNAMBOL-MILEAGE:
X-FUNAMBOL-NOAGING:0
ATTENDEE;STATUS=NEEDS ACTION;ENCODING=QUOTED-PRINTABLE;CHARSET=UTF-8:'Heino' heino@test.com
ATTENDEE;STATUS=NEEDS ACTION;ENCODING=QUOTED-PRINTABLE;CHARSET=UTF-8:'Markus' test@test.com
ATTENDEE;STATUS=NEEDS AC
ICS;

        Reader::read($input);
    }

    public function testReadBrokenInput(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        Reader::read(false); /* @phpstan-ignore-line */
    }

    public function testReadBOM(): void
    {
        $data = chr(0xEF).chr(0xBB).chr(0xBF)."BEGIN:VCALENDAR\r\nEND:VCALENDAR";
        $result = Reader::read($data);

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCALENDAR', $result->name);
        self::assertCount(0, $result->children());
    }

    public function testReadXMLComponent(): void
    {
        $data = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
 </vcalendar>
</icalendar>
XML;

        $result = Reader::readXML($data);

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCALENDAR', $result->name);
        self::assertCount(0, $result->children());
    }

    public function testReadXMLStream(): void
    {
        $data = <<<XML
<?xml version="1.0" encoding="utf-8"?>
<icalendar xmlns="urn:ietf:params:xml:ns:icalendar-2.0">
 <vcalendar>
 </vcalendar>
</icalendar>
XML;

        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $data);
        rewind($stream);

        $result = Reader::readXML($stream);

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCALENDAR', $result->name);
        self::assertCount(0, $result->children());
    }
}
