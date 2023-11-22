<?php

namespace Sabre\VObject\Recur;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\InvalidDataException;

class RRuleIteratorTest extends TestCase
{
    public function testHourly(): void
    {
        $this->parse(
            'FREQ=HOURLY;INTERVAL=3;COUNT=12',
            '2011-10-07 12:00:00',
            [
                '2011-10-07 12:00:00',
                '2011-10-07 15:00:00',
                '2011-10-07 18:00:00',
                '2011-10-07 21:00:00',
                '2011-10-08 00:00:00',
                '2011-10-08 03:00:00',
                '2011-10-08 06:00:00',
                '2011-10-08 09:00:00',
                '2011-10-08 12:00:00',
                '2011-10-08 15:00:00',
                '2011-10-08 18:00:00',
                '2011-10-08 21:00:00',
            ]
        );
    }

    public function testDaily(): void
    {
        $this->parse(
            'FREQ=DAILY;INTERVAL=3;UNTIL=20111025T000000Z',
            '2011-10-07',
            [
                '2011-10-07 00:00:00',
                '2011-10-10 00:00:00',
                '2011-10-13 00:00:00',
                '2011-10-16 00:00:00',
                '2011-10-19 00:00:00',
                '2011-10-22 00:00:00',
                '2011-10-25 00:00:00',
            ]
        );
    }

    public function testDailyByDayByHour(): void
    {
        $this->parse(
            'FREQ=DAILY;BYDAY=SA,SU;BYHOUR=6,7',
            '2011-10-08 06:00:00',
            [
                '2011-10-08 06:00:00',
                '2011-10-08 07:00:00',
                '2011-10-09 06:00:00',
                '2011-10-09 07:00:00',
                '2011-10-15 06:00:00',
                '2011-10-15 07:00:00',
                '2011-10-16 06:00:00',
                '2011-10-16 07:00:00',
                '2011-10-22 06:00:00',
                '2011-10-22 07:00:00',
                '2011-10-23 06:00:00',
                '2011-10-23 07:00:00',
            ]
        );
    }

    public function testDailyByHour(): void
    {
        $this->parse(
            'FREQ=DAILY;INTERVAL=2;BYHOUR=10,11,12,13,14,15',
            '2012-10-11 12:00:00',
            [
                '2012-10-11 12:00:00',
                '2012-10-11 13:00:00',
                '2012-10-11 14:00:00',
                '2012-10-11 15:00:00',
                '2012-10-13 10:00:00',
                '2012-10-13 11:00:00',
                '2012-10-13 12:00:00',
                '2012-10-13 13:00:00',
                '2012-10-13 14:00:00',
                '2012-10-13 15:00:00',
                '2012-10-15 10:00:00',
                '2012-10-15 11:00:00',
            ]
        );
    }

    public function testDailyByDay(): void
    {
        $this->parse(
            'FREQ=DAILY;INTERVAL=2;BYDAY=TU,WE,FR',
            '2011-10-07 12:00:00',
            [
                '2011-10-07 12:00:00',
                '2011-10-11 12:00:00',
                '2011-10-19 12:00:00',
                '2011-10-21 12:00:00',
                '2011-10-25 12:00:00',
                '2011-11-02 12:00:00',
                '2011-11-04 12:00:00',
                '2011-11-08 12:00:00',
                '2011-11-16 12:00:00',
                '2011-11-18 12:00:00',
                '2011-11-22 12:00:00',
                '2011-11-30 12:00:00',
            ]
        );
    }

    public function testDailyCount(): void
    {
        $this->parse(
            'FREQ=DAILY;COUNT=5',
            '2014-08-01 18:03:00',
            [
                '2014-08-01 18:03:00',
                '2014-08-02 18:03:00',
                '2014-08-03 18:03:00',
                '2014-08-04 18:03:00',
                '2014-08-05 18:03:00',
            ]
        );
    }

