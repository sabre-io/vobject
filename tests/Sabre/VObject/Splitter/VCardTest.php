<?php

namespace Sabre\VObject;

class VCardSplitterTest extends \PHPUnit_Framework_TestCase {

    function createTempFile($data) {
        $tempfile = tempnam("/tmp", "vobject-test");
        $fp = fopen($tempfile, "w");
        fwrite($fp, $data);
        fclose($fp);
        return $tempfile;
    }

    function removeTempFile($tempFile) {
        unlink($tempFile);
    }

    function testVCardImportValidVCard() {
        $data = <<<EOT
BEGIN:VCARD
UID:foo
END:VCARD
EOT;
        $tempFile = $this->createTempFile($data);
        
        $objects = new Splitter\VCard($tempFile);

        $return = "";
        while($object=$objects->getNext()) {
            $return .= $object->serialize();
        }
        $this->removeTempFile($tempFile);

        Reader::read($return);
    }

    function testVCardImportValidVCardsWithCategories() {
        $data = <<<EOT
BEGIN:VCARD
UID:card-in-foo1-and-foo2
CATEGORIES:foo1\,foo2
END:VCARD
BEGIN:VCARD
UID:card-in-foo1
CATEGORIES:foo1
END:VCARD
BEGIN:VCARD
UID:card-in-foo3
CATEGORIES:foo3
END:VCARD
BEGIN:VCARD
UID:card-in-foo1-and-foo3
CATEGORIES:foo1\,foo3
END:VCARD
EOT;
        $tempFile = $this->createTempFile($data);
        
        $objects = new Splitter\VCard($tempFile);

        $return = "";
        while($object=$objects->getNext()) {
            $return .= $object->serialize();
        }
        $this->removeTempFile($tempFile);

        Reader::read($return);
    }

    function testVCardImportEndOfData() {
        $data = <<<EOT
BEGIN:VCARD
UID:foo
END:VCARD
EOT;
        $tempFile = $this->createTempFile($data);
        
        $objects = new Splitter\VCard($tempFile);
        $object=$objects->getNext();
        
        $this->assertFalse($object=$objects->getNext());

        $this->removeTempFile($tempFile);

    }

    /**
     * @expectedException        Sabre\VObject\ParseException
     */
    function testVCardImportCheckInvalidComponentException() {
        $data = <<<EOT
BEGIN:FOO
END:FOO
EOT;
        $tempFile = $this->createTempFile($data);
        
        $objects = new Splitter\VCard($tempFile);
        while($object=$objects->getNext()) {
            $return .= $object->serialize();
        }
        $this->removeTempFile($tempFile);
        
    }

    function testVCardImportMultipleValidVCards() {
        $data = <<<EOT
BEGIN:VCARD
UID:foo
END:VCARD
BEGIN:VCARD
UID:foo
END:VCARD
EOT;
        $tempFile = $this->createTempFile($data);
        
        $objects = new Splitter\VCard($tempFile);

        $return = "";
        while($object=$objects->getNext()) {
            $return .= $object->serialize();
        }
        $this->removeTempFile($tempFile);

        Reader::read($return);
    }

    function testVCardImportVCardWithoutUID() {
        $data = <<<EOT
BEGIN:VCARD
END:VCARD
EOT;
        $tempFile = $this->createTempFile($data);
        
        $objects = new Splitter\VCard($tempFile);

        $return = "";
        while($object=$objects->getNext()) {
            $return .= $object->serialize();
        }
        $this->removeTempFile($tempFile);

        Reader::read($return);
    }

}
