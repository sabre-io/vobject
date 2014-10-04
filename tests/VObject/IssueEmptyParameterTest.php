<?php

namespace Sabre\VObject;

class IssueEmptyParameterTest extends \PHPUnit_Framework_TestCase {

    function testRead() {

        $input = <<<VCF
BEGIN:VCARD
VERSION:2.1
N:Doe;Jon;;;
FN:Jon Doe
EMAIL;X-INTERN:foo@example.org
UID:foo
END:VCARD
VCF;

        $vcard = Reader::read($input);

        $this->assertInstanceOf('Sabre\\VObject\\Component\\VCard', $vcard);
        $vcard = $vcard->convert(\Sabre\VObject\Document::VCARD30);
        $vcard = $vcard->serialize();

        $converted = Reader::read($vcard);
        $converted->validate();

        $this->assertTrue(isset($converted->EMAIL['X-INTERN']));

    }

}