    public function testDailyByMonth(): void
    {
        $this->parse(
            'FREQ=DAILY;BYMONTH=9,10;BYDAY=SU',
            '2007-10-04 16:00:00',
            [
                '2013-09-29 16:00:00',
                '2013-10-06 16:00:00',
                '2013-10-13 16:00:00',
                '2013-10-20 16:00:00',
                '2013-10-27 16:00:00',
                '2014-09-07 16:00:00',
            ],
            '2013-09-28'
        );
    }

    /**
     * This test can take some seconds to complete.
     * The "large" annotation means phpunit will let it run for
     * up to 60 seconds by default.
     *
     * @large
     */
    public function testDailyBySetPosLoop(): void
    {
        $this->parse(
            'FREQ=DAILY;INTERVAL=7;BYDAY=MO',
            '2022-03-15',
            [
            ],
            '2022-05-01'
        );
    }

    public function testWeekly(): void
    {
        $this->parse(
            'FREQ=WEEKLY;INTERVAL=2;COUNT=10',
            '2011-10-07 00:00:00',
            [
                '2011-10-07 00:00:00',
                '2011-10-21 00:00:00',
                '2011-11-04 00:00:00',
                '2011-11-18 00:00:00',
                '2011-12-02 00:00:00',
                '2011-12-16 00:00:00',
                '2011-12-30 00:00:00',
                '2012-01-13 00:00:00',
                '2012-01-27 00:00:00',
                '2012-02-10 00:00:00',
            ]
        );
    }

    public function testWeeklyByDay(): void
    {
        $this->parse(
            'FREQ=WEEKLY;INTERVAL=1;COUNT=4;BYDAY=MO;WKST=SA',
            '2014-08-01 00:00:00',
            [
                '2014-08-01 00:00:00',
                '2014-08-04 00:00:00',
                '2014-08-11 00:00:00',
                '2014-08-18 00:00:00',
            ]
        );
    }

    public function testWeeklyByDay2(): void
    {
        $this->parse(
            'FREQ=WEEKLY;INTERVAL=2;BYDAY=TU,WE,FR;WKST=SU',
            '2011-10-07 00:00:00',
            [
                '2011-10-07 00:00:00',
                '2011-10-18 00:00:00',
                '2011-10-19 00:00:00',
                '2011-10-21 00:00:00',
                '2011-11-01 00:00:00',
                '2011-11-02 00:00:00',
                '2011-11-04 00:00:00',
                '2011-11-15 00:00:00',
                '2011-11-16 00:00:00',
                '2011-11-18 00:00:00',
                '2011-11-29 00:00:00',
                '2011-11-30 00:00:00',
            ]
        );
    }

    public function testWeeklyByDayByHour(): void
    {
        $this->parse(
            'FREQ=WEEKLY;INTERVAL=2;BYDAY=TU,WE,FR;WKST=MO;BYHOUR=8,9,10',
            '2011-10-07 08:00:00',
            [
                '2011-10-07 08:00:00',
                '2011-10-07 09:00:00',
                '2011-10-07 10:00:00',
                '2011-10-18 08:00:00',
                '2011-10-18 09:00:00',
                '2011-10-18 10:00:00',
                '2011-10-19 08:00:00',
                '2011-10-19 09:00:00',
                '2011-10-19 10:00:00',
                '2011-10-21 08:00:00',
                '2011-10-21 09:00:00',
                '2011-10-21 10:00:00',
                '2011-11-01 08:00:00',
                '2011-11-01 09:00:00',
                '2011-11-01 10:00:00',
            ]
        );
    }

    public function testWeeklyByDaySpecificHour(): void
    {
        $this->parse(
            'FREQ=WEEKLY;INTERVAL=2;BYDAY=TU,WE,FR;WKST=SU',
            '2011-10-07 18:00:00',
            [
                '2011-10-07 18:00:00',
                '2011-10-18 18:00:00',
                '2011-10-19 18:00:00',
                '2011-10-21 18:00:00',
                '2011-11-01 18:00:00',
                '2011-11-02 18:00:00',
                '2011-11-04 18:00:00',
                '2011-11-15 18:00:00',
                '2011-11-16 18:00:00',
                '2011-11-18 18:00:00',
                '2011-11-29 18:00:00',
                '2011-11-30 18:00:00',
            ]
        );
    }

