<?php

namespace Sabre\VObject\Property\VCard;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;

class LanguageTagTest extends TestCase
{
    public function testMimeDir(): void
    {
        $input = "BEGIN:VCARD\r\nVERSION:4.0\r\nLANG:nl\r\nEND:VCARD\r\n";
        $mimeDir = new VObject\Parser\MimeDir($input);

        $result = $mimeDir->parse($input);

        self::assertInstanceOf(LanguageTag::class, $result->LANG);

        self::assertEquals('nl', $result->LANG->getValue());

        self::assertEquals(
            $input,
            $result->serialize()
        );
    }

    public function testChangeAndSerialize(): void
    {
        $input = "BEGIN:VCARD\r\nVERSION:4.0\r\nLANG:nl\r\nEND:VCARD\r\n";
        $mimeDir = new VObject\Parser\MimeDir($input);

        $result = $mimeDir->parse($input);

        self::assertInstanceOf(LanguageTag::class, $result->LANG);
        // This replicates what the vcard converter does and triggered a bug in
        // the past.
        $result->LANG->setValue(['de']);

        self::assertEquals('de', $result->LANG->getValue());

        $expected = "BEGIN:VCARD\r\nVERSION:4.0\r\nLANG:de\r\nEND:VCARD\r\n";
        self::assertEquals(
            $expected,
            $result->serialize()
        );
    }
}
