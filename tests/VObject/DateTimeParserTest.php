<?php

namespace Sabre\VObject;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class DateTimeParserTest extends TestCase
{
    /**
     * @throws InvalidDataException
     */
    public function testParseICalendarDuration()
    {
        $this->assertEquals('+1 weeks', DateTimeParser::parseDurationAsString('P1W'));
        $this->assertEquals('+5 days', DateTimeParser::parseDurationAsString('P5D'));
        $this->assertEquals('+5 days 3 hours 50 minutes 12 seconds', DateTimeParser::parseDurationAsString('P5DT3H50M12S'));
        $this->assertEquals('-1 weeks 50 minutes', DateTimeParser::parseDurationAsString('-P1WT50M'));
        $this->assertEquals('+50 days 3 hours 2 seconds', DateTimeParser::parseDurationAsString('+P50DT3H2S'));
        $this->assertEquals('+0 seconds', DateTimeParser::parseDurationAsString('+PT0S'));
        $this->assertEquals(new DateInterval('PT0S'), DateTimeParser::parseDuration('PT0S'));
    }

    /**
     * @throws InvalidDataException
     */
    public function testParseICalendarDurationDateInterval()
    {
        $expected = new DateInterval('P7D');
        $this->assertEquals($expected, DateTimeParser::parseDuration('P1W'));
        $this->assertEquals($expected, DateTimeParser::parse('P1W'));

        $expected = new DateInterval('PT3M');
        $expected->invert = true;
        $this->assertEquals($expected, DateTimeParser::parseDuration('-PT3M'));
    }

    public function testParseICalendarDurationFail()
    {
        $this->expectException(InvalidDataException::class);
        DateTimeParser::parseDurationAsString('P1X');
    }

    public function testParseICalendarDateTime()
    {
        $dateTime = DateTimeParser::parseDateTime('20100316T141405');

        $compare = new DateTimeImmutable('2010-03-16 14:14:05', new DateTimeZone('UTC'));

        $this->assertEquals($compare, $dateTime);
    }

    /**
     * @depends testParseICalendarDateTime
     */
    public function testParseICalendarDateTimeBadFormat()
    {
        $this->expectException(InvalidDataException::class);
        $dateTime = DateTimeParser::parseDateTime('20100316T141405 ');
    }

    /**
     * @depends testParseICalendarDateTime
     */
    public function testParseICalendarDateTimeInvalidTime()
    {
        $this->expectException(InvalidDataException::class);
        $dateTime = DateTimeParser::parseDateTime('20100316T251405');
    }

    /**
     * @depends testParseICalendarDateTime
     */
    public function testParseICalendarDateTimeUTC()
    {
        $dateTime = DateTimeParser::parseDateTime('20100316T141405Z');

        $compare = new DateTimeImmutable('2010-03-16 14:14:05', new DateTimeZone('UTC'));
        $this->assertEquals($compare, $dateTime);
    }

    /**
     * @depends testParseICalendarDateTime
     */
    public function testParseICalendarDateTimeUTC2()
    {
        $dateTime = DateTimeParser::parseDateTime('20101211T160000Z');

        $compare = new DateTimeImmutable('2010-12-11 16:00:00', new DateTimeZone('UTC'));
        $this->assertEquals($compare, $dateTime);
    }

    /**
     * @depends testParseICalendarDateTime
     */
    public function testParseICalendarDateTimeCustomTimeZone()
    {
        $dateTime = DateTimeParser::parseDateTime('20100316T141405', new DateTimeZone('Europe/Amsterdam'));

        $compare = new DateTimeImmutable('2010-03-16 14:14:05', new DateTimeZone('Europe/Amsterdam'));
        $this->assertEquals($compare, $dateTime);
    }

    public function testParseICalendarDate()
    {
        $dateTime = DateTimeParser::parseDate('20100316');

        $expected = new DateTimeImmutable('2010-03-16 00:00:00', new DateTimeZone('UTC'));

        $this->assertEquals($expected, $dateTime);

        $dateTime = DateTimeParser::parse('20100316');
        $this->assertEquals($expected, $dateTime);
    }

    /**
     * TCheck if a date with year > 4000 will not throw an exception. iOS seems to use 45001231 in yearly recurring events.
     */
    public function testParseICalendarDateGreaterThan4000()
    {
        $dateTime = DateTimeParser::parseDate('45001231');

        $expected = new DateTimeImmutable('4500-12-31 00:00:00', new DateTimeZone('UTC'));

        $this->assertEquals($expected, $dateTime);

        $dateTime = DateTimeParser::parse('45001231');
        $this->assertEquals($expected, $dateTime);
    }