    public function testMonthly(): void
    {
        $this->parse(
            'FREQ=MONTHLY;INTERVAL=3;COUNT=5',
            '2011-12-05 00:00:00',
            [
                 '2011-12-05 00:00:00',
                 '2012-03-05 00:00:00',
                 '2012-06-05 00:00:00',
                 '2012-09-05 00:00:00',
                 '2012-12-05 00:00:00',
            ]
        );
    }

    public function testMonthlyEndOfMonth(): void
    {
        $this->parse(
            'FREQ=MONTHLY;INTERVAL=2;COUNT=12',
            '2011-12-31 00:00:00',
            [
                '2011-12-31 00:00:00',
                '2012-08-31 00:00:00',
                '2012-10-31 00:00:00',
                '2012-12-31 00:00:00',
                '2013-08-31 00:00:00',
                '2013-10-31 00:00:00',
                '2013-12-31 00:00:00',
                '2014-08-31 00:00:00',
                '2014-10-31 00:00:00',
                '2014-12-31 00:00:00',
                '2015-08-31 00:00:00',
                '2015-10-31 00:00:00',
            ]
        );
    }

    public function testMonthlyByMonthDay(): void
    {
        $this->parse(
            'FREQ=MONTHLY;INTERVAL=5;COUNT=9;BYMONTHDAY=1,31,-7',
            '2011-01-01 00:00:00',
            [
                '2011-01-01 00:00:00',
                '2011-01-25 00:00:00',
                '2011-01-31 00:00:00',
                '2011-06-01 00:00:00',
                '2011-06-24 00:00:00',
                '2011-11-01 00:00:00',
                '2011-11-24 00:00:00',
                '2012-04-01 00:00:00',
                '2012-04-24 00:00:00',
            ]
        );
    }

    public function testMonthlyByDay(): void
    {
        $this->parse(
            'FREQ=MONTHLY;INTERVAL=2;COUNT=16;BYDAY=MO,-2TU,+1WE,3TH',
            '2011-01-03 00:00:00',
            [
                '2011-01-03 00:00:00',
                '2011-01-05 00:00:00',
                '2011-01-10 00:00:00',
                '2011-01-17 00:00:00',
                '2011-01-18 00:00:00',
                '2011-01-20 00:00:00',
                '2011-01-24 00:00:00',
                '2011-01-31 00:00:00',
                '2011-03-02 00:00:00',
                '2011-03-07 00:00:00',
                '2011-03-14 00:00:00',
                '2011-03-17 00:00:00',
                '2011-03-21 00:00:00',
                '2011-03-22 00:00:00',
                '2011-03-28 00:00:00',
                '2011-05-02 00:00:00',
            ]
        );
    }

    public function testMonthlyByDayUntil(): void
    {
        $this->parse(
            'FREQ=MONTHLY;INTERVAL=1;BYDAY=WE;WKST=WE;UNTIL=20210317T000000Z',
            '2021-02-10 00:00:00',
            [
                '2021-02-10 00:00:00',
                '2021-02-17 00:00:00',
                '2021-02-24 00:00:00',
                '2021-03-03 00:00:00',
                '2021-03-10 00:00:00',
                '2021-03-17 00:00:00',
            ]
        );
    }

    public function testMonthlyByDayUntilWithImpossibleNextOccurrence(): void
    {
        $this->parse(
            'FREQ=MONTHLY;INTERVAL=1;BYDAY=2WE;BYMONTHDAY=2;WKST=WE;UNTIL=20210317T000000Z',
            '2021-02-10 00:00:00',
            [
                '2021-02-10 00:00:00',
            ]
        );
    }

    public function testMonthlyByDayByMonthDay(): void
    {
        $this->parse(
            'FREQ=MONTHLY;COUNT=10;BYDAY=MO;BYMONTHDAY=1',
            '2011-08-01 00:00:00',
            [
                '2011-08-01 00:00:00',
                '2012-10-01 00:00:00',
                '2013-04-01 00:00:00',
                '2013-07-01 00:00:00',
                '2014-09-01 00:00:00',
                '2014-12-01 00:00:00',
                '2015-06-01 00:00:00',
                '2016-02-01 00:00:00',
                '2016-08-01 00:00:00',
                '2017-05-01 00:00:00',
            ]
        );
    }

