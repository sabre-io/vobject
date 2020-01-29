<?php

namespace Sabre\VObject\Recur;

use DateTime;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class FastForwardToEndTest extends TestCase
{
    const FF_TIMEOUT = 1000000; // in usec

    private function fastForwardToEnd(RRuleIterator $ruleIterator, $enfoceTiming = true)
    {
        $ru = getrusage();
        $startTime = $ru['ru_utime.tv_sec'] * 1000000 + $ru['ru_utime.tv_usec'];
        $ruleIterator->fastForwardToEnd();
        $ru = getrusage();
        $endTime = $ru['ru_utime.tv_sec'] * 1000000 + $ru['ru_utime.tv_usec'];
        $enfoceTiming && $this->assertLessThan(self::FF_TIMEOUT, $endTime - $startTime);
        $this->assertTrue($ruleIterator->valid());
        $this->assertNotNull($ruleIterator->current());
    }

    public function testFastForwardToEndWithoutEndYearlyBasic()
    {
        $startDate = new DateTime('1970-10-23 00:00:00', new DateTimeZone('zulu'));
        $rrule = new RRuleIterator('FREQ=YEARLY', $startDate);

        $this->expectException(\LogicException::class);
        $rrule->fastForwardToEnd();
    }

    public function testFastForwardToEndCountYearlyBasic()
    {
        $startDate = new DateTime('1970-10-23 00:00:00', new DateTimeZone('zulu'));
        $rrule = new RRuleIterator('FREQ=YEARLY;COUNT=7777', $startDate);

        // We do not enforce the timing in case of a count rule as we cannot optimize it
        $this->fastForwardToEnd($rrule, false);

        $expected = (new DateTime())
            ->setTimezone(new DateTimeZone('zulu'))
            ->setDate(9746, 10, 23)
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndUntilYearlyBasic()
    {
        $startDate = new DateTime('1970-10-23 00:00:00', new DateTimeZone('zulu'));
        $rrule = new RRuleIterator('FREQ=YEARLY;UNTIL=97461212T000000', $startDate);

        $this->fastForwardToEnd($rrule);

        $expected = (new DateTime())
            ->setTimezone(new DateTimeZone('zulu'))
            ->setDate(9746, 10, 23)
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndCountYearlyByYearDay()
    {
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone('zulu'));
        $rrule = new RRuleIterator('FREQ=YEARLY;BYYEARDAY=1,20,300;COUNT=10000', $startDate);

        // We do not enforce the timing in case of a count rule as we cannot optimize it
        $this->fastForwardToEnd($rrule, false);

        $expected = (new DateTime())
            ->setTimezone(new DateTimeZone('zulu'))
            ->setDate(5303, 1, 20)
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndUntilYearlyByYearDay()
    {
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone('zulu'));
        $rrule = new RRuleIterator('FREQ=YEARLY;BYYEARDAY=1,20,300;UNTIL=53030808T000000', $startDate);

        $this->fastForwardToEnd($rrule);

        $expected = (new DateTime())
            ->setTimezone(new DateTimeZone('zulu'))
            ->setDate(5303, 1, 20)
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    /*
     * Issue CALENDAR-587
    public function testFastForwardToEndCountYearlyByWeekNo()
    {
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone('zulu'));
        $rrule = new RRuleIterator('FREQ=YEARLY;BYWEEKNO=1,20;COUNT=100', $startDate);

        // We do not enforce the timing in case of a count rule as we cannot optimize it
        $this->fastForwardToEnd($rrule, false);

        $expected = (new DateTime())
            ->setTimezone(new DateTimeZone('zulu'))
            ->setDate(2019, 12, 30)
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndUntilYearlyByWeekNo()
    {
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone('zulu'));
        $rrule = new RRuleIterator('FREQ=YEARLY;BYWEEKNO=1,20;UNTIL=20030808T000000', $startDate);

        $this->fastForwardToEnd($rrule);

        $expected = (new DateTime())
            ->setTimezone(new DateTimeZone('zulu'))
            ->setDate(2019, 12, 30)
            ->setTime(0, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }
    */

    public function testFastForwardToEndCountYearlyAdvanced()
    {
        $startDate = new \DateTime('1970-10-23 12:34:56', new \DateTimeZone('zulu'));
        $rrule = new RRuleIterator('FREQ=YEARLY;INTERVAL=2;BYMONTH=1;BYDAY=SU;BYHOUR=8,9;BYMINUTE=30;COUNT=10000', $startDate);

        // We do not enforce the timing in case of a count rule as we cannot optimize it
        $this->fastForwardToEnd($rrule, false);

        $expected = (new DateTime('midnight', new DateTimeZone('zulu')))
            ->setDate(4226, 1, 1)
            ->setTime(8, 30, 56)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndUntilYearlyAdvanced()
    {
        $startDate = new \DateTime('1970-10-23 12:34:56', new \DateTimeZone('zulu'));
        $rrule = new RRuleIterator('FREQ=YEARLY;INTERVAL=2;BYMONTH=1;BYDAY=SU;BYHOUR=8,9;BYMINUTE=30;UNTIL=42180125T092500', $startDate);

        $this->fastForwardToEnd($rrule);

        $expected = (new DateTime('midnight', new DateTimeZone('zulu')))
            ->setDate(4218, 1, 25)
            ->setTime(8, 30, 56)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndCountMonthlyBasic()
    {
        $startDate = new \DateTime('1970-10-23 22:42:31', new \DateTimeZone('zulu'));
        $rrule = new RRuleIterator('FREQ=MONTHLY;COUNT=10000', $startDate);

        // We do not enforce the timing in case of a count rule as we cannot optimize it
        $this->fastForwardToEnd($rrule, false);

        $expected = (new DateTime('midnight', new DateTimeZone('zulu')))
            ->setDate(2804, 1, 23)
            ->setTime(22, 42, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndUntilMonthlyBasic()
    {
        $startDate = new \DateTime('1970-10-23 22:42:31', new \DateTimeZone('zulu'));
        $rrule = new RRuleIterator('FREQ=MONTHLY;UNTIL=28040122T092500', $startDate);

        $this->fastForwardToEnd($rrule);

        $expected = (new DateTime('midnight', new DateTimeZone('zulu')))
            ->setDate(2803, 12, 23)
            ->setTime(22, 42, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    /**
     * FIXME fails in <=PHP 7.1
     * @requires PHP 7.2
     */    public function testFastForwardToEndCountMonthly31thDay()
    {
        $startDate = new \DateTime('1970-01-31 00:00:00', new \DateTimeZone('America/New_York'));
        $rrule = new RRuleIterator('FREQ=MONTHLY;COUNT=10000', $startDate);

        // We do not enforce the timing in case of a count rule as we cannot optimize it
        $this->fastForwardToEnd($rrule, false);

        $expected = (new DateTime('midnight', new DateTimeZone('America/New_York')))
            ->setDate(3398, 10, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndUntilMonthly31thDay()
    {
        $startDate = new \DateTime('1970-01-31 00:00:00', new \DateTimeZone('America/New_York'));
        $rrule = new RRuleIterator('FREQ=MONTHLY;UNTIL=33980909T092500', $startDate);

        $this->fastForwardToEnd($rrule);

        $expected = (new DateTime('midnight', new DateTimeZone('America/New_York')))
            ->setDate(3398, 8, 31)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndCountMonthlyAdvanced()
    {
        $startDate = new \DateTime('1970-01-31 00:00:00', new DateTimeZone('America/New_York'));
        // every 2 months on the 1st Monday, 2nd Tuesday, 3rd Wednesday and 4th Thursday
        $rrule = new RRuleIterator('FREQ=MONTHLY;INTERVAL=2;BYDAY=1MO,2TU,3WE,4TH;COUNT=10000', $startDate);

        // We do not enforce the timing in case of a count rule as we cannot optimize it
        $this->fastForwardToEnd($rrule, false);

        $expected = (new DateTime('midnight', new DateTimeZone('America/New_York')))
            ->setDate(2386, 9, 17)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndUntilMonthlyAdvanced()
    {
        $startDate = new \DateTime('1970-01-31 00:00:00', new DateTimeZone('America/New_York'));
        // every 2 months on the 1st Monday, 2nd Tuesday, 3rd Wednesday and 4th Thursday
        $rrule = new RRuleIterator('FREQ=MONTHLY;INTERVAL=2;BYDAY=1MO,2TU,3WE,4TH;UNTIL=23860914T092500', $startDate);

        $this->fastForwardToEnd($rrule);

        $expected = (new DateTime('midnight', new DateTimeZone('America/New_York')))
            ->setDate(2386, 9, 9)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndCountDailyBasic()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone($timezone));
        $rrule = new RRuleIterator('FREQ=DAILY;COUNT=100000', $startDate);

        // We do not enforce the timing in case of a count rule as we cannot optimize it
        $this->fastForwardToEnd($rrule, false);

        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(2244, 8, 6)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndUntilDailyBasic()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone($timezone));
        $rrule = new RRuleIterator('FREQ=DAILY;UNTIL=22440806T092500', $startDate);

        $this->fastForwardToEnd($rrule);

        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(2244, 8, 6)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndCountDailyAdvanced()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone($timezone));
        // every 10 days at 16, 17 and 18
        $rrule = new RRuleIterator('FREQ=DAILY;BYHOUR=16,17,18;INTERVAL=10;COUNT=10000', $startDate);

        // We do not enforce the timing in case of a count rule as we cannot optimize it
        $this->fastForwardToEnd($rrule, false);

        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(2062, 1, 13)
            ->setTime(18, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndUntilDailyAdvanced()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-10-23 00:00:00', new \DateTimeZone($timezone));
        // every 10 days at 16, 17 and 18
        $rrule = new RRuleIterator('FREQ=DAILY;BYHOUR=16,17,18;INTERVAL=10;UNTIL=20620113T183456', $startDate);

        $this->fastForwardToEnd($rrule);

        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(2062, 1, 13)
            ->setTime(18, 0, 0)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndCountHourlyBasic()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-10-23 00:12:34', new \DateTimeZone($timezone));
        $rrule = new RRuleIterator('FREQ=HOURLY;COUNT=100000', $startDate);

        // We do not enforce the timing in case of a count rule as we cannot optimize it
        $this->fastForwardToEnd($rrule, false);

        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(1982, 3, 21)
            ->setTime(2, 12, 34)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }

    public function testFastForwardToEndUntilHourlyBasic()
    {
        $timezone = 'America/New_York';
        $startDate = new \DateTime('1970-10-23 00:12:34', new \DateTimeZone($timezone));
        $rrule = new RRuleIterator('FREQ=HOURLY;UNTIL=19820321T024032', $startDate);

        $this->fastForwardToEnd($rrule);

        $expected = (new DateTime('midnight', new DateTimeZone($timezone)))
            ->setDate(1982, 3, 21)
            ->setTime(2, 12, 34)
            ->getTimestamp();
        $this->assertEquals($expected, $rrule->current()->getTimestamp());
    }
}
