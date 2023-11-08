<?php

namespace Sabre\VObject\Component;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;
use Sabre\VObject\Reader;

class VFreeBusyTest extends TestCase
{
    public function testIsFree(): void
    {
        $input = <<<BLA
BEGIN:VCALENDAR
BEGIN:VFREEBUSY
FREEBUSY;FBTYPE=FREE:20120912T000500Z/PT1H
FREEBUSY;FBTYPE=BUSY:20120912T010000Z/20120912T020000Z
FREEBUSY;FBTYPE=BUSY-TENTATIVE:20120912T020000Z/20120912T030000Z
FREEBUSY;FBTYPE=BUSY-UNAVAILABLE:20120912T030000Z/20120912T040000Z
FREEBUSY;FBTYPE=BUSY:20120912T050000Z/20120912T060000Z,20120912T080000Z/20120912T090000Z
FREEBUSY;FBTYPE=BUSY:20120912T100000Z/PT1H
END:VFREEBUSY
END:VCALENDAR
BLA;

        $obj = VObject\Reader::read($input);
        /**
         * @var VFreeBusy<int, mixed> $vfb
         */
        $vfb = $obj->VFREEBUSY;

        $tz = new \DateTimeZone('UTC');

        self::assertFalse($vfb->isFree(new \DateTime('2012-09-12 01:15:00', $tz), new \DateTime('2012-09-12 01:45:00', $tz)));
        self::assertFalse($vfb->isFree(new \DateTime('2012-09-12 08:05:00', $tz), new \DateTime('2012-09-12 08:10:00', $tz)));
        self::assertFalse($vfb->isFree(new \DateTime('2012-09-12 10:15:00', $tz), new \DateTime('2012-09-12 10:45:00', $tz)));

        // Checking whether the end time is treated as non-inclusive
        self::assertTrue($vfb->isFree(new \DateTime('2012-09-12 09:00:00', $tz), new \DateTime('2012-09-12 09:15:00', $tz)));
        self::assertTrue($vfb->isFree(new \DateTime('2012-09-12 09:45:00', $tz), new \DateTime('2012-09-12 10:00:00', $tz)));
        self::assertTrue($vfb->isFree(new \DateTime('2012-09-12 11:00:00', $tz), new \DateTime('2012-09-12 12:00:00', $tz)));
    }

    public function testValidate(): void
    {
        $input = <<<HI
BEGIN:VCALENDAR
VERSION:2.0
PRODID:YoYo
BEGIN:VFREEBUSY
UID:some-random-id
DTSTAMP:20140402T180200Z
END:VFREEBUSY
END:VCALENDAR
HI;

        $obj = Reader::read($input);

        $warnings = $obj->validate();
        $messages = [];
        foreach ($warnings as $warning) {
            $messages[] = $warning['message'];
        }

        self::assertEquals([], $messages);
    }
}