    public function testMonthlyByDayBySetPos(): void
    {
        $this->parse(
            'FREQ=MONTHLY;COUNT=10;BYDAY=MO,TU,WE,TH,FR;BYSETPOS=1,-1',
            '2011-01-03 00:00:00',
            [
                '2011-01-03 00:00:00',
                '2011-01-31 00:00:00',
                '2011-02-01 00:00:00',
                '2011-02-28 00:00:00',
                '2011-03-01 00:00:00',
                '2011-03-31 00:00:00',
                '2011-04-01 00:00:00',
                '2011-04-29 00:00:00',
                '2011-05-02 00:00:00',
                '2011-05-31 00:00:00',
            ]
        );
    }

    public function testYearly(): void
    {
        $this->parse(
            'FREQ=YEARLY;COUNT=10;INTERVAL=3',
            '2011-01-01 00:00:00',
            [
                '2011-01-01 00:00:00',
                '2014-01-01 00:00:00',
                '2017-01-01 00:00:00',
                '2020-01-01 00:00:00',
                '2023-01-01 00:00:00',
                '2026-01-01 00:00:00',
                '2029-01-01 00:00:00',
                '2032-01-01 00:00:00',
                '2035-01-01 00:00:00',
                '2038-01-01 00:00:00',
            ]
        );
    }

    public function testYearlyLeapYear(): void
    {
        $this->parse(
            'FREQ=YEARLY;COUNT=3',
            '2012-02-29 00:00:00',
            [
                '2012-02-29 00:00:00',
                '2016-02-29 00:00:00',
                '2020-02-29 00:00:00',
            ]
        );
    }

    public function testYearlyByMonth(): void
    {
        $this->parse(
            'FREQ=YEARLY;COUNT=8;INTERVAL=4;BYMONTH=4,10',
            '2011-04-07 00:00:00',
            [
                '2011-04-07 00:00:00',
                '2011-10-07 00:00:00',
                '2015-04-07 00:00:00',
                '2015-10-07 00:00:00',
                '2019-04-07 00:00:00',
                '2019-10-07 00:00:00',
                '2023-04-07 00:00:00',
                '2023-10-07 00:00:00',
            ]
        );
    }

    public function testYearlyByMonthInvalidValue1(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->parse(
            'FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=0',
            '2011-04-07 00:00:00',
            []
        );
    }

    public function testYearlyByMonthInvalidValue2(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->parse(
            'FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=bla',
            '2011-04-07 00:00:00',
            []
        );
    }

    public function testYearlyByMonthManyInvalidValues(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->parse(
            'FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=0,bla',
            '2011-04-07 00:00:00',
            []
        );
    }

    public function testYearlyByMonthEmptyValue(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->parse(
            'FREQ=YEARLY;COUNT=6;BYMONTHDAY=24;BYMONTH=',
            '2011-04-07 00:00:00',
            []
        );
    }

    public function testYearlyByMonthByDay(): void
    {
        $this->parse(
            'FREQ=YEARLY;COUNT=8;INTERVAL=5;BYMONTH=4,10;BYDAY=1MO,-1SU',
            '2011-04-04 00:00:00',
            [
                '2011-04-04 00:00:00',
                '2011-04-24 00:00:00',
                '2011-10-03 00:00:00',
                '2011-10-30 00:00:00',
                '2016-04-04 00:00:00',
                '2016-04-24 00:00:00',
                '2016-10-03 00:00:00',
                '2016-10-30 00:00:00',
            ]
        );
    }

    public function testYearlyNewYearsDay(): void
    {
        $this->parse(
            'FREQ=YEARLY;COUNT=7;INTERVAL=2;BYYEARDAY=1',
            '2011-01-01 03:07:00',
            [
                '2011-01-01 03:07:00',
                '2013-01-01 03:07:00',
                '2015-01-01 03:07:00',
                '2017-01-01 03:07:00',
                '2019-01-01 03:07:00',
                '2021-01-01 03:07:00',
                '2023-01-01 03:07:00',
            ]
        );
    }

