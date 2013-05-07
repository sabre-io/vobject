<?php

namespace Sabre\VObject\Parser;

class MimeDirTest extends \PHPUnit_Framework_TestCase {

    function parse($input, $options) {

        $parser = new MimeDir();
        return $parser->parse($input, $options);

    }

    function assertParse($expected, $input, $options = 0) {

        $this->assertEquals(
            $expected,
            $this->parse($input, $options)
        );

    }

    function testComponent() {

        $input = <<<ICS
BEGIN:VCALENDAR
END:VCALENDAR
ICS;

        $expected = array(
            'vcalendar',
            array(),
            array(),
        );

        $this->assertParse($expected, $input);

    }

    function testProperty() {

        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
END:VCALENDAR
ICS;

        $expected = array(
            'vcalendar',
            array(
                array(
                    'version',
                    array(),
                    null,
                    '2.0',
                ),
            ),
            array(),
        );

        $this->assertParse($expected, $input);

    }

    function testSubComponent() {

        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
END:VEVENT
END:VCALENDAR
ICS;

        $expected = array(
            'vcalendar',
            array(
                array(
                    'version',
                    array(),
                    null,
                    '2.0',
                ),
            ),
            array(
                array(
                    'vevent',
                    array(),
                    array(),
                )
            ),
        );

        $this->assertParse($expected, $input);

    }

    function testLineFolding() {

        $input = <<<ICS
BEGIN:VCALENDAR
V
 ERSION:2.0
BEGIN:V
 EVENT
END:VE
\tVENT
END:VCALENDAR
ICS;

        $expected = array(
            'vcalendar',
            array(
                array(
                    'version',
                    array(),
                    null,
                    '2.0',
                ),
            ),
            array(
                array(
                    'vevent',
                    array(),
                    array(),
                )
            ),
        );

        $this->assertParse($expected, $input);

    }

    function testParameters() {

        $input = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
BEGIN:VEVENT
DTSTART;TZID="Europe/London";VALUE=DATE-TIME:20130513T2058
END:VEVENT
END:VCALENDAR
ICS;

        $expected = array(
            'vcalendar',
            array(
                array(
                    'version',
                    array(),
                    null,
                    '2.0',
                ),
            ),
            array(
                array(
                    'vevent',
                    array(
                        array(
                            'dtstart',
                            array(
                                'tzid' => 'Europe/London',
                                'value' => 'DATE-TIME',
                            ),
                            null,
                            '20130513T2058'
                        )
                    ),
                    array(),
                )
            ),
        );

        $this->assertParse($expected, $input);

    }

    function testRepeatingParameters() {

        $input = <<<ICS
BEGIN:VCARD
VERSION:4.0
TEL;TYPE=PREF;TYPE=VOICE:123
TEL;TYPE=PREF,FAX:123
TEL;TYPE="PREF","PAGER","FAX":123
END:VCARD
ICS;

        $expected = array(
            'vcard',
            array(
                array(
                    'version',
                    array(),
                    null,
                    '4.0',
                ),
                array(
                    'tel',
                    array(
                        'type' => array('PREF', 'VOICE'),
                    ),
                    null,
                    '123',
                ),
                array(
                    'tel',
                    array(
                        'type' => array('PREF', 'FAX'),
                    ),
                    null,
                    '123',
                ),
                array(
                    'tel',
                    array(
                        'type' => array('PREF', 'PAGER', 'FAX'),
                    ),
                    null,
                    '123',
                ),
            ),
            array(),
        );

        $this->assertParse($expected, $input);

    }

    function testReadQuotedPrintableSimple() {

        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aach=65n\r\nEND:VCARD";

        $expected = array(
            'vcard',
            array(
                array(
                    'label',
                    array(
                        'encoding' => 'QUOTED-PRINTABLE',
                    ),
                    null,
                    'Aachen',
                ),
            ),
            array(),
        );

        $this->assertParse($expected, $data, MimeDir::OPTION_FORGIVING);

    }

    function testReadQuotedPrintableNewlineSoft() {

        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aa=\r\n ch=\r\n en\r\nEND:VCARD";
        $expected = array(
            'vcard',
            array(
                array(
                    'label',
                    array(
                        'encoding' => 'QUOTED-PRINTABLE',
                    ),
                    null,
                    'Aachen',
                ),
            ),
            array(),
        );

        $this->assertParse($expected, $data, MimeDir::OPTION_FORGIVING);

    }

    /*
    function testReadQuotedPrintableNewlineHard() {

        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aachen=0D=0A=\r\n Germany\r\nEND:VCARD";
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children));
        $this->assertEquals("Aachen\r\nGermany", $this->getPropertyValue($result->label));


    }

    function testReadQuotedPrintableCompatibilityMS() {

        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aachen=0D=0A=\r\nDeutschland:okay\r\nEND:VCARD";
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children));
        $this->assertEquals("Aachen\r\nDeutschland:okay", $this->getPropertyValue($result->label));

    }

    function testReadQuotedPrintableCompatibilityMSTwice() {

        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aachen=0D=0A=\r\nDeutschland=0D=0A=\r\nDE\r\nNOTE;ENCODING=QUOTED-PRINTABLE:Aachen=0D=0A=\r\nist=0D=0A=\r\ntoll\r\nEND:VCARD";

        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(2, count($result->children));
        $this->assertEquals("Aachen\r\nDeutschland\r\nDE", $this->getPropertyValue($result->label));
        $this->assertEquals("Aachen\r\nist\r\ntoll", $this->getPropertyValue($result->note));

    }

    function testReadQuotedPrintableCompatibilityMSSeveral() {

        $data = <<<EOT
BEGIN:VCARD
N
I
C
K
NAME:folder
LABEL;WORK;PREF;ENCODING=QUOTED-PRINTABLE:M=FCnster
ADR;CHARSET=Windows-1252;ENCODING=QUO
TED-PRINTABLE:;B=FCro =
D=FCtschland\\r\\n
NOTE:ENCODING=QUOTED-PRINTABLE:Test=0D=0A
END:VCARD
EOT;

        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(4, count($result->children));
        $this->assertEquals('folder', $result->nickname);
        $this->assertEquals('Münster', $this->getPropertyValue($result->label));
        $this->assertEquals(";Büro Dütschland\\r\\n", $this->getPropertyValue($result->adr));
        $this->assertEquals("ENCODING=QUOTED-PRINTABLE:Test=0D=0A", $this->getPropertyValue($result->note));
    }

     */

}
