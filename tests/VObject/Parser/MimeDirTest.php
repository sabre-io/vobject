<?php

namespace Sabre\VObject\Parser;

/**
 * Note that most MimeDir related tests can actually be found in the ReaderTest
 * class one level up.
 */
class MimeDirTest extends \PHPUnit_Framework_TestCase {

    /**
     * @expectedException \Sabre\VObject\ParseException
     */
    function testParseError() {

        $mimeDir = new MimeDir();
        $mimeDir->parse(fopen(__FILE__, 'a'));

    }

    function testDecodeLatin1() {

        $vcard = <<<VCF
BEGIN:VCARD
VERSION:3.0
FN:umlaut u - \xFC
END:VCARD\n
VCF;

        $mimeDir = new Mimedir();
        $mimeDir->setCharSet('ISO-8859-1');
        $vcard = $mimeDir->parse($vcard);
        $this->assertEquals("umlaut u - \xC3\xBC", $vcard->FN->getValue());

    }

    function testDecodeInlineLatin1() {

        $vcard = <<<VCF
BEGIN:VCARD
VERSION:2.1
FN;CHARSET=ISO-8859-1:umlaut u - \xFC
END:VCARD\n
VCF;

        $mimeDir = new Mimedir();
        $vcard = $mimeDir->parse($vcard);
        $this->assertEquals("umlaut u - \xC3\xBC", $vcard->FN->getValue());

    }

    function testDontDecodeLatin1() {

        $vcard = <<<VCF
BEGIN:VCARD
VERSION:4.0
FN:umlaut u - \xFC
END:VCARD\n
VCF;

        $mimeDir = new Mimedir();
        $vcard = $mimeDir->parse($vcard);
        // This basically tests that we don't touch the input string if
        // the encoding was set to UTF-8. The result is actually invalid
        // and the validator should report this, but it tests effectively
        // that we pass through the string byte-by-byte.
        $this->assertEquals("umlaut u - \xFC", $vcard->FN->getValue());

    }
}
