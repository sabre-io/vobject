<?php

namespace Sabre\VObject\Recur\EventIterator;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Recur\EventIterator;

class Issue566Test extends TestCase
{
    /**
     * @medium
     */
    public function testDaily() {
        $vcal = new VCalendar();
        $ev = $vcal->createComponent('VEVENT');
        $ev->UID = '1';
        $ev->RRULE = 'FREQ=DAILY;INTERVAL=7;BYDAY=MO';
        $dtStart = $vcal->createProperty('DTSTART');
        $dtStart->setDateTime(new DateTimeImmutable('2022-03-15'));
        $ev->add($dtStart);
        $vcal->add($ev);
        $iterator = new EventIterator($vcal, $ev->UID);
        $this->assertTrue(true);
    }
}
