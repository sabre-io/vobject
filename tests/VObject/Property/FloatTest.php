<?php

namespace Sabre\VObject\Property;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;

class FloatTest extends TestCase
{
    public function testMimeDir(): void
    {
        $input = "BEGIN:VCARD\r\nVERSION:4.0\r\nX-FLOAT;VALUE=FLOAT:0.234;1.245\r\nEND:VCARD\r\n";
        $mimeDir = new VObject\Parser\MimeDir($input);

        $result = $mimeDir->parse($input);

        self::assertInstanceOf(FloatValue::class, $result->{'X-FLOAT'});

        self::assertEquals([
            0.234,
            1.245,
        ], $result->{'X-FLOAT'}->getParts());

        self::assertEquals(
            $input,
            $result->serialize()
        );
    }
}
