<?php

namespace Sabre\VObject\Parser;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Reader;

class QuotedPrintableTest extends TestCase
{
    public function testReadQuotedPrintableSimple()
    {
        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aach=65n\r\nEND:VCARD";

        $result = Reader::read($data);

        $this->assertInstanceOf(VCard::class, $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children()));
        $this->assertEquals('Aachen', (string) $result->LABEL);
    }

    public function testReadQuotedPrintableNewlineSoft()
    {
        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aa=\r\n ch=\r\n en\r\nEND:VCARD";
        $result = Reader::read($data);

        $this->assertInstanceOf(VCard::class, $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children()));
        $this->assertEquals('Aachen', (string) $result->LABEL);
    }

    public function testReadQuotedPrintableNewlineHard()
    {
        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aachen=0D=0A=\r\n Germany\r\nEND:VCARD";
        $result = Reader::read($data);

        $this->assertInstanceOf(VCard::class, $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children()));
        $this->assertEquals("Aachen\r\nGermany", (string) $result->LABEL);
    }

    public function testReadQuotedPrintableCompatibilityMS()
    {
        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aachen=0D=0A=\r\nDeutschland:okay\r\nEND:VCARD";
        $result = Reader::read($data, Reader::OPTION_FORGIVING);

        $this->assertInstanceOf(VCard::class, $result);
        $this->assertEquals('VCARD', $result->name);
        $this->assertEquals(1, count($result->children()));
        $this->assertEquals("Aachen\r\nDeutschland:okay", (string) $result->LABEL);
    }

    public function testReadQuotesPrintableCompoundValues()
    {
        $data = <<<VCF
BEGIN:VCARD
VERSION:2.1
N:Doe;John;;;
FN:John Doe
ADR;WORK;CHARSET=UTF-8;ENCODING=QUOTED-PRINTABLE:;;M=C3=BCnster =
Str. 1;M=C3=BCnster;;48143;Deutschland
END:VCARD
VCF;

        $result = Reader::read($data, Reader::OPTION_FORGIVING);
        $this->assertEquals([
            '', '', 'Münster Str. 1', 'Münster', '', '48143', 'Deutschland',
        ], $result->ADR->getParts());
    }
}
