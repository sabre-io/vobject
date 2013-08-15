<?php

namespace Sabre\VObject;

/**
 * Assorted vcard 2.1 tests.
 */
class VCard21Test extends \PHPUnit_Framework_TestCase {

    function testPropertyWithNoName() {

        $input = <<<VCF
BEGIN:VCARD\r
VERSION:2.1\r
EMAIL;HOME;WORK:evert@fruux.com\r
END:VCARD\r

VCF;

        $vobj = Reader::read($input);
        $output = $vobj->serialize($input);

        $this->assertEquals($input, $output);  

    }

}
