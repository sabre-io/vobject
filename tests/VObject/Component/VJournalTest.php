<?php

namespace Sabre\VObject\Component;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Reader;
use Sabre\VObject\TestHelper;

class VJournalTest extends TestCase
{
    /**
     * @param VJournal<int, mixed> $vtodo
     *
     * @dataProvider timeRangeTestData
     */
    public function testInTimeRange(VJournal $vtodo, \DateTime $start, \DateTime $end, bool $outcome): void
    {
        self::assertEquals($outcome, $vtodo->isInTimeRange($start, $end));
    }

    public function testValidate(): void
    {
        $input = <<<HI
BEGIN:VCALENDAR
VERSION:2.0
PRODID:YoYo
BEGIN:VJOURNAL
UID:12345678
DTSTAMP:20140402T174100Z
END:VJOURNAL
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

    public function testValidateBroken(): void
    {
        $input = <<<HI
BEGIN:VCALENDAR
VERSION:2.0
PRODID:YoYo
BEGIN:VJOURNAL
UID:12345678
DTSTAMP:20140402T174100Z
URL:http://example.org/
URL:http://example.com/
END:VJOURNAL
END:VCALENDAR
HI;

        $obj = Reader::read($input);

        $warnings = $obj->validate();
        $messages = [];
        foreach ($warnings as $warning) {
            $messages[] = $warning['message'];
        }

        self::assertEquals(
            ['URL MUST NOT appear more than once in a VJOURNAL component'],
            $messages
        );
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function timeRangeTestData(): array
    {
        $calendar = new VCalendar();

        $tests = [];

        /**
         * @var VJournal<int, mixed> $vjournal
         */
        $vjournal = $calendar->createComponent('VJOURNAL');
        $vjournal->DTSTART = TestHelper::createDtStart($calendar, '20111223T120000Z');
        $tests[] = [$vjournal, new \DateTime('2011-01-01'), new \DateTime('2012-01-01'), true];
        $tests[] = [$vjournal, new \DateTime('2011-01-01'), new \DateTime('2011-11-01'), false];

        /**
         * @var VJournal<int, mixed> $vjournal2
         */
        $vjournal2 = $calendar->createComponent('VJOURNAL');
        $vjournal2->DTSTART = TestHelper::createDtStart($calendar, '20111223');
        $vjournal2->DTSTART['VALUE'] = 'DATE';
        $tests[] = [$vjournal2, new \DateTime('2011-01-01'), new \DateTime('2012-01-01'), true];
        $tests[] = [$vjournal2, new \DateTime('2011-01-01'), new \DateTime('2011-11-01'), false];

        /**
         * @var VJournal<int, mixed> $vjournal3
         */
        $vjournal3 = $calendar->createComponent('VJOURNAL');
        $tests[] = [$vjournal3, new \DateTime('2011-01-01'), new \DateTime('2012-01-01'), false];
        $tests[] = [$vjournal3, new \DateTime('2011-01-01'), new \DateTime('2011-11-01'), false];

        return $tests;
    }
}
