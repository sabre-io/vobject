<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class StringUtilTest extends TestCase
{
    public function testNonUTF8(): void
    {
        $string = StringUtil::isUTF8(chr(0xBF));

        $this->assertEquals(false, $string);
    }

    public function testIsUTF8(): void
    {
        $string = StringUtil::isUTF8('I 💚 SabreDAV');

        $this->assertEquals(true, $string);
    }

    public function testUTF8ControlChar(): void
    {
        $string = StringUtil::isUTF8(chr(0x00));

        $this->assertEquals(false, $string);
    }

    public function testConvertToUTF8nonUTF8(): void
    {
        $string = StringUtil::convertToUTF8(chr(0xBF));

        $this->assertEquals(utf8_encode(chr(0xBF)), $string);
    }

    public function testConvertToUTF8IsUTF8(): void
    {
        $string = StringUtil::convertToUTF8('I 💚 SabreDAV');

        $this->assertEquals('I 💚 SabreDAV', $string);
    }

    public function testConvertToUTF8ControlChar(): void
    {
        $string = StringUtil::convertToUTF8(chr(0x00));

        $this->assertEquals('', $string);
    }
}