    /**
     * Check if a datetime with year > 4000 will not throw an exception. iOS seems to use 45001231T235959 in yearly recurring events.
     */
    public function testParseICalendarDateTimeGreaterThan4000()
    {
        $dateTime = DateTimeParser::parseDateTime('45001231T235959');

        $expected = new DateTimeImmutable('4500-12-31 23:59:59', new DateTimeZone('UTC'));

        $this->assertEquals($expected, $dateTime);

        $dateTime = DateTimeParser::parse('45001231T235959');
        $this->assertEquals($expected, $dateTime);
    }

    /**
     * @depends testParseICalendarDate
     */
    public function testParseICalendarDateBadFormat()
    {
        $this->expectException(InvalidDataException::class);
        $dateTime = DateTimeParser::parseDate('20100316T141405');
    }

    /**
     * @depends testParseICalendarDate
     */
    public function testParseICalendarDateInvalidDate()
    {
        $this->expectException(InvalidDataException::class);
        $dateTime = DateTimeParser::parseDate('20101331');
    }

    /**
     * @dataProvider vcardDates
     */
    public function testVCardDate($input, $output)
    {
        $this->assertEquals(
            $output,
            DateTimeParser::parseVCardDateTime($input)
        );
    }

    public function testBadVCardDate()
    {
        $this->expectException(InvalidDataException::class);
        DateTimeParser::parseVCardDateTime('1985---01');
    }

    public function testBadVCardTime()
    {
        $this->expectException(InvalidDataException::class);
        DateTimeParser::parseVCardTime('23:12:166');
    }

    public function vcardDates()
    {
        return [
            [
                '19961022T140000',
                [
                    'year' => 1996,
                    'month' => 10,
                    'date' => 22,
                    'hour' => 14,
                    'minute' => 00,
                    'second' => 00,
                    'timezone' => null,
                ],
            ],
            [
                '--1022T1400',
                [
                    'year' => null,
                    'month' => 10,
                    'date' => 22,
                    'hour' => 14,
                    'minute' => 00,
                    'second' => null,
                    'timezone' => null,
                ],
            ],
            [
                '---22T14',
                [
                    'year' => null,
                    'month' => null,
                    'date' => 22,
                    'hour' => 14,
                    'minute' => null,
                    'second' => null,
                    'timezone' => null,
                ],
            ],
            [
                '19850412',
                [
                    'year' => 1985,
                    'month' => 4,
                    'date' => 12,
                    'hour' => null,
                    'minute' => null,
                    'second' => null,
                    'timezone' => null,
                ],
            ],
            [
                '1985-04',
                [
                    'year' => 1985,
                    'month' => 04,
                    'date' => null,
                    'hour' => null,
                    'minute' => null,
                    'second' => null,
                    'timezone' => null,
                ],
            ],
            [
                '1985',
                [
                    'year' => 1985,
                    'month' => null,
                    'date' => null,
                    'hour' => null,
                    'minute' => null,
                    'second' => null,
                    'timezone' => null,
                ],
            ],
            [
                '--0412',
                [
                    'year' => null,
                    'month' => 4,
                    'date' => 12,
                    'hour' => null,
                    'minute' => null,
                    'second' => null,
                    'timezone' => null,
                ],
            ],
            [
                '---12',
                [
                    'year' => null,
                    'month' => null,
                    'date' => 12,
                    'hour' => null,
                    'minute' => null,
                    'second' => null,
                    'timezone' => null,
                ],
            ],
            [
                'T102200',
                [
                    'year' => null,
                    'month' => null,
                    'date' => null,
                    'hour' => 10,
                    'minute' => 22,
                    'second' => 0,
                    'timezone' => null,
                ],
            ],
            [
                'T1022',
                [
                    'year' => null,
                    'month' => null,
                    'date' => null,
                    'hour' => 10,
                    'minute' => 22,
                    'second' => null,
                    'timezone' => null,
                ],
            ],
            [
                'T10',
                [
                    'year' => null,
                    'month' => null,
                    'date' => null,
                    'hour' => 10,
                    'minute' => null,
                    'second' => null,
                    'timezone' => null,
                ],
            ],
            [
                'T-2200',
                [
                    'year' => null,
                    'month' => null,
                    'date' => null,
                    'hour' => null,
                    'minute' => 22,
                    'second' => 00,
                    'timezone' => null,
                ],
            ],
            [
                'T--00',
                [
                    'year' => null,
                    'month' => null,
                    'date' => null,
                    'hour' => null,
                    'minute' => null,
                    'second' => 00,
                    'timezone' => null,
                ],
            ],
            [
                'T102200Z',
                [
                    'year' => null,
                    'month' => null,
                    'date' => null,
                    'hour' => 10,
                    'minute' => 22,
                    'second' => 00,
                    'timezone' => 'Z',
                ],
            ],
            [
                'T102200-0800',
                [
                    'year' => null,
                    'month' => null,
                    'date' => null,
                    'hour' => 10,
                    'minute' => 22,
                    'second' => 00,
                    'timezone' => '-0800',
                ],
            ],

            // extended format
            [
                '2012-11-29T15:10:53Z',
                [
                    'year' => 2012,
                    'month' => 11,
                    'date' => 29,
                    'hour' => 15,
                    'minute' => 10,
                    'second' => 53,
                    'timezone' => 'Z',
                ],
            ],

            // with milliseconds
            [
                '20121129T151053.123Z',
                [
                    'year' => 2012,
                    'month' => 11,
                    'date' => 29,
                    'hour' => 15,
                    'minute' => 10,
                    'second' => 53,
                    'timezone' => 'Z',
                ],
            ],

            // extended format with milliseconds
            [
                '2012-11-29T15:10:53.123Z',
                [
                    'year' => 2012,
                    'month' => 11,
                    'date' => 29,
                    'hour' => 15,
                    'minute' => 10,
                    'second' => 53,
                    'timezone' => 'Z',
                ],
            ],
        ];
    }

