<?php

namespace Sabre\VObject;

/**
 * Tests the cli.
 *
 * Warning: these tests are very rudimentary.
 */
class CliTest extends \PHPUnit_Framework_TestCase {

    public function setUp() {

        $this->cli = new CliMock();
        $this->cli->stderr = fopen('php://memory','r+');
        $this->cli->stdout = fopen('php://memory','r+');

    }

    public function testInvalidArg() {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', '--hi'))
        );
        rewind($this->cli->stderr);
        $this->assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

    }

    public function testQuiet() {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', '-q'))
        );
        $this->assertTrue($this->cli->quiet);

        rewind($this->cli->stderr);
        $this->assertEquals(0, strlen(stream_get_contents($this->cli->stderr)));

    }

    public function testHelp() {

        $this->assertEquals(
            0,
            $this->cli->main(array('vobject', '-h'))
        );
        rewind($this->cli->stderr);
        $this->assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

    }

    public function testFormat() {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', '--format=jcard'))
        );

        rewind($this->cli->stderr);
        $this->assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

        $this->assertEquals('jcard', $this->cli->format);

    }

    public function testFormatInvalid() {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', '--format=foo'))
        );

        rewind($this->cli->stderr);
        $this->assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

        $this->assertNull($this->cli->format);

    }

    public function testInputFormatInvalid() {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', '--inputformat=foo'))
        );

        rewind($this->cli->stderr);
        $this->assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

        $this->assertNull($this->cli->format);

    }


    public function testNoInputFile() {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', 'color'))
        );

        rewind($this->cli->stderr);
        $this->assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

    }

    public function testTooManyArgs() {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', 'color', 'a', 'b', 'c'))
        );

    }

    public function testUnknownCommand() {

        $this->assertEquals(
            1,
            $this->cli->main(array('vobject', 'foo', '-'))
        );

    }

    public function testConvertJson() {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<ICS
BEGIN:VCARD
VERSION:3.0
FN:Cowboy Henk
END:VCARD
ICS
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $this->assertEquals(
            0,
            $this->cli->main(array('vobject', 'convert','--format=json', '-'))
        );

        rewind($this->cli->stdout);
        $this->assertEquals(
            '["vcard",[["version",{},"text","4.0"],["prodid",{},"text","-\/\/Sabre\/\/Sabre VObject 3.1.0\/\/EN"],["fn",{},"text","Cowboy Henk"]]]',
            stream_get_contents($this->cli->stdout)
        );

    }

    public function testConvertJCardPretty() {

        if (version_compare(PHP_VERSION, '5.4.0') < 0) {
            $this->markTestSkipped('This test required PHP 5.4.0');
        }

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<ICS
BEGIN:VCARD
VERSION:3.0
FN:Cowboy Henk
END:VCARD
ICS
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $this->assertEquals(
            0,
            $this->cli->main(array('vobject', 'convert','--format=jcard', '--pretty', '-'))
        );

        rewind($this->cli->stdout);
        $expected = <<<JCARD
[
    "vcard",
    [
        [
            "version",
            {

            },
            "text",
            "4.0"
        ],
        [
            "prodid",
            {

            },
            "text",
            "-\/\/Sabre\/\/Sabre VObject 3.1.0\/\/EN"
        ],
        [
            "fn",
            {

            },
            "text",
            "Cowboy Henk"
        ]
    ]
]
JCARD;

          $this->assertEquals(
            $expected,
            stream_get_contents($this->cli->stdout)
        );

    }

    public function testConvertJCalFail() {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<ICS
BEGIN:VCARD
VERSION:3.0
FN:Cowboy Henk
END:VCARD
ICS
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $this->assertEquals(
            2,
            $this->cli->main(array('vobject', 'convert','--format=jcal', '--inputformat=mimedir', '-'))
        );

    }

    public function testConvertMimeDir() {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<JCARD
[
    "vcard",
    [
        [
            "version",
            {

            },
            "text",
            "4.0"
        ],
        [
            "prodid",
            {

            },
            "text",
            "-\/\/Sabre\/\/Sabre VObject 3.1.0\/\/EN"
        ],
        [
            "fn",
            {

            },
            "text",
            "Cowboy Henk"
        ]
    ]
]
JCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $this->assertEquals(
            0,
            $this->cli->main(array('vobject', 'convert','--format=mimedir', '--inputformat=json', '--pretty', '-'))
        );

        rewind($this->cli->stdout);
        $expected = <<<VCF
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
FN:Cowboy Henk
END:VCARD

VCF;

          $this->assertEquals(
            strtr($expected, array("\n"=>"\r\n")),
            stream_get_contents($this->cli->stdout)
        );

    }

    public function testConvertDefaultFormats() {

        $inputStream = fopen('php://memory','r+');

        $this->assertEquals(
            2,
            $this->cli->main(array('vobject', 'convert','foo.json','bar.json'))
        );

        $this->assertEquals('json', $this->cli->inputFormat);
        $this->assertEquals('json', $this->cli->format);

    }

    public function testConvertDefaultFormats2() {

        $this->assertEquals(
            2,
            $this->cli->main(array('vobject', 'convert','foo.ics','bar.ics'))
        );

        $this->assertEquals('mimedir', $this->cli->inputFormat);
        $this->assertEquals('mimedir', $this->cli->format);

    }

    public function testVCard3040() {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCARD
VERSION:3.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
FN:Cowboy Henk
END:VCARD

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $this->assertEquals(
            0,
            $this->cli->main(array('vobject', 'convert','--format=vcard40', '--pretty', '-'))
        );

        rewind($this->cli->stdout);
        $expected = <<<VCF
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
FN:Cowboy Henk
END:VCARD

VCF;

          $this->assertEquals(
            strtr($expected, array("\n"=>"\r\n")),
            stream_get_contents($this->cli->stdout)
        );

    }

    public function testVCard4030() {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
FN:Cowboy Henk
END:VCARD

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $this->assertEquals(
            0,
            $this->cli->main(array('vobject', 'convert','--format=vcard30', '--pretty', '-'))
        );

        rewind($this->cli->stdout);
        $expected = <<<VCF
BEGIN:VCARD
VERSION:3.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
FN:Cowboy Henk
END:VCARD

VCF;

          $this->assertEquals(
            strtr($expected, array("\n"=>"\r\n")),
            stream_get_contents($this->cli->stdout)
        );

    }

    public function testVCard4021() {

        $inputStream = fopen('php://memory','r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
FN:Cowboy Henk
END:VCARD

VCARD
    );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        // vCard 2.1 is not supported yet, so this returns a failure.
        $this->assertEquals(
            2,
            $this->cli->main(array('vobject', 'convert','--format=vcard21', '--pretty', '-'))
        );

    }

}

class CliMock extends Cli {

    public $log = array();

    public $quiet = false;

    public $format;

    public $pretty;

    public $stdin;

    public $stdout;

    public $stderr;

    public $inputFormat;

    public $outputFormat;

}