    public function testYearlyByYearDay(): void
    {
        $this->parse(
            'FREQ=YEARLY;COUNT=7;INTERVAL=2;BYYEARDAY=190',
            '2011-07-09 03:07:00',
            [
                '2011-07-09 03:07:00',
                '2013-07-09 03:07:00',
                '2015-07-09 03:07:00',
                '2017-07-09 03:07:00',
                '2019-07-09 03:07:00',
                '2021-07-09 03:07:00',
                '2023-07-09 03:07:00',
            ]
        );
    }

    /*
     * Regression test for #383
     * $parser->next() used to cause an infinite loop.
     */
    public function testYearlyByYearDayImmutable(): void
    {
        $start = '2011-07-10 03:07:00';
        $rule = 'FREQ=YEARLY;COUNT=7;INTERVAL=2;BYYEARDAY=190';
        $tz = 'UTC';

        $dt = new \DateTimeImmutable($start, new \DateTimeZone($tz));
        $parser = new RRuleIterator($rule, $dt);

        $parser->next();

        $item = $parser->current();
        self::assertEquals('2013-07-09 03:07:00', $item->format('Y-m-d H:i:s'));
    }

    public function testYearlyByYearDayMultiple(): void
    {
        $this->parse(
            'FREQ=YEARLY;COUNT=8;INTERVAL=3;BYYEARDAY=190,301',
            '2011-07-10 14:53:11',
            [
                '2011-07-10 14:53:11',
                '2011-10-28 14:53:11',
                '2014-07-09 14:53:11',
                '2014-10-28 14:53:11',
                '2017-07-09 14:53:11',
                '2017-10-28 14:53:11',
                '2020-07-08 14:53:11',
                '2020-10-27 14:53:11',
            ]
        );
    }

    public function testYearlyByYearDayByDay(): void
    {
        $this->parse(
            'FREQ=YEARLY;COUNT=6;BYYEARDAY=97;BYDAY=SA',
            '2001-04-07 14:53:11',
            [
                '2001-04-07 14:53:11',
                '2007-04-07 14:53:11',
                '2018-04-07 14:53:11',
                '2024-04-06 14:53:11',
                '2029-04-07 14:53:11',
                '2035-04-07 14:53:11',
            ]
        );
    }

    public function testYearlyByYearDayNegative(): void
    {
        $this->parse(
            'FREQ=YEARLY;COUNT=8;BYYEARDAY=-97,-5',
            '2001-09-26 14:53:11',
            [
                '2001-09-26 14:53:11',
                '2001-12-27 14:53:11',
                '2002-09-26 14:53:11',
                '2002-12-27 14:53:11',
                '2003-09-26 14:53:11',
                '2003-12-27 14:53:11',
                '2004-09-26 14:53:11',
                '2004-12-27 14:53:11',
            ]
        );
    }

    /*
     * Verifies that -365 back in the year is usually 1 Jan, but
     * in leap years it is 2 Jan.
     */
    public function testYearlyByYearDayLargeNegative(): void
    {
        $this->parse(
            'FREQ=YEARLY;COUNT=8;BYYEARDAY=-365',
            '2001-01-01 14:53:11',
            [
                '2001-01-01 14:53:11',
                '2002-01-01 14:53:11',
                '2003-01-01 14:53:11',
                '2004-01-02 14:53:11',
                '2005-01-01 14:53:11',
                '2006-01-01 14:53:11',
                '2007-01-01 14:53:11',
                '2008-01-02 14:53:11',
            ]
        );
    }

    /*
     * Verifies that -366 back in the year is 1 Jan in a leap year
     * Interestingly, it goes back to 31 Dec of the previous year
     * when not a leap year. The spec says that -366 is valid, and
     * makes no mention of it being valid only in a leap year, so
     * the behavior seems reasonable.
     */
    public function testYearlyByYearDayMaxNegative(): void
    {
        $this->parse(
            'FREQ=YEARLY;COUNT=8;BYYEARDAY=-366',
            '2001-01-01 14:53:11',
            [
                '2001-01-01 14:53:11',
                '2001-12-31 14:53:11',
                '2002-12-31 14:53:11',
                '2004-01-01 14:53:11',
                '2004-12-31 14:53:11',
                '2005-12-31 14:53:11',
                '2006-12-31 14:53:11',
                '2008-01-01 14:53:11',
            ]
        );
    }

