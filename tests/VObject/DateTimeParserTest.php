<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class DateTimeParserTest extends TestCase
{
    /**
     * @throws InvalidDataException
     */
    public function testParseICalendarDuration(): void
    {
        self::assertEquals('+1 weeks', DateTimeParser::parseDurationAsString('P1W'));
        self::assertEquals('+5 days', DateTimeParser::parseDurationAsString('P5D'));
        self::assertEquals('+5 days 3 hours 50 minutes 12 seconds', DateTimeParser::parseDurationAsString('P5DT3H50M12S'));
        self::assertEquals('-1 weeks 50 minutes', DateTimeParser::parseDurationAsString('-P1WT50M'));
        self::assertEquals('+50 days 3 hours 2 seconds', DateTimeParser::parseDurationAsString('+P50DT3H2S'));
        self::assertEquals('+0 seconds', DateTimeParser::parseDurationAsString('+PT0S'));
        self::assertEquals(new \DateInterval('PT0S'), DateTimeParser::parseDuration('PT0S'));
    }

    /**
     * @throws InvalidDataException
     */
    public function testParseICalendarDurationDateInterval(): void
    {
        $expected = new \DateInterval('P7D');
        self::assertEquals($expected, DateTimeParser::parseDuration('P1W'));
        self::assertEquals($expected, DateTimeParser::parse('P1W'));

        $expected = new \DateInterval('PT3M');
        // https://www.php.net/manual/en/class.dateinterval.php#dateinterval.props.invert
        // invert is 1 if the interval represents a negative time period and 0 otherwise.
        $expected->invert = 1;
        self::assertEquals($expected, DateTimeParser::parseDuration('-PT3M'));
    }

    public function testParseDurationZero(): void
    {
        $expected = new \DateInterval('PT0S');
        self::assertEquals($expected, DateTimeParser::parseDuration('P'));
    }

    public function testParseICalendarDurationFail(): void
    {
        $this->expectException(InvalidDataException::class);
        DateTimeParser::parseDurationAsString('P1X');
    }

    public function testParseICalendarDateTime(): void
    {
        $dateTime = DateTimeParser::parseDateTime('20100316T141405');

        $compare = new \DateTimeImmutable('2010-03-16 14:14:05', new \DateTimeZone('UTC'));

        self::assertEquals($compare, $dateTime);
    }

    /**
     * @depends testParseICalendarDateTime
     */
    public function testParseICalendarDateTimeBadFormat(): void
    {
        $this->expectException(InvalidDataException::class);
        DateTimeParser::parseDateTime('20100316T141405 ');
    }

    /**
     * @depends testParseICalendarDateTime
     */
    public function testParseICalendarDateTimeInvalidTime(): void
    {
        $this->expectException(InvalidDataException::class);
        DateTimeParser::parseDateTime('20100316T251405');
    }

    /**
     * @depends testParseICalendarDateTime
     */
    public function testParseICalendarDateTimeUTC(): void
    {
        $dateTime = DateTimeParser::parseDateTime('20100316T141405Z');

        $compare = new \DateTimeImmutable('2010-03-16 14:14:05', new \DateTimeZone('UTC'));
        self::assertEquals($compare, $dateTime);
    }

    /**
     * @depends testParseICalendarDateTime
     */
    public function testParseICalendarDateTimeUTC2(): void
    {
        $dateTime = DateTimeParser::parseDateTime('20101211T160000Z');

        $compare = new \DateTimeImmutable('2010-12-11 16:00:00', new \DateTimeZone('UTC'));
        self::assertEquals($compare, $dateTime);
    }

    /**
     * @depends testParseICalendarDateTime
     */
    public function testParseICalendarDateTimeCustomTimeZone(): void
    {
        $dateTime = DateTimeParser::parseDateTime('20100316T141405', new \DateTimeZone('Europe/Amsterdam'));

        $compare = new \DateTimeImmutable('2010-03-16 14:14:05', new \DateTimeZone('Europe/Amsterdam'));
        self::assertEquals($compare, $dateTime);
    }

    public function testParseICalendarDate(): void
    {
        $dateTime = DateTimeParser::parseDate('20100316');

        $expected = new \DateTimeImmutable('2010-03-16 00:00:00', new \DateTimeZone('UTC'));

        self::assertEquals($expected, $dateTime);

        $dateTime = DateTimeParser::parse('20100316');
        self::assertEquals($expected, $dateTime);
    }

    /**
     * TCheck if a date with year > 4000 will not throw an exception. iOS seems to use 45001231 in yearly recurring events.
     */
    public function testParseICalendarDateGreaterThan4000(): void
    {
        $dateTime = DateTimeParser::parseDate('45001231');

        $expected = new \DateTimeImmutable('4500-12-31 00:00:00', new \DateTimeZone('UTC'));

        self::assertEquals($expected, $dateTime);

        $dateTime = DateTimeParser::parse('45001231');
        self::assertEquals($expected, $dateTime);
    }

    /**
     * Check if a datetime with year > 4000 will not throw an exception. iOS seems to use 45001231T235959 in yearly recurring events.
     */
    public function testParseICalendarDateTimeGreaterThan4000(): void
    {
        $dateTime = DateTimeParser::parseDateTime('45001231T235959');

        $expected = new \DateTimeImmutable('4500-12-31 23:59:59', new \DateTimeZone('UTC'));

        self::assertEquals($expected, $dateTime);

        $dateTime = DateTimeParser::parse('45001231T235959');
        self::assertEquals($expected, $dateTime);
    }

    /**
     * @depends testParseICalendarDate
     */
    public function testParseICalendarDateBadFormat(): void
    {
        $this->expectException(InvalidDataException::class);
        DateTimeParser::parseDate('20100316T141405');
    }

    /**
     * @depends testParseICalendarDate
     */
    public function testParseICalendarDateInvalidDate(): void
    {
        $this->expectException(InvalidDataException::class);
        DateTimeParser::parseDate('20101331');
    }

    /**
     * @param array<int, array<int, array<string, int|string|null>|string>> $output
     *
     * @dataProvider vcardDates
     */
    public function testVCardDate(string $input, array $output): void
    {
        self::assertEquals(
            $output,
            DateTimeParser::parseVCardDateTime($input)
        );
    }

    public function testBadVCardDate(): void
    {
        $this->expectException(InvalidDataException::class);
        DateTimeParser::parseVCardDateTime('1985---01');
    }

    public function testBadVCardTime(): void
    {
        $this->expectException(InvalidDataException::class);
        DateTimeParser::parseVCardTime('23:12:166');
    }

    /**
     * @return array<int, array<int, array<string, int|string|null>|string>>
     */
    public function vcardDates(): array
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

    public function testDateAndOrTimeDateWithYearMonthDay(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '20150128',
            [
                'year' => '2015',
                'month' => '01',
                'date' => '28',
            ]
        );
    }

    public function testDateAndOrTimeDateWithYearMonth(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '2015-01',
            [
                'year' => '2015',
                'month' => '01',
            ]
        );
    }

    public function testDateAndOrTimeDateWithMonth(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '--01',
            [
                'month' => '01',
            ]
        );
    }

    public function testDateAndOrTimeDateWithMonthDay(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '--0128',
            [
                'month' => '01',
                'date' => '28',
            ]
        );
    }

    public function testDateAndOrTimeDateWithDay(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '---28',
            [
                'date' => '28',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithHour(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '13',
            [
                'hour' => '13',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithHourMinute(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '1353',
            [
                'hour' => '13',
                'minute' => '53',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithHourSecond(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '135301',
            [
                'hour' => '13',
                'minute' => '53',
                'second' => '01',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithMinute(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '-53',
            [
                'minute' => '53',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithMinuteSecond(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '-5301',
            [
                'minute' => '53',
                'second' => '01',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithSecond(): void
    {
        self::assertTrue(true);

        /*
         * This is unreachable due to a conflict between date and time pattern.
         * This is an error in the specification, not in our implementation.
         */
    }

    public function testDateAndOrTimeTimeWithSecondZ(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '--01Z',
            [
                'second' => '01',
                'timezone' => 'Z',
            ]
        );
    }

    public function testDateAndOrTimeTimeWithSecondTZ(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '--01+1234',
            [
                'second' => '01',
                'timezone' => '+1234',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithYearMonthDayHour(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '20150128T13',
            [
                'year' => '2015',
                'month' => '01',
                'date' => '28',
                'hour' => '13',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithMonthDayHour(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '--0128T13',
            [
                'month' => '01',
                'date' => '28',
                'hour' => '13',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithDayHour(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '---28T13',
            [
                'date' => '28',
                'hour' => '13',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithDayHourMinute(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '---28T1353',
            [
                'date' => '28',
                'hour' => '13',
                'minute' => '53',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithDayHourMinuteSecond(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '---28T135301',
            [
                'date' => '28',
                'hour' => '13',
                'minute' => '53',
                'second' => '01',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithDayHourZ(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '---28T13Z',
            [
                'date' => '28',
                'hour' => '13',
                'timezone' => 'Z',
            ]
        );
    }

    public function testDateAndOrTimeDateTimeWithDayHourTZ(): void
    {
        self::assertDateAndOrTimeEqualsTo(
            '---28T13+1234',
            [
                'date' => '28',
                'hour' => '13',
                'timezone' => '+1234',
            ]
        );
    }

    /**
     * @param array<string, string> $parts
     *
     * @throws InvalidDataException
     */
    protected function assertDateAndOrTimeEqualsTo(string $date, array $parts): void
    {
        self::assertSame(
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
