<?php

namespace Sabre\VObject;

class TestCase extends \PHPUnit_Framework_TestCase {

    /**
     * This method tests wether two vcards or icalendar objects are
     * semantically identical.
     *
     * It supports objects being supplied as strings, streams or
     * Sabre\VObject\Component instances.
     *
     * PRODID is removed from both objects as this is often changes and would
     * just get in the way.
     *
     * CALSCALE will automatically get removed if it's set to GREGORIAN.
     *
     * @param resource|string|Component $expected
     * @param resource|string|Component $actual
     * @param string $message
     */
    function assertVObjEquals($expected, $actual, $message = '') {

        $self = $this;
        $getObj = function($input) use ($self) {

            if (is_resource($input)) {
                $input = stream_get_contents($input);
            }
            if (is_string($input)) {
                $input = Reader::read($input);
            }
            if (!$input instanceof Component) {
                $this->fail('Input must be a string, stream or VObject component');
            }
            unset($input->PRODID);
            if ($input instanceof Component\VCalendar && (string)$input->CALSCALE === 'GREGORIAN') {
                unset($input->CALSCALE);
            }
            return $input;

        };

        $expected = $getObj($expected);
        $actual = $getObj($actual);

        $this->assertEquals(
            $expected->serialize(),
            $actual->serialize(),
            $message
        );

    }

}