    public function testYearlyByYearDayInvalid390(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->parse(
            'FREQ=YEARLY;COUNT=8;INTERVAL=4;BYYEARDAY=390',
            '2011-04-07 00:00:00',
            [
            ]
        );
    }

    public function testYearlyByYearDayInvalid0(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->parse(
            'FREQ=YEARLY;COUNT=8;INTERVAL=4;BYYEARDAY=0',
            '2011-04-07 00:00:00',
            [
            ]
        );
    }

    public function testYearlyByDayByWeekNo(): void
    {
        $this->parse(
            'FREQ=YEARLY;COUNT=3;BYDAY=MO;BYWEEKNO=13,15,50',
            '2021-01-01 00:00:00',
            [
                '2021-01-01 00:00:00',
                '2021-03-29 00:00:00',
                '2021-04-12 00:00:00',
            ]
        );
    }

    public function testFastForward(): void
    {
        // The idea is that we're fast-forwarding too far in the future, so
        // there will be no results left.
        $this->parse(
            'FREQ=YEARLY;COUNT=8;INTERVAL=5;BYMONTH=4,10;BYDAY=1MO,-1SU',
            '2011-04-04 00:00:00',
            [],
            '2020-05-05 00:00:00'
        );
    }

    /**
     * The bug that was in the
     * system before would fail on the 5th tuesday of the month, if the 5th
     * tuesday did not exist.
     *
     * A pretty slow test. Had to be marked as 'medium' for phpunit to not die
     * after 1 second. Would be good to optimize later.
     *
     * @medium
     */
    public function testFifthTuesdayProblem(): void
    {
        $this->parse(
            'FREQ=MONTHLY;INTERVAL=1;UNTIL=20071030T035959Z;BYDAY=5TU',
            '2007-10-04 14:46:42',
            [
                '2007-10-04 14:46:42',
            ]
        );
    }

    /**
     * This bug came from a Fruux customer. This would result in a never-ending
     * request.
     */
    public function testFastForwardTooFar(): void
    {
        $this->parse(
            'FREQ=WEEKLY;BYDAY=MO;UNTIL=20090704T205959Z;INTERVAL=1',
            '2009-04-20 18:00:00',
            [
                '2009-04-20 18:00:00',
                '2009-04-27 18:00:00',
                '2009-05-04 18:00:00',
                '2009-05-11 18:00:00',
                '2009-05-18 18:00:00',
                '2009-05-25 18:00:00',
                '2009-06-01 18:00:00',
                '2009-06-08 18:00:00',
                '2009-06-15 18:00:00',
                '2009-06-22 18:00:00',
                '2009-06-29 18:00:00',
            ]
        );
    }

    public function testValidByWeekNo(): void
    {
        $this->parse(
            'FREQ=YEARLY;BYWEEKNO=20;BYDAY=TU',
            '2011-02-07 00:00:00',
            [
                '2011-02-07 00:00:00',
                '2011-05-17 00:00:00',
                '2012-05-15 00:00:00',
                '2013-05-14 00:00:00',
                '2014-05-13 00:00:00',
                '2015-05-12 00:00:00',
                '2016-05-17 00:00:00',
                '2017-05-16 00:00:00',
                '2018-05-15 00:00:00',
                '2019-05-14 00:00:00',
                '2020-05-12 00:00:00',
                '2021-05-18 00:00:00',
            ]
        );
    }

    public function testNegativeValidByWeekNo(): void
    {
        $this->parse(
            'FREQ=YEARLY;BYWEEKNO=-20;BYDAY=TU,FR',
            '2011-09-02 00:00:00',
            [
                '2011-09-02 00:00:00',
                '2012-08-07 00:00:00',
                '2012-08-10 00:00:00',
                '2013-08-06 00:00:00',
                '2013-08-09 00:00:00',
                '2014-08-05 00:00:00',
                '2014-08-08 00:00:00',
                '2015-08-11 00:00:00',
                '2015-08-14 00:00:00',
                '2016-08-09 00:00:00',
                '2016-08-12 00:00:00',
                '2017-08-08 00:00:00',
            ]
        );
    }

