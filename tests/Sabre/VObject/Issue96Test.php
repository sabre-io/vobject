<?php

namespace Sabre\VObject;

class Issue96Test extends \PHPUnit_Framework_TestCase {

    function testRead() {

        $input = <<<VCF
BEGIN:VCARD
URL;CHARSET=utf-8;ENCODING=QUOTED-PRINTABLE: =
http&amp;#92;://www.example.org
END:VCARD
VCF;

        $vcard = Reader::read($input);
        $this->assertInstanceOf('Sabre\\VObject\\Component\\VCard', $vcard);
        $this->assertEquals("http://www.example.org", $this->getPropertyValue($vcard->url));

    }

}