    public function testDateAndOrTimeDateWithYearMonthDay()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '20150128',
            [
                'year' => '2015',
                'month' => '01',
                'date' => '28',
            ]
        );
    }

    public function testDateAndOrTimeDateWithYearMonth()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '2015-01',
            [
                'year' => '2015',
                'month' => '01',
            ]
        );
    }

    public function testDateAndOrTimeDateWithMonth()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '--01',
            [
                'month' => '01',
            ]
        );
    }

    public function testDateAndOrTimeDateWithMonthDay()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '--0128',
            [
                'month' => '01',
                'date' => '28',
            ]
        );
    }

    public function testDateAndOrTimeDateWithDay()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '---28',
            [
                'date' => '28',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithHour()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '13',
            [
                'hour' => '13',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithHourMinute()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '1353',
            [
                'hour' => '13',
                'minute' => '53',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithHourSecond()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '135301',
            [
                'hour' => '13',
                'minute' => '53',
                'second' => '01',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithMinute()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '-53',
            [
                'minute' => '53',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithMinuteSecond()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '-5301',
            [
                'minute' => '53',
                'second' => '01',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithSecond()
    {
        $this->assertTrue(true);

        /*
         * This is unreachable due to a conflict between date and time pattern.
         * This is an error in the specification, not in our implementation.
         */
    }

    public function testDateAndOrTimeTimeWithSecondZ()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '--01Z',
            [
                'second' => '01',
                'timezone' => 'Z',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithSecondTZ()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '--01+1234',
            [
                'second' => '01',
                'timezone' => '+1234',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithYearMonthDayHour()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '20150128T13',
            [
                'year' => '2015',
                'month' => '01',
                'date' => '28',
                'hour' => '13',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithMonthDayHour()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '--0128T13',
            [
                'month' => '01',
                'date' => '28',
                'hour' => '13',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithDayHour()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '---28T13',
            [
                'date' => '28',
                'hour' => '13',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithDayHourMinute()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '---28T1353',
            [
                'date' => '28',
                'hour' => '13',
                'minute' => '53',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithDayHourMinuteSecond()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '---28T135301',
            [
                'date' => '28',
                'hour' => '13',
                'minute' => '53',
                'second' => '01',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithDayHourZ()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '---28T13Z',
            [
                'date' => '28',
                'hour' => '13',
                'timezone' => 'Z',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithDayHourTZ()
    {
        $this->assertDateAndOrTimeEqualsTo(
            '---28T13+1234',
            [
                'date' => '28',
                'hour' => '13',
                'timezone' => '+1234',
            ]
        );
    }

    protected function assertDateAndOrTimeEqualsTo($date, $parts)
    {
        $this->assertSame(
            DateTimeParser::parseVCardDateAndOrTime($date),
            array_merge(
                [
                    'year' => null,
                    'month' => null,
                    'date' => null,
                    'hour' => null,
                    'minute' => null,
                    'second' => null,
                    'timezone' => null,
                ],
                $parts
            )
        );
    }
}