    public function testTwoValidByWeekNo(): void
    {
        $this->parse(
            'FREQ=YEARLY;BYWEEKNO=20;BYDAY=TU,FR',
            '2011-09-07 09:00:00',
            [
                '2011-09-07 09:00:00',
                '2012-05-15 09:00:00',
                '2012-05-18 09:00:00',
                '2013-05-14 09:00:00',
                '2013-05-17 09:00:00',
                '2014-05-13 09:00:00',
                '2014-05-16 09:00:00',
                '2015-05-12 09:00:00',
                '2015-05-15 09:00:00',
                '2016-05-17 09:00:00',
                '2016-05-20 09:00:00',
                '2017-05-16 09:00:00',
            ]
        );
    }

    public function testValidByWeekNoByDayDefault(): void
    {
        $this->parse(
            'FREQ=YEARLY;BYWEEKNO=20',
            '2011-05-16 00:00:00',
            [
                '2011-05-16 00:00:00',
                '2012-05-14 00:00:00',
                '2013-05-13 00:00:00',
                '2014-05-12 00:00:00',
                '2015-05-11 00:00:00',
                '2016-05-16 00:00:00',
                '2017-05-15 00:00:00',
                '2018-05-14 00:00:00',
                '2019-05-13 00:00:00',
                '2020-05-11 00:00:00',
                '2021-05-17 00:00:00',
                '2022-05-16 00:00:00',
            ]
        );
    }

    public function testMultipleValidByWeekNo(): void
    {
        $this->parse(
            'FREQ=YEARLY;BYWEEKNO=20,50;BYDAY=TU,FR',
            '2011-01-16 00:00:00',
            [
                '2011-01-16 00:00:00',
                '2011-05-17 00:00:00',
                '2011-05-20 00:00:00',
                '2011-12-13 00:00:00',
                '2011-12-16 00:00:00',
                '2012-05-15 00:00:00',
                '2012-05-18 00:00:00',
                '2012-12-11 00:00:00',
                '2012-12-14 00:00:00',
                '2013-05-14 00:00:00',
                '2013-05-17 00:00:00',
                '2013-12-10 00:00:00',
            ]
        );
    }

    public function testInvalidByWeekNo(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->parse(
            'FREQ=YEARLY;BYWEEKNO=54',
            '2011-05-16 00:00:00',
            [
            ]
        );
    }

    /**
     * This also at one point caused an infinite loop. We're keeping the test.
     */
    public function testYearlyByMonthLoop(): void
    {
        $this->parse(
            'FREQ=YEARLY;INTERVAL=1;UNTIL=20120203T225959Z;BYMONTH=2;BYSETPOS=1;BYDAY=SU,MO,TU,WE,TH,FR,SA',
            '2012-01-01 15:45:00',
            [
                '2012-02-01 15:45:00',
            ],
            '2012-01-29 23:00:00'
        );
    }

    /**
     * This test can take some seconds to complete.
     * The "large" annotation means phpunit will let it run for
     * up to 60 seconds by default.
     *
     * @large
     */
    public function testYearlyBySetPosLoop(): void
    {
        $this->parse(
            'FREQ=YEARLY;BYMONTH=5;BYSETPOS=3;BYMONTHDAY=3',
            '2022-03-03 15:45:00',
            [
            ],
            '2022-05-01'
        );
    }

    /**
     * Something, somewhere produced an ics with an interval set to 0. Because
     * this means we increase the current day (or week, month) by 0, this also
     * results in an infinite loop.
     */
    public function testZeroInterval(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->parse(
            'FREQ=YEARLY;INTERVAL=0',
            '2012-08-24 14:57:00',
            [],
            '2013-01-01 23:00:00'
        );
    }

