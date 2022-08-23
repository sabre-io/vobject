<?php

namespace Sabre\VObject\Property\ICalendar;

use PHPUnit\Framework\TestCase;

class CalAddressTest extends TestCase
{
    /**
     * @dataProvider values
     */
    public function testGetNormalizedValue(string $expected, string $input): void
    {
        $vobj = new \Sabre\VObject\Component\VCalendar();
        $property = $vobj->add('ATTENDEE', $input);

        $this->assertEquals(
            $expected,
            $property->getNormalizedValue()
        );
    }

    public function values(): array
    {
        return [
            ['mailto:a@b.com', 'mailto:a@b.com'],
            ['mailto:a@b.com', 'MAILTO:a@b.com'],
            ['mailto:a@b.com', 'mailto:A@B.COM'],
            ['mailto:a@b.com', 'MAILTO:A@B.COM'],
            ['/foo/bar', '/foo/bar'],
        ];
    }
}
