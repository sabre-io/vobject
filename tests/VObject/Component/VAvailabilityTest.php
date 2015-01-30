<?php

namespace Sabre\VObject\Component;

use Sabre\VObject;
use Sabre\VObject\Reader;
use Sabre\VObject\Component\VAvailability;

class VAvailabilityTest extends \PHPUnit_Framework_TestCase {

    function testVAvailabilityComponent() {

        $vcal = <<<VCAL
BEGIN:VCALENDAR
BEGIN:VAVAILABILITY
END:VAVAILABILITY
END:VCALENDAR
VCAL;
        $document = Reader::read($vcal);

        $this->assertInstanceOf(VAvailability::class, $document->VAVAILABILITY);

    }

}
