<?php

namespace Sabre\VObject\Property;

use Sabre\VObject\Component\VCard;

class TextTest extends \PHPUnit_Framework_TestCase {

    function assertVCard21serialization($propValue, $expected) {

        $doc = new VCard(array(
            'VERSION'=>'2.1',
            'PROP' => $propValue
        ), false);

        // Adding quoted-printable, because we're testing if it gets removed
        // automatically.
        $doc->PROP['ENCODING'] = 'QUOTED-PRINTABLE';
        $doc->PROP['P1'] = 'V1';


        $output = $doc->serialize();


        $this->assertEquals("BEGIN:VCARD\r\nVERSION:2.1\r\n$expected\r\nEND:VCARD\r\n", $output);

    }

    function testSerializeVCard21() {

        $this->assertVCard21Serialization(
            'f;oo',
            'PROP;P1=V1:f;oo'
        );

    }

    function testSerializeVCard21Array() {

        $this->assertVCard21Serialization(
            array('f;oo','bar'),
            'PROP;P1=V1:f\;oo;bar'
        );

    }
    function testSerializeVCard21Fold() {

        $this->assertVCard21Serialization(
            str_repeat('x',80),
            'PROP;P1=V1:' . str_repeat('x',64) . "\r\n " . str_repeat('x',16)
        );

    }



    function testSerializeQuotedPrintable() {

        $this->assertVCard21Serialization(
            "foo\r\nbar",
            'PROP;P1=V1;ENCODING=QUOTED-PRINTABLE:foo=0D=0Abar'
        );
    }

    function testSerializeQuotedPrintableFold() {

        $this->assertVCard21Serialization(
            "foo\r\nbarxxxxxxxxxxxxxxxxxxxxxxxxxxxxx",
            "PROP;P1=V1;ENCODING=QUOTED-PRINTABLE:foo=0D=0Abarxxxxxxxxxxxxxxxxxxxxxxxxxx=\r\n xxx"
        );

    }

    function testValidateMinimumPropValue() {

        $vcard = <<<IN
BEGIN:VCARD
VERSION:4.0
UID:foo
FN:Hi!
N:A
END:VCARD
IN;

        $vcard = \Sabre\VObject\Reader::read($vcard);
        $this->assertEquals(1, count($vcard->validate()));

        $this->assertEquals(1, count($vcard->N->getParts()));

        $vcard->validate(\Sabre\VObject\Node::REPAIR);

        $this->assertEquals(5, count($vcard->N->getParts()));

    }

    function testControlCharacterStripping() {

        $s = "chars[";
        foreach (array(
            0x7F, 0x5E, 0x5C, 0x3B, 0x3A, 0x2C, 0x22, 0x20,
            0x1F, 0x1E, 0x1D, 0x1C, 0x1B, 0x1A, 0x19, 0x18,
            0x17, 0x16, 0x15, 0x14, 0x13, 0x12, 0x11, 0x10,
            0x0F, 0x0E, 0x0D, 0x0C, 0x0B, 0x0A, 0x09, 0x08,
            0x07, 0x06, 0x05, 0x04, 0x03, 0x02, 0x01, 0x00,
          ) as $c) {
            $s .= sprintf('%02X(%c)', $c, $c);
        }
        $s .= "]end";

        $v = new VCard();
        $v->LABEL = $s;

        $this->assertEquals("chars[7F()5E(^)5C(\\\\)3B(\\;)3A(:)2C(\\,)22(\")20( )1F()1E()1D()1C()1B()1A()19()18()17()16()15()14()13()12()11()10()0F()0E()0D()0C()0B()0A(\\n)09(	)08()07()06()05()04()03()02()01()00()]end", $v->LABEL->getRawMimeDirValue());

    }

}
