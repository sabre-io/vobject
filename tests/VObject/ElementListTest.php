<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class ElementListTest extends TestCase
{
    public function testIterate()
    {
        $cal = new Component\VCalendar();
        $sub = $cal->createComponent('VEVENT');

        $elems = [
            $sub,
            clone $sub,
            clone $sub,
        ];

        $elemList = new ElementList($elems);

        $count = 0;
        foreach ($elemList as $key => $subcomponent) {
            ++$count;
            $this->assertInstanceOf(Component::class, $subcomponent);

            if (3 === $count) {
                $this->assertEquals(2, $key);
            }
        }
        $this->assertEquals(3, $count);
    }
}