    public function testInvalidFreq(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->parse(
            'FREQ=SMONTHLY;INTERVAL=3;UNTIL=20111025T000000Z',
            '2011-10-07',
            []
        );
    }

    public function testByDayBadOffset(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->parse(
            'FREQ=WEEKLY;INTERVAL=1;COUNT=4;BYDAY=0MO;WKST=SA',
            '2014-08-01 00:00:00',
            []
        );
    }

    public function testUntilBeginHasTimezone(): void
    {
        $this->parse(
            'FREQ=WEEKLY;UNTIL=20131118T183000',
            '2013-09-23 18:30:00',
            [
                '2013-09-23 18:30:00',
                '2013-09-30 18:30:00',
                '2013-10-07 18:30:00',
                '2013-10-14 18:30:00',
                '2013-10-21 18:30:00',
                '2013-10-28 18:30:00',
                '2013-11-04 18:30:00',
                '2013-11-11 18:30:00',
                '2013-11-18 18:30:00',
            ],
            null,
            'America/New_York'
        );
    }

    public function testUntilBeforeDtStart(): void
    {
        $this->parse(
            'FREQ=DAILY;UNTIL=20140101T000000Z',
            '2014-08-02 00:15:00',
            [
                '2014-08-02 00:15:00',
            ]
        );
    }

    public function testIgnoredStuff(): void
    {
        $this->parse(
            'FREQ=DAILY;BYSECOND=1;BYMINUTE=1;BYYEARDAY=1;BYWEEKNO=1;COUNT=2',
            '2014-08-02 00:15:00',
            [
                '2014-08-02 00:15:00',
                '2014-08-03 00:15:00',
            ]
        );
    }

    public function testMinusFifthThursday(): void
    {
        $this->parse(
            'FREQ=MONTHLY;BYDAY=-4TH,-5TH;COUNT=4',
            '2015-01-01 00:15:00',
            [
                '2015-01-01 00:15:00',
                '2015-01-08 00:15:00',
                '2015-02-05 00:15:00',
                '2015-03-05 00:15:00',
            ]
        );
    }

    /**
     * This test can take some seconds to complete.
     * The "large" annotation means phpunit will let it run for
     * up to 60 seconds by default.
     *
     * @large
     */
    public function testNeverEnding(): void
    {
        $this->parse(
            'FREQ=MONTHLY;BYDAY=2TU;BYSETPOS=2',
            '2015-01-01 00:15:00',
            [
                '2015-01-01 00:15:00',
            ],
            null,
            'UTC',
            true
        );
    }

    public function testUnsupportedPart(): void
    {
        $this->expectException(InvalidDataException::class);
        $this->parse(
            'FREQ=DAILY;BYWODAN=1',
            '2014-08-02 00:15:00',
            []
        );
    }

    public function testIteratorFunctions(): void
    {
        $parser = new RRuleIterator('FREQ=DAILY', new \DateTime('2014-08-02 00:00:13'));
        $parser->next();
        self::assertEquals(
            new \DateTime('2014-08-03 00:00:13'),
            $parser->current()
        );
        self::assertEquals(
            1,
            $parser->key()
        );

        $parser->rewind();

        self::assertEquals(
            new \DateTime('2014-08-02 00:00:13'),
            $parser->current()
        );
        self::assertEquals(
            0,
            $parser->key()
        );
    }

    /**
     * @param string|array<string, mixed> $rule
     * @param array<int, string>          $expected
     *
     * @throws InvalidDataException
     */
    public function parse($rule, string $start, array $expected, string $fastForward = null, string $tz = 'UTC', bool $runTillTheEnd = false): void
    {
        $dt = new \DateTime($start, new \DateTimeZone($tz));
        $parser = new RRuleIterator($rule, $dt);

        if ($fastForward) {
            $parser->fastForward(new \DateTime($fastForward));
        }

        $result = [];
        while ($parser->valid()) {
            $item = $parser->current();
            $result[] = $item->format('Y-m-d H:i:s');

            if (!$runTillTheEnd && $parser->isInfinite() && count($result) >= count($expected)) {
                break;
            }
            $parser->next();
        }

        self::assertEquals(
            $expected,
            $result
        );
    }
}
