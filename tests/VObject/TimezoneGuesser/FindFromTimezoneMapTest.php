<?php

declare(strict_types=1);

namespace Sabre\VObject\TimezoneGuesser;

use PHPUnit\Framework\TestCase;

class FindFromTimezoneMapTest extends TestCase
{
    /**
     * Verify that previously-deprecated IANA names have been replaced with
     * their canonical successors and resolve correctly.
     *
     * @dataProvider updatedTimezoneProvider
     */
    public function testUpdatedTimezonesResolve(string $mapKey, string $expectedOlson): void
    {
        $finder = new FindFromTimezoneMap();
        $tz = $finder->find($mapKey);

        self::assertNotNull($tz, "Expected '$mapKey' to resolve to '$expectedOlson'");
        self::assertSame($expectedOlson, $tz->getName());
    }

    public function updatedTimezoneProvider(): array
    {
        return [
            // windowszones.php
            ['FLE Standard Time', 'Europe/Kyiv'],
            ['India Standard Time', 'Asia/Kolkata'],
            ['Nepal Standard Time', 'Asia/Kathmandu'],
            ['Myanmar Standard Time', 'Asia/Yangon'],
            ['Greenland Standard Time', 'America/Nuuk'],
            ['Argentina Standard Time', 'America/Argentina/Buenos_Aires'],
            ['US Eastern Standard Time', 'America/Indiana/Indianapolis'],
            // lotuszones.php
            ['India', 'Asia/Kolkata'],
            ['Myanmar', 'Asia/Yangon'],
            // exchangezones.php
            ['Kolkata, Chennai, Mumbai, New Delhi, India Standard Time', 'Asia/Kolkata'],
            ['Rangoon', 'Asia/Yangon'],
        ];
    }

    /**
     * Verify that the Microsoft-offset-prefix stripping path still works
     * with updated timezone values.
     */
    public function testMicrosoftOffsetPrefixStripping(): void
    {
        $finder = new FindFromTimezoneMap();
        $tz = $finder->find('(UTC+02:00) FLE Standard Time');

        self::assertNotNull($tz);
        self::assertSame('Europe/Kyiv', $tz->getName());
    }

    public function testUnknownTimezoneReturnsNull(): void
    {
        $finder = new FindFromTimezoneMap();

        self::assertNull($finder->find('This/Does_Not_Exist'));
    }
}
