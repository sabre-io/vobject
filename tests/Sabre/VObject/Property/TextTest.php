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
}
