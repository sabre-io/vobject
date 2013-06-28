<?php

namespace Sabre\VObject\Property\ICalendar;

use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VEvent;

class DurationTest extends \PHPUnit_Framework_TestCase {

    function testGetDateInterval() {

        $vcal = new VCalendar();
        $event = $vcal->add('VEVENT', array('DURATION' => array('PT1H', '-PT30M')));

        $this->assertEquals(
            new \DateInterval('PT30M'),
            $event->{'DURATION'}->getDateInterval()
        );
    }
}
