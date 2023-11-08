<?php

namespace Sabre\VObject\Component;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Reader;
use Sabre\VObject\TestHelper;

class VTodoTest extends TestCase
{
    /**
     * @param VTodo<int, mixed> $vtodo
     *
     * @dataProvider timeRangeTestData
     */
    public function testInTimeRange(VTodo $vtodo, \DateTime $start, \DateTime $end, bool $outcome): void
    {
        self::assertEquals($outcome, $vtodo->isInTimeRange($start, $end));
    }

    /**
     * @return array<int, array<int, mixed>>
     */
    public function timeRangeTestData(): array
    {
        $tests = [];

        $calendar = new VCalendar();

        /**
         * @var VTodo<int, mixed> $vtodo
         */
        $vtodo = $calendar->createComponent('VTODO');
        $vtodo->DTSTART = TestHelper::createDtStart($calendar, '20111223T120000Z');
        $tests[] = [$vtodo, new \DateTime('2011-01-01'), new \DateTime('2012-01-01'), true];
        $tests[] = [$vtodo, new \DateTime('2011-01-01'), new \DateTime('2011-11-01'), false];

        $vtodo2 = clone $vtodo;
        $vtodo2->DURATION = TestHelper::createDuration($calendar, 'P1D');
        $tests[] = [$vtodo2, new \DateTime('2011-01-01'), new \DateTime('2012-01-01'), true];
        $tests[] = [$vtodo2, new \DateTime('2011-01-01'), new \DateTime('2011-11-01'), false];

        $vtodo3 = clone $vtodo;
        $vtodo3->DUE = TestHelper::createDateCreated($calendar, '20111225');
        $tests[] = [$vtodo3, new \DateTime('2011-01-01'), new \DateTime('2012-01-01'), true];
        $tests[] = [$vtodo3, new \DateTime('2011-01-01'), new \DateTime('2011-11-01'), false];

        /**
         * @var VTodo<int, mixed> $vtodo4
         */
        $vtodo4 = $calendar->createComponent('VTODO');
        $vtodo4->DUE = TestHelper::createDateCreated($calendar, '20111225');
        $tests[] = [$vtodo4, new \DateTime('2011-01-01'), new \DateTime('2012-01-01'), true];
        $tests[] = [$vtodo4, new \DateTime('2011-01-01'), new \DateTime('2011-11-01'), false];

        /**
         * @var VTodo<int, mixed> $vtodo5
         */
        $vtodo5 = $calendar->createComponent('VTODO');
        $vtodo5->COMPLETED = TestHelper::createDateCompleted($calendar, '20111225');
        $tests[] = [$vtodo5, new \DateTime('2011-01-01'), new \DateTime('2012-01-01'), true];
        $tests[] = [$vtodo5, new \DateTime('2011-01-01'), new \DateTime('2011-11-01'), false];

        /**
         * @var VTodo<int, mixed> $vtodo6
         */
        $vtodo6 = $calendar->createComponent('VTODO');
        $vtodo6->CREATED = TestHelper::createDateCreated($calendar, '20111225');
        $tests[] = [$vtodo6, new \DateTime('2011-01-01'), new \DateTime('2012-01-01'), true];
        $tests[] = [$vtodo6, new \DateTime('2011-01-01'), new \DateTime('2011-11-01'), false];

        /**
         * @var VTodo<int, mixed> $vtodo7
         */
        $vtodo7 = $calendar->createComponent('VTODO');
        $vtodo7->CREATED = TestHelper::createDateCreated($calendar, '20111225');
        $vtodo7->COMPLETED = TestHelper::createDateCompleted($calendar, '20111226');
        $tests[] = [$vtodo7, new \DateTime('2011-01-01'), new \DateTime('2012-01-01'), true];
        $tests[] = [$vtodo7, new \DateTime('2011-01-01'), new \DateTime('2011-11-01'), false];

        $vtodo7 = $calendar->createComponent('VTODO');
        $tests[] = [$vtodo7, new \DateTime('2011-01-01'), new \DateTime('2012-01-01'), true];
        $tests[] = [$vtodo7, new \DateTime('2011-01-01'), new \DateTime('2011-11-01'), true];

        return $tests;
    }

    public function testValidate(): void
    {
        $input = <<<HI
BEGIN:VCALENDAR
VERSION:2.0
PRODID:YoYo
BEGIN:VTODO
UID:1234-21355-123156
DTSTAMP:20140402T183400Z
END:VTODO
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

    public function testValidateInvalid(): void
    {
        $input = <<<HI
BEGIN:VCALENDAR
VERSION:2.0
PRODID:YoYo
BEGIN:VTODO
END:VTODO
END:VCALENDAR
HI;

        $obj = Reader::read($input);

        $warnings = $obj->validate();
        $messages = [];
        foreach ($warnings as $warning) {
            $messages[] = $warning['message'];
        }

        self::assertEquals([
            'UID MUST appear exactly once in a VTODO component',
            'DTSTAMP MUST appear exactly once in a VTODO component',
        ], $messages);
    }

    public function testValidateDueDateTimeStartMisMatch(): void
    {
        $input = <<<HI
BEGIN:VCALENDAR
VERSION:2.0
PRODID:YoYo
BEGIN:VTODO
UID:FOO
DTSTART;VALUE=DATE-TIME:20140520T131600Z
DUE;VALUE=DATE:20140520
DTSTAMP;VALUE=DATE-TIME:20140520T131600Z
END:VTODO
END:VCALENDAR
HI;

        $obj = Reader::read($input);

        $warnings = $obj->validate();
        $messages = [];
        foreach ($warnings as $warning) {
            $messages[] = $warning['message'];
        }

        self::assertEquals([
            'The value type (DATE or DATE-TIME) must be identical for DUE and DTSTART',
        ], $messages);
    }

    public function testValidateDueBeforeDateTimeStart(): void
    {
        $input = <<<HI
BEGIN:VCALENDAR
VERSION:2.0
PRODID:YoYo
BEGIN:VTODO
UID:FOO
DTSTART;VALUE=DATE:20140520
DUE;VALUE=DATE:20140518
DTSTAMP;VALUE=DATE-TIME:20140520T131600Z
END:VTODO
END:VCALENDAR
HI;

        $obj = Reader::read($input);

        $warnings = $obj->validate();
        $messages = [];
        foreach ($warnings as $warning) {
            $messages[] = $warning['message'];
        }

        self::assertEquals([
            'DUE must occur after DTSTART',
        ], $messages);
    }
}
