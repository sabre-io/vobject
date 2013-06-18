<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject\Reader;

class QuotedPrintableTest extends \PHPUnit_Framework_TestCase {

    function testReadQuotedPrintableSimple() {

        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aach=65n\r\nEND:VCARD";

        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children()));
        $this->assertEquals("Aachen", $this->getPropertyValue($result->label));

    }

    function testReadQuotedPrintableNewlineSoft() {

        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aa=\r\n ch=\r\n en\r\nEND:VCARD";
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children()));
        $this->assertEquals("Aachen", $this->getPropertyValue($result->label));

    }

    function testReadQuotedPrintableNewlineHard() {

        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aachen=0D=0A=\r\n Germany\r\nEND:VCARD";
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children()));
        $this->assertEquals("Aachen\r\nGermany", $this->getPropertyValue($result->label));


    }

    function testReadQuotedPrintableCompatibilityMS() {

        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aachen=0D=0A=\r\nDeutschland:okay\r\nEND:VCARD";
        $result = Reader::read($data, Reader::OPTION_FORGIVING);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children()));
        $this->assertEquals("Aachen\r\nDeutschland:okay", $this->getPropertyValue($result->label));

    }

    function testReadPropertyWithCharset() {

        $data = "BEGIN:VCARD\r\nLABEL;CHARSET=Windows-1252:Aachen\xFC\r\nEND:VCARD";
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children()));
        $this->assertEquals("Aachenü", $this->getPropertyValue($result->label));

    }

    function testReadPropertyWithoutCharset() {

        $data = "BEGIN:VCARD\r\nLABEL:Aachen\xFC\r\nEND:VCARD";
        $result = Reader::read($data);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children()));
        $this->assertEquals("Aachen\xFC", $this->getPropertyValue($result->label));

    }

    function testReadQuotedPrintableCompatibilityMSSeveral() {

        $data = <<<EOT
BEGIN:VCARD
ADR;CHARSET=Windows-1252;ENCODING=QUOTED-PRINTABLE:;B=FCro =
D=FCtschland\\r\\n
END:VCARD
EOT;

        $result = Reader::read($data, Reader::OPTION_FORGIVING);

        $this->assertInstanceOf('Sabre\\VObject\\Component', $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children));
        $this->assertEquals(";Büro Dütschland\\r\\n", $this->getPropertyValue($result->adr));
    }

    private function getPropertyValue(\Sabre\VObject\Property $property) {

        return (string)$property;

        /*
        $param = $property['encoding'];
        if ($param !== null) {
            $encoding = strtoupper((string)$param);
            if ($encoding === 'QUOTED-PRINTABLE') {
                $value = quoted_printable_decode($value);
            } else {
                throw new Exception();
            }
        }

        $param = $property['charset'];
        if ($param !== null) {
            $charset = strtoupper((string)$param);
            if ($charset !== 'UTF-8') {
                $value = mb_convert_encoding($value, 'UTF-8', $charset);
            }
        } else {
            $value = StringUtil::convertToUTF8($value);
        }

        return $value;
         */
    }
}
