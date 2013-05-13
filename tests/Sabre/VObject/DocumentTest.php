<?php

namespace Sabre\VObject;

class DocumentTest extends \PHPUnit_Framework_TestCase {

    function testGetDocumentType() {

        $doc = new MockDocument();
        $this->assertEquals(Document::UNKNOWN, $doc->getDocumentType());

    }

    function testConstruct() {

        $doc = new MockDocument('VLIST');
        $this->assertEquals('VLIST', $doc->name);

    }
}


class MockDocument extends Document {

}
