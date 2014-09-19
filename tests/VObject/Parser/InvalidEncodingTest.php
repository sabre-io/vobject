<?php

namespace Sabre\VObject\Parser;

use
    Sabre\VObject\Reader;

class InvalidEncodingTest extends \PHPUnit_Framework_TestCase {

    function testLibicalSpecificValues() {

        $data = "BEGIN:VCARD\nLABEL:a'b\"c\\\\d\\;e\\,f:g^h&i<j>k!l\\\"M\\tN\\rO\\bP\\fQ\nEND:VCARD";

        /* without compatibility kludges */
        $result = Reader::read($data);
        $this->assertEquals("a'b\"c\\d;e,f:g^h&i<j>k!l\\\"M\\tN\\rO\\bP\\fQ", "".$result->LABEL);

        /* forgiving should not change this */
        $result = Reader::read($data, Reader::OPTION_FORGIVING);
        $this->assertEquals("a'b\"c\\d;e,f:g^h&i<j>k!l\\\"M\\tN\\rO\\bP\\fQ", "".$result->LABEL);

        /* with compatibility kludges */
        $result = Reader::read($data, Reader::OPTION_LIBICAL_COMPATIBLE);
        $this->assertEquals("a'b\"c\\d;e,f:g^h&i<j>k!l\"M\tN\rO\010P\fQ", "".$result->LABEL);

    }
}
