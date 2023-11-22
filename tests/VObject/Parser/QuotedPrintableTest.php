<?php

namespace Sabre\VObject\Parser;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component;
use Sabre\VObject\Property;
use Sabre\VObject\Reader;

class QuotedPrintableTest extends TestCase
{
    public function testReadQuotedPrintableSimple(): void
    {
        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aach=65n\r\nEND:VCARD";

        $result = Reader::read($data);

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCARD', $result->name);
        self::assertCount(1, $result->children());
        self::assertEquals('Aachen', $this->getPropertyValue($result->LABEL));
    }

    public function testReadQuotedPrintableNewlineSoft(): void
    {
        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aa=\r\n ch=\r\n en\r\nEND:VCARD";
        $result = Reader::read($data);

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCARD', $result->name);
        self::assertCount(1, $result->children());
        self::assertEquals('Aachen', $this->getPropertyValue($result->LABEL));
    }

    public function testReadQuotedPrintableNewlineHard(): void
    {
        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aachen=0D=0A=\r\n Germany\r\nEND:VCARD";
        $result = Reader::read($data);

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCARD', $result->name);
        self::assertCount(1, $result->children());
        self::assertEquals("Aachen\r\nGermany", $this->getPropertyValue($result->LABEL));
    }

    public function testReadQuotedPrintableCompatibilityMS(): void
    {
        $data = "BEGIN:VCARD\r\nLABEL;ENCODING=QUOTED-PRINTABLE:Aachen=0D=0A=\r\nDeutschland:okay\r\nEND:VCARD";
        $result = Reader::read($data, Reader::OPTION_FORGIVING);

        self::assertInstanceOf(Component::class, $result);
        self::assertEquals('VCARD', $result->name);
        self::assertCount(1, $result->children());
        self::assertEquals("Aachen\r\nDeutschland:okay", $this->getPropertyValue($result->LABEL));
    }

    public function testReadQuotesPrintableCompoundValues(): void
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
        self::assertEquals([
            '', '', 'Münster Str. 1', 'Münster', '', '48143', 'Deutschland',
        ], $result->ADR->getParts());
    }

    /**
     * @param Property<int, mixed>|null $property
     */
    private function getPropertyValue(?Property $property): string
    {
        $this->assertNotNull($property, __METHOD__.' called with property set to null');

        return (string) $property;

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
