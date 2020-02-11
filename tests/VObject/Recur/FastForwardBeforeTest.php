<?php

namespace Sabre\VObject\Recur;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class FastForwardBeforeTest extends TestCase
{
    const FF_TIMEOUT = 1000000; // in usec

    private function fastForward(RRuleIterator $ruleIterator, \DateTimeInterface $ffDate)
    {
        $ru = getrusage();
        $startTime = $ru['ru_utime.tv_sec'] * 1000000 + $ru['ru_utime.tv_usec'];
        $ruleIterator->fastForwardBefore($ffDate);
        $ru = getrusage();
        $endTime = $ru['ru_utime.tv_sec'] * 1000000 + $ru['ru_utime.tv_usec'];
        $this->assertLessThan(self::FF_TIMEOUT, $endTime - $startTime);
    }

    public function testFastForwardBeforeYearlyBasic()
    {
        $startDate = new DateTime('1970-10-23 00:00:00', new DateTimeZone('zulu'));
        $ffDate = new DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(99999, 1, 1);
        $rrule = new RRuleIterator('FREQ=YEARLY', $startDate);

        $this->fastForward($rrule, $ffDate);

        $year = 60 * 60 * 24 * 365;
        $expected = (new DateTime())
            ->setTimezone(new DateTimeZone('zulu'))
            ->setDate(99998, 10, 23)
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $rrule->next();
        $expected += $year;
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

    public function testFastForwardBeforeYearlyByYearDay()
    {
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone('zulu'));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(99999, 1, 5);
        $rrule = new RRuleIterator('FREQ=YEARLY;BYYEARDAY=1,20,300', $startDate);

        $this->fastForward($rrule, $ffDate);

        // 1st day
        $day = 60 * 60 * 24;
        $expected = (new DateTime())
            ->setTimezone(new DateTimeZone('zulu'))
            ->setDate(99999, 1, 1)
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 20th day
        $rrule->next();
        $expected += 19 * $day;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 300th day
        $rrule->next();
        $expected += 280 * $day;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 1st day
        $expected += 66 * $day;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 20th day
        $rrule->next();
        $expected += 19 * $day;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 300th day
        $rrule->next();
        $expected += 280 * $day;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 1st day (leap year, we have 366 days in this year)
        $rrule->next();
        $expected += 67 * $day;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 20th day
        $rrule->next();
        $expected += 19 * $day;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardBeforeYearlyByWeekNo()
    {
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone('zulu'));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(99999, 1, 5);
        $rrule = new RRuleIterator('FREQ=YEARLY;BYWEEKNO=1,20', $startDate);

        $this->fastForward($rrule, $ffDate);

        $day = 60 * 60 * 24;
        $week = 7 * $day;

        // 1st week
        $expected = (new DateTime())
            ->setTimezone(new DateTimeZone('zulu'))
            ->setDate(99999, 1, 4)
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // 20st week
        $rrule->next();
        $expected += $week * 19;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardBeforeYearlyAdvanced()
    {
        $startDate = new \DateTime('1970-10-23 12:34:56', new \DateTimeZone('zulu'));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(10000, 1, 2)->setTime(8, 44, 13);
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

    public function testFastForwardBeforeMonthlyBasic()
    {
        $startDate = new \DateTime('1970-10-23 22:42:31', new \DateTimeZone('zulu'));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(18000, 1, 30);
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

    public function testFastForwardBeforeMonthly31thDay()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-01-31 00:00:00', new \DateTimeZone($timezone));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(18000, 2, 1);
        $rrule = new RRuleIterator('FREQ=MONTHLY', $startDate);

        $this->fastForward($rrule, $ffDate);

        $expected = (new DateTime('midnight', new DateTimeZone('America/New_York')))
            ->setDate(18000, 1, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // march
        $rrule->next();
        $expected += (29 + 31) * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // may
        $rrule->next();
        $expected += (30 + 31) * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // july
        $rrule->next();
        $expected += (30 + 31) * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // august
        $rrule->next();
        $expected += 31 * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // october
        $rrule->next();
        $expected += (30 + 31) * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // december
        $rrule->next();
        $expected += (30 + 31) * 24 * 60 * 60;
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardBeforeMonthlyAdvanced()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-01-31 00:00:00', new DateTimeZone($timezone));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(8000, 1, 6);
        // every 2 months on the 1st Monday, 2nd Tuesday, 3rd Wednesday and 4th Thursday
        $rrule = new RRuleIterator('FREQ=MONTHLY;INTERVAL=2;BYDAY=1MO,2TU,3WE,4TH', $startDate);

        $this->fastForward($rrule, $ffDate);

        // monday
        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(8000, 1, 3)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // tuesday
        $expected += 8 * 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // wednesday
        $expected += 8 * 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // thursday
        $expected += 8 * 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // monday march
        $expected += (29 + 10) * 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // tuesday
        $expected += 8 * 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // wednesday (this month starts on wednesday so that's just the next day)
        $expected += 1 * 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // thursday
        $expected += 8 * 24 * 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardBeforeDailyBasic()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone($timezone));
        $ffDate = new \DateTime('midnight', new DateTimeZone('zulu'));
        $ffDate->setDate(4000, 1, 2);
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

    public function testFastForwardBeforeDailyAdvanced()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone($timezone));
        $ffDate = new \DateTime('midnight', new DateTimeZone($timezone));
        $ffDate->setDate(4000, 1, 4)->setTime(16, 30, 0);
        // every 10 days at 16, 17 and 18
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

    public function testFastForwardBeforeHourlyBasic()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-10-23 00:12:34', new \DateTimeZone($timezone));
        $ffDate = new \DateTime('midnight', new DateTimeZone($timezone));
        $ffDate->setDate(4000, 1, 2)->setTime(2, 0, 0);
        $rrule = new RRuleIterator('FREQ=HOURLY', $startDate);

        $this->fastForward($rrule, $ffDate);

        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(4000, 1, 2)
            ->setTime(1, 12, 34)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $expected += 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $expected += 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $expected += 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $expected += 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $expected += 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        $expected += 60 * 60;
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardBeforeNotInFrequency()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone($timezone));
        $ffDate = new \DateTime('midnight', new DateTimeZone($timezone));
        $ffDate->setDate(2023, 3, 15)->setTime(1, 0, 0);
        // every leap years
        $rrule = new RRuleIterator('FREQ=YEARLY;BYMONTH=2;BYMONTHDAY=29', $startDate);

        $this->fastForward($rrule, $ffDate);

        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(2020, 2, 29)
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());

        // the next leap year
        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(2024, 2, 29)
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $rrule->next();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardBeforeMultipleTimesBasic()
    {
        $startDate = new DateTime('2020-01-02 00:00:00', new DateTimeZone('zulu'));
        $ffDate = new DateTime('2020-01-18 00:00:00', new DateTimeZone('zulu'));
        $rrule = new RRuleIterator('FREQ=WEEKLY', $startDate);
        $expected = new DateTime('2020-01-16 00:00:00', new DateTimeZone('zulu'));

        $this->fastForward($rrule, $ffDate);
        $this->assertEquals($expected->getTimestamp(), $rrule->current()->getTimestamp());

        $this->fastForward($rrule, $ffDate);
        $this->assertEquals($expected->getTimestamp(), $rrule->current()->getTimestamp());

        $this->fastForward($rrule, $ffDate);
        $this->assertEquals($expected->getTimestamp(), $rrule->current()->getTimestamp());
    }
}
