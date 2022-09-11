<?php

namespace Sabre\VObject\Property\ICalendar;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;

class CalAddressTest extends TestCase
{
    /**
     * @dataProvider values
     */
    public function testGetNormalizedValue(string $expected, string $input): void
    {
        $vobj = new VCalendar();
        /**
         * @var CalAddress<int, mixed> $property
         */
        $property = $vobj->add('ATTENDEE', $input);

        self::assertEquals(
            $expected,
            $property->getNormalizedValue()
        );
    }

    /**
     * @return string[][]
     */
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
