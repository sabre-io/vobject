<?php

namespace Sabre\VObject;

class VCardConverterTest extends \PHPUnit_Framework_TestCase {

    function testConvert30to40() {

        $version = Version::VERSION;

        $input = <<<IN
BEGIN:VCARD
VERSION:3.0
PRODID:foo
FN;CHARSET=UTF-8:Steve
TEL;TYPE=PREF,HOME:+1 555 666 777
PHOTO;ENCODING=b;TYPE=JPEG,HOME:Zm9v
PHOTO;ENCODING=b;TYPE=GIF:Zm9v
PHOTO;X-PARAM=FOO;ENCODING=b;TYPE=PNG:Zm9v
PHOTO;VALUE=URI:http://example.org/foo.png
X-ABShowAs:COMPANY
END:VCARD

IN;

        $output = <<<OUT
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject {$version}//EN
FN:Steve
TEL;PREF=1;TYPE=HOME:+1 555 666 777
PHOTO;TYPE=HOME:data:image/jpeg;base64,Zm9v
PHOTO:data:image/gif;base64,Zm9v
PHOTO;X-PARAM=FOO:data:image/png;base64,Zm9v
PHOTO:http://example.org/foo.png
KIND:org
END:VCARD

OUT;

        $vcard = \Sabre\VObject\Reader::read($input);
        $vcard = $vcard->convert(\Sabre\VObject\Document::VCARD40);

        $this->assertEquals(
            $output,
            str_replace("\r", "", $vcard->serialize())
        );

    }

    function testConvert40to40() {

        $version = Version::VERSION;

        $input = <<<IN
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject {$version}//EN
FN:Steve
TEL;PREF=1;TYPE=HOME:+1 555 666 777
PHOTO:data:image/jpeg;base64,Zm9v
PHOTO:data:image/gif;base64,Zm9v
PHOTO;X-PARAM=FOO:data:image/png;base64,Zm9v
PHOTO:http://example.org/foo.png
END:VCARD

IN;

        $output = <<<OUT
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject {$version}//EN
FN:Steve
TEL;PREF=1;TYPE=HOME:+1 555 666 777
PHOTO:data:image/jpeg;base64,Zm9v
PHOTO:data:image/gif;base64,Zm9v
PHOTO;X-PARAM=FOO:data:image/png;base64,Zm9v
PHOTO:http://example.org/foo.png
END:VCARD

OUT;

        $vcard = \Sabre\VObject\Reader::read($input);
        $vcard = $vcard->convert(\Sabre\VObject\Document::VCARD40);

        $this->assertEquals(
            $output,
            str_replace("\r", "", $vcard->serialize())
        );

    }

    /**
     * @expectedException InvalidArgumentException
     */
    function testConvert21to40() {

        $version = Version::VERSION;

        $input = <<<IN
BEGIN:VCARD
VERSION:2.1
PRODID:-//Sabre//Sabre VObject {$version}//EN
FN:Steve
END:VCARD

IN;

        $vcard = \Sabre\VObject\Reader::read($input);
        $vcard->convert(\Sabre\VObject\Document::VCARD40);

    }

    function testConvert30to30() {

        $version = Version::VERSION;

        $input = <<<IN
BEGIN:VCARD
VERSION:3.0
PRODID:foo
FN;CHARSET=UTF-8:Steve
TEL;TYPE=PREF,HOME:+1 555 666 777
PHOTO;ENCODING=b;TYPE=JPEG:Zm9v
PHOTO;ENCODING=b;TYPE=GIF:Zm9v
PHOTO;X-PARAM=FOO;ENCODING=b;TYPE=PNG:Zm9v
PHOTO;VALUE=URI:http://example.org/foo.png
END:VCARD

IN;

        $output = <<<OUT
BEGIN:VCARD
VERSION:3.0
PRODID:foo
FN;CHARSET=UTF-8:Steve
TEL;TYPE=PREF,HOME:+1 555 666 777
PHOTO;ENCODING=b;TYPE=JPEG:Zm9v
PHOTO;ENCODING=b;TYPE=GIF:Zm9v
PHOTO;X-PARAM=FOO;ENCODING=b;TYPE=PNG:Zm9v
PHOTO;VALUE=URI:http://example.org/foo.png
END:VCARD

OUT;

        $vcard = \Sabre\VObject\Reader::read($input);
        $vcard = $vcard->convert(\Sabre\VObject\Document::VCARD30);

        $this->assertEquals(
            $output,
            str_replace("\r", "", $vcard->serialize())
        );

    }

    function testConvert40to30() {

        $version = Version::VERSION;

        $input = <<<IN
BEGIN:VCARD
VERSION:4.0
PRODID:foo
FN:Steve
TEL;PREF=1;TYPE=HOME:+1 555 666 777
PHOTO:data:image/jpeg;base64,Zm9v
PHOTO:data:image/gif,foo
PHOTO;X-PARAM=FOO:data:image/png;base64,Zm9v
PHOTO:http://example.org/foo.png
KIND:org
END:VCARD

IN;

        $output = <<<OUT
BEGIN:VCARD
VERSION:3.0
PRODID:-//Sabre//Sabre VObject {$version}//EN
FN:Steve
TEL;TYPE=PREF,HOME:+1 555 666 777
PHOTO;ENCODING=b;TYPE=JPEG:Zm9v
PHOTO;ENCODING=b;TYPE=GIF:Zm9v
PHOTO;ENCODING=b;TYPE=PNG;X-PARAM=FOO:Zm9v
PHOTO;VALUE=URI:http://example.org/foo.png
X-ABSHOWAS:COMPANY
END:VCARD

OUT;

        $vcard = \Sabre\VObject\Reader::read($input);
        $vcard = $vcard->convert(\Sabre\VObject\Document::VCARD30);

        $this->assertEquals(
            $output,
            str_replace("\r", "", $vcard->serialize())
        );

    }

}
