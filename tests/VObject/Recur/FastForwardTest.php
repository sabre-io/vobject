<?php

namespace Sabre\VObject\Recur;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class FastForwardTest extends TestCase
{
    const FF_TIMEOUT = 1000000; // in usec

    private function fastForward(RRuleIterator $ruleIterator, \DateTimeInterface $ffDate)
    {
        $ru = getrusage();
        $startTime = $ru['ru_utime.tv_sec'] * 1000000 + $ru['ru_utime.tv_usec'];
        $ruleIterator->fastForward($ffDate);
        $ru = getrusage();
        $endTime = $ru['ru_utime.tv_sec'] * 1000000 + $ru['ru_utime.tv_usec'];
        $this->assertLessThan(self::FF_TIMEOUT, $endTime - $startTime);
    }

    public function testFastForwardYearlyBasic()
    {
        $startDate = new DateTime('1970-10-23 00:00:00', new DateTimeZone('zulu'));
        $ffDate = new DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(99999, 1, 1);
        $rrule = new RRuleIterator('FREQ=YEARLY', $startDate);

        $this->fastForward($rrule, $ffDate);

        $year = 60 * 60 * 24 * 365;
        $expected = (new DateTime())
            ->setTimezone(new DateTimeZone('zulu'))
            ->setDate(99999, 10, 23)
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        // It's a leap
        $expected += $year + 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        $expected += $year;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        $expected += $year;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        $expected += $year;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        // leap
        $expected += $year + 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        $expected += $year;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        $expected += $year;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        $expected += $year;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        // leap
        $expected += $year + 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
    }

    public function testFastForwardYearlyByYearDay()
    {
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone('zulu'));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(99998, 12, 31);
        $rrule = new RRuleIterator('FREQ=YEARLY;BYYEARDAY=1,20,300', $startDate);

        $this->fastForward($rrule, $ffDate);

        $day = 60 * 60 * 24;
        $expected = (new DateTime())
            ->setTimezone(new DateTimeZone('zulu'))
            ->setDate(99999, 1, 1)// 20th day
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        // 300th day
        $expected += 19 * $day;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        // 1st day
        $expected += 280 * $day;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        // 20th day
        $expected += 66 * $day;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        // 300th day
        $rrule->next();
        $expected += 19 * $day;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        $expected += 280 * $day;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        // 1st day (leap year, we have 366 days in this year)
        $expected += 67 * $day;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        $expected += 19 * $day;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardYearlyByWeekNo()
    {
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone('zulu'));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(99999, 1, 1);
        $rrule = new RRuleIterator('FREQ=YEARLY;BYWEEKNO=1,20', $startDate);

        $this->fastForward($rrule, $ffDate);

