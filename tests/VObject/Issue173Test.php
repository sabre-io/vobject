<?php

namespace Sabre\VObject;

class Issue173Test extends \PHPUnit_Framework_TestCase {

  function testConvert() {

    $input = <<<VCF
BEGIN:VCARD
VERSION:3.0
UID:foo
N:Doe;John;;;
FN:John Doe
item1.X-ABDATE;type=pref:2008-12-11
END:VCARD

VCF;

    $vcard = Reader::read($input);

    $this->assertInstanceOf('Sabre\\VObject\\Component\\VCard', $vcard);
    $vcard = $vcard->convert(\Sabre\VObject\Document::VCARD40);
    $vcard = $vcard->serialize();

    $converted = Reader::read($vcard);
    $converted->validate();

    $version = Version::VERSION;

    $expected = <<<VCF
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject $version//EN
UID:foo
N:Doe;John;;;
FN:John Doe
ITEM1.X-ABDATE;PREF=1:2008-12-11
END:VCARD

VCF;

    $this->assertEquals($expected, str_replace("\r","", $vcard));

  }

}
