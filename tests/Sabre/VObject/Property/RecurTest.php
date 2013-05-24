<?php

namespace Sabre\VObject\Property;

use Sabre\VObject\Component\VCalendar;

class RecurTest extends \PHPUnit_Framework_TestCase {

    function testParts() {

        $vcal = new VCalendar();
        $recur = $vcal->add('RRULE', 'FREQ=Daily');

        $this->assertInstanceOf('Sabre\VObject\Property\Recur', $recur);

        $this->assertEquals(array('FREQ'=>'DAILY'), $recur->getParts());
        $recur->setParts(array('freq'=>'MONTHLY'));

        $this->assertEquals(array('FREQ'=>'MONTHLY'), $recur->getParts());

    }

    /**
     * @expectedException \InvalidArgumentException
     */
    function testSetValueBadVal() {

        $vcal = new VCalendar();
        $recur = $vcal->add('RRULE', 'FREQ=Daily');
        $recur->setValue(new \StdClass());

    }
}
