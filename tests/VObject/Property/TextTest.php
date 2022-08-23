<?php

namespace Sabre\VObject\Property;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCard;

class TextTest extends TestCase
{
    public function assertVCard21Serialization($propValue, string $expected): void
    {
        $doc = new VCard([
            'VERSION' => '2.1',
            'PROP' => $propValue,
        ], false);

        // Adding quoted-printable, because we're testing if it gets removed
        // automatically.
        $doc->PROP['ENCODING'] = 'QUOTED-PRINTABLE';
        $doc->PROP['P1'] = 'V1';

        $output = $doc->serialize();

        $this->assertEquals("BEGIN:VCARD\r\nVERSION:2.1\r\n$expected\r\nEND:VCARD\r\n", $output);
    }

    public function testSerializeVCard21(): void
    {
        $this->assertVCard21Serialization(
            'f;oo',
            'PROP;P1=V1:f;oo'
        );
    }

    public function testSerializeVCard21Array(): void
    {
        $this->assertVCard21Serialization(
            ['f;oo', 'bar'],
            'PROP;P1=V1:f\;oo;bar'
        );
    }

    public function testSerializeVCard21Fold(): void
    {
        $this->assertVCard21Serialization(
            str_repeat('x', 80),
            'PROP;P1=V1:'.str_repeat('x', 64)."\r\n ".str_repeat('x', 16)
        );
    }

    public function testSerializeQuotedPrintable(): void
    {
        $this->assertVCard21Serialization(
            "foo\r\nbar",
            'PROP;P1=V1;ENCODING=QUOTED-PRINTABLE:foo=0D=0Abar'
        );
    }

    public function testSerializeQuotedPrintableFold(): void
    {
        $this->assertVCard21Serialization(
            "foo\r\nbarxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
            "PROP;P1=V1;ENCODING=QUOTED-PRINTABLE:foo=0D=0Abarxxxxxxxxxxxxxxxxxxxxxxxxxx=\r\n xxx"
        );
    }

    public function testValidateMinimumPropValue(): void
    {
        $vcard = <<<IN
BEGIN:VCARD
VERSION:4.0
UID:foo
FN:Hi!
N:A
END:VCARD
IN;

        $vcard = \Sabre\VObject\Reader::read($vcard);
        $this->assertCount(1, $vcard->validate());

        $this->assertCount(1, $vcard->N->getParts());

        $vcard->validate(\Sabre\VObject\Node::REPAIR);

        $this->assertCount(5, $vcard->N->getParts());
    }
}
