<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class ElementListTest extends TestCase
{
    public function testIterate(): void
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
            self::assertInstanceOf(Component::class, $subcomponent);

            if (3 === $count) {
                self::assertEquals(2, $key);
            }
        }
        self::assertEquals(3, $count);
    }
}
