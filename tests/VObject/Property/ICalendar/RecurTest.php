<?php

namespace Sabre\VObject\Property\ICalendar;

use Sabre\VObject\Component\VCalendar;

class RecurTest extends \PHPUnit_Framework_TestCase {

    function testParts() {

        $vcal = new VCalendar();
        $recur = $vcal->add('RRULE', 'FREQ=Daily');

        $this->assertInstanceOf('Sabre\VObject\Property\ICalendar\Recur', $recur);

        $this->assertEquals(['FREQ' => 'DAILY'], $recur->getParts());
        $recur->setParts(['freq'    => 'MONTHLY']);

        $this->assertEquals(['FREQ' => 'MONTHLY'], $recur->getParts());

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testSetValueBadVal() {

        $vcal = new VCalendar();
        $recur = $vcal->add('RRULE', 'FREQ=Daily');
        $recur->setValue(new \Exception());

    }

    function testSetSubParts() {

        $vcal = new VCalendar();
        $recur = $vcal->add('RRULE', ['FREQ' => 'DAILY', 'BYDAY' => 'mo,tu', 'BYMONTH' => [0, 1]]);

        $this->assertEquals([
            'FREQ'    => 'DAILY',
            'BYDAY'   => ['MO', 'TU'],
            'BYMONTH' => [0, 1],
        ], $recur->getParts());

    }
}