        $day = 60 * 60 * 24;
        $week = 7 * $day;
        $expected = (new DateTime())
            ->setTimezone(new DateTimeZone('zulu'))
            ->setDate(99999, 1, 4)// 1st day
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        $rrule->next();
        $expected += $week * 19;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardYearlyAdvanced()
    {
        $startDate = new \DateTime('1970-10-23 12:34:56', new \DateTimeZone('zulu'));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(9999, 1, 20)->setTime(0, 0, 13);
        $rrule = new RRuleIterator('FREQ=YEARLY;INTERVAL=2;BYMONTH=1;BYDAY=SU;BYHOUR=8,9;BYMINUTE=30', $startDate);

        $this->fastForward($rrule, $ffDate);

        $expected = (new DateTime('midnight', new DateTimeZone('zulu')))
            ->setDate(10000, 1, 2)
            ->setTime(8, 30, 56)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $rrule->next();
        $expected += 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $rrule->next();
        $expected += 7 * 24 * 60 * 60 - 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $rrule->next();
        $expected += 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $rrule->next();
        $expected += 7 * 24 * 60 * 60 - 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $rrule->next();
        $expected += 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $rrule->next();
        $expected += 7 * 24 * 60 * 60 - 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $rrule->next();
        $expected += 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $rrule->next();
        $expected += 7 * 24 * 60 * 60 - 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $rrule->next();
        $expected += 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // jump to 6th january 10002
        $rrule->next();
        $expected = (new DateTime('midnight', new DateTimeZone('zulu')))
            ->setDate(10002, 1, 6)
            ->setTime(8, 30, 56)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $rrule->next();
        $expected += 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardMonthlyBasic()
    {
        $startDate = new \DateTime('1970-10-23 22:42:31', new \DateTimeZone('zulu'));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(18000, 1, 1);
        $rrule = new RRuleIterator('FREQ=MONTHLY', $startDate);

        $this->fastForward($rrule, $ffDate);

        $expected = (new DateTime('midnight', new DateTimeZone('zulu')))
            ->setDate(18000, 1, 23)
            ->setTime(22, 42, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // february
        $rrule->next();
        $expected += 31 * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        // march
        $rrule->next();
        $expected += 29 * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        // april
        $rrule->next();
        $expected += 31 * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        // may
        $rrule->next();
        $expected += 30 * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        // june
        $rrule->next();
        $expected += 31 * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        // july
        $rrule->next();
        $expected += 30 * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
        // august
        $rrule->next();
        $expected += 31 * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardMonthly31thDay()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-01-31 00:00:00', new \DateTimeZone($timezone));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(18000, 1, 1);
        $rrule = new RRuleIterator('FREQ=MONTHLY', $startDate);

        $this->fastForward($rrule, $ffDate);

        $expected = (new DateTime('midnight', new DateTimeZone('America/New_York')))
            ->setDate(18000, 1, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // march
        $rrule->next();
        $expected = (new DateTime('midnight', new DateTimeZone('America/New_York')))
            ->setDate(18000, 3, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // may
        $rrule->next();
        $expected += (30 + 31) * 24 * 60 * 60;
        $expected = (new DateTime('midnight', new DateTimeZone('America/New_York')))
            ->setDate(18000, 5, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // july
        $rrule->next();
        $expected = (new DateTime('midnight', new DateTimeZone('America/New_York')))
            ->setDate(18000, 7, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // august
        $rrule->next();
        $expected = (new DateTime('midnight', new DateTimeZone('America/New_York')))
            ->setDate(18000, 8, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // october
        $rrule->next();
        $expected = (new DateTime('midnight', new DateTimeZone('America/New_York')))
            ->setDate(18000, 10, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // december
        $rrule->next();
        $expected = (new DateTime('midnight', new DateTimeZone('America/New_York')))
            ->setDate(18000, 12, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardMonthlyAdvanced()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-01-31 00:00:00', new DateTimeZone($timezone));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(8000, 1, 1);
        $rrule = new RRuleIterator('FREQ=MONTHLY;INTERVAL=2;BYDAY=1MO,2TU,3WE,4TH', $startDate);

        $this->fastForward($rrule, $ffDate);

        // monday
        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(8000, 1, 3)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // tuesday
        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(8000, 1, 11)
            ->getTimestamp();
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // wednesday
        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(8000, 1, 19)
            ->getTimestamp();
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // thursday
        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(8000, 1, 27)
            ->getTimestamp();
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // monday march
        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(8000, 3, 6)
            ->getTimestamp();
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // tuesday
        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(8000, 3, 14)
            ->getTimestamp();
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // wednesday (this month starts on wednesday so that's just the next day)
        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(8000, 3, 15)
            ->getTimestamp();
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // thursday
        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(8000, 3, 23)
            ->getTimestamp();
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardDailyBasic()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone($timezone));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(4000, 1, 1);
        $rrule = new RRuleIterator('FREQ=DAILY', $startDate);

        $this->fastForward($rrule, $ffDate);

        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(4000, 1, 1)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $expected += 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $expected += 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $expected += 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $expected += 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $expected += 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $expected += 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardDailyAdvanced()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone($timezone));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(4000, 1, 1);
        $rrule = new RRuleIterator('FREQ=DAILY;BYHOUR=16,17,18;INTERVAL=10', $startDate);

        $this->fastForward($rrule, $ffDate);

        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(4000, 1, 4)
            ->setTime(16, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 17:00
        $expected += 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 18:00
        $expected += 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 16:00
        $expected += 10 * 24 * 60 * 60 - 2 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 17:00
        $expected += 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 18:00
        $expected += 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 16:00
        $expected += 10 * 24 * 60 * 60 - 2 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }
}
