<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class StringUtilTest extends TestCase
{
    public function testNonUTF8()
    {
        $string = StringUtil::isUTF8(chr(0xbf));

        $this->assertEquals(false, $string);
    }

    public function testIsUTF8()
    {
        $string = StringUtil::isUTF8('I 💚 SabreDAV');

        $this->assertEquals(true, $string);
    }

    public function testUTF8ControlChar()
    {
        $string = StringUtil::isUTF8(chr(0x00));

        $this->assertEquals(false, $string);
    }

    public function testConvertToUTF8nonUTF8()
    {
        // 0xBF is an ASCII upside-down question mark
        $string = StringUtil::convertToUTF8(chr(0xBF));

        $this->assertEquals(mb_convert_encoding(chr(0xBF), 'UTF-8', 'ISO-8859-1'), $string);
    }

    public function testConvertToUTF8IsUTF8()
    {
        $string = StringUtil::convertToUTF8('I 💚 SabreDAV');

        $this->assertEquals('I 💚 SabreDAV', $string);
    }

    public function testConvertToUTF8ControlChar()
    {
        $string = StringUtil::convertToUTF8(chr(0x00));

        $this->assertEquals('', $string);
    }
}
