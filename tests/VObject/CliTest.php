<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

/**
 * Tests the cli.
 *
 * Warning: these tests are very rudimentary.
 */
class CliTest extends TestCase
{
    private CliMock $cli;

    private string $sabreTempDir = __DIR__.'/../temp/';

    public function setUp(): void
    {
        if (!file_exists($this->sabreTempDir)) {
            mkdir($this->sabreTempDir);
        }

        $this->cli = new CliMock();
        $this->cli->stderr = fopen('php://memory', 'r+');
        $this->cli->stdout = fopen('php://memory', 'r+');
    }

    public function testInvalidArg(): void
    {
        self::assertEquals(
            1,
            $this->cli->main(['vobject', '--hi'])
        );
        rewind($this->cli->stderr);
        self::assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);
    }

    public function testQuiet(): void
    {
        self::assertEquals(
            1,
            $this->cli->main(['vobject', '-q'])
        );
        self::assertTrue($this->cli->quiet);

        rewind($this->cli->stderr);
        self::assertEquals(0, strlen(stream_get_contents($this->cli->stderr)));
    }

    public function testHelp(): void
    {
        self::assertEquals(
            0,
            $this->cli->main(['vobject', '-h'])
        );
        rewind($this->cli->stderr);
        self::assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);
    }

    public function testFormat(): void
    {
        self::assertEquals(
            1,
            $this->cli->main(['vobject', '--format=jcard'])
        );

        rewind($this->cli->stderr);
        self::assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

        self::assertEquals('jcard', $this->cli->format);
    }

    public function testFormatInvalid(): void
    {
        self::assertEquals(
            1,
            $this->cli->main(['vobject', '--format=foo'])
        );

        rewind($this->cli->stderr);
        self::assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

        self::assertNull($this->cli->format);
    }

    public function testInputFormatInvalid(): void
    {
        self::assertEquals(
            1,
            $this->cli->main(['vobject', '--inputformat=foo'])
        );

        rewind($this->cli->stderr);
        self::assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);

        self::assertNull($this->cli->format);
    }

    public function testNoInputFile(): void
    {
        self::assertEquals(
            1,
            $this->cli->main(['vobject', 'color'])
        );

        rewind($this->cli->stderr);
        self::assertTrue(strlen(stream_get_contents($this->cli->stderr)) > 100);
    }

    public function testTooManyArgs(): void
    {
        self::assertEquals(
            1,
            $this->cli->main(['vobject', 'color', 'a', 'b', 'c'])
        );
    }

    public function testUnknownCommand(): void
    {
        self::assertEquals(
            1,
            $this->cli->main(['vobject', 'foo', '-'])
        );
    }

    public function testConvertJson(): void
    {
        $inputStream = fopen('php://memory', 'r+');

        fwrite($inputStream, <<<ICS
BEGIN:VCARD
VERSION:3.0
FN:Cowboy Henk
END:VCARD
ICS
        );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        self::assertEquals(
            0,
            $this->cli->main(['vobject', 'convert', '--format=json', '-'])
        );

        rewind($this->cli->stdout);
        $version = Version::VERSION;
        self::assertEquals(
            '["vcard",[["version",{},"text","4.0"],["prodid",{},"text","-\/\/Sabre\/\/Sabre VObject '.$version.'\/\/EN"],["fn",{},"text","Cowboy Henk"]]]',
            stream_get_contents($this->cli->stdout)
        );
    }

    public function testConvertJCardPretty(): void
    {
        if (version_compare(PHP_VERSION, '5.4.0') < 0) {
            $this->markTestSkipped('This test required PHP 5.4.0');
        }

        $inputStream = fopen('php://memory', 'r+');

        fwrite($inputStream, <<<ICS
BEGIN:VCARD
VERSION:3.0
FN:Cowboy Henk
END:VCARD
ICS
        );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        self::assertEquals(
            0,
            $this->cli->main(['vobject', 'convert', '--format=jcard', '--pretty', '-'])
        );

        rewind($this->cli->stdout);

        // PHP 5.5.12 changed the output

        $expected = <<<JCARD
[
    "vcard",
    [
        [
            "versi
JCARD;

        self::assertStringStartsWith(
            $expected,
            stream_get_contents($this->cli->stdout)
        );
    }

    public function testConvertJCalFail(): void
    {
        $inputStream = fopen('php://memory', 'r+');

        fwrite($inputStream, <<<ICS
BEGIN:VCARD
VERSION:3.0
FN:Cowboy Henk
END:VCARD
ICS
        );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        self::assertEquals(
            2,
            $this->cli->main(['vobject', 'convert', '--format=jcal', '--inputformat=mimedir', '-'])
        );
    }

    public function testConvertMimeDir(): void
    {
        $inputStream = fopen('php://memory', 'r+');

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

        self::assertEquals(
            0,
            $this->cli->main(['vobject', 'convert', '--format=mimedir', '--inputformat=json', '--pretty', '-'])
        );

        rewind($this->cli->stdout);
        $expected = <<<VCF
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
FN:Cowboy Henk
END:VCARD

VCF;

        self::assertEquals(
            strtr($expected, ["\n" => "\r\n"]),
            stream_get_contents($this->cli->stdout)
        );
    }

    public function testConvertDefaultFormats(): void
    {
        $outputFile = $this->sabreTempDir.'bar.json';

        self::assertEquals(
            2,
            $this->cli->main(['vobject', 'convert', 'foo.json', $outputFile])
        );

        self::assertEquals('json', $this->cli->inputFormat);
        self::assertEquals('json', $this->cli->format);
    }

    public function testConvertDefaultFormats2(): void
    {
        $outputFile = $this->sabreTempDir.'bar.ics';

        self::assertEquals(
            2,
            $this->cli->main(['vobject', 'convert', 'foo.ics', $outputFile])
        );

        self::assertEquals('mimedir', $this->cli->inputFormat);
        self::assertEquals('mimedir', $this->cli->format);
    }

    public function testVCard3040(): void
    {
        $inputStream = fopen('php://memory', 'r+');

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

        self::assertEquals(
            0,
            $this->cli->main(['vobject', 'convert', '--format=vcard40', '--pretty', '-'])
        );

        rewind($this->cli->stdout);

        $version = Version::VERSION;
        $expected = <<<VCF
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject $version//EN
FN:Cowboy Henk
END:VCARD

VCF;

        self::assertEquals(
            strtr($expected, ["\n" => "\r\n"]),
            stream_get_contents($this->cli->stdout)
        );
    }

    public function testVCard4030(): void
    {
        $inputStream = fopen('php://memory', 'r+');

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

        self::assertEquals(
            0,
            $this->cli->main(['vobject', 'convert', '--format=vcard30', '--pretty', '-'])
        );

        $version = Version::VERSION;

        rewind($this->cli->stdout);
        $expected = <<<VCF
BEGIN:VCARD
VERSION:3.0
PRODID:-//Sabre//Sabre VObject $version//EN
FN:Cowboy Henk
END:VCARD

VCF;

        self::assertEquals(
            strtr($expected, ["\n" => "\r\n"]),
            stream_get_contents($this->cli->stdout)
        );
    }

    public function testVCard4021(): void
    {
        $inputStream = fopen('php://memory', 'r+');

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

        self::assertEquals(
            2,
            $this->cli->main(['vobject', 'convert', '--format=vcard21', '--pretty', '-'])
        );
    }

    public function testValidate(): void
    {
        $inputStream = fopen('php://memory', 'r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
UID:foo
FN:Cowboy Henk
END:VCARD

VCARD
        );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;
        $result = $this->cli->main(['vobject', 'validate', '-']);

        self::assertEquals(
            0,
            $result
        );
    }

    public function testValidateFail(): void
    {
        $inputStream = fopen('php://memory', 'r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCALENDAR
VERSION:2.0
END:VCARD

VCARD
        );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;
        // vCard 2.0 is not supported yet, so this returns a failure.
        self::assertEquals(
            2,
            $this->cli->main(['vobject', 'validate', '-'])
        );
    }

    public function testValidateFail2(): void
    {
        $inputStream = fopen('php://memory', 'r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCALENDAR
VERSION:5.0
END:VCALENDAR

VCARD
        );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        self::assertEquals(
            2,
            $this->cli->main(['vobject', 'validate', '-'])
        );
    }

    public function testRepair(): void
    {
        $inputStream = fopen('php://memory', 'r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCARD
VERSION:5.0
END:VCARD

VCARD
        );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        self::assertEquals(
            2,
            $this->cli->main(['vobject', 'repair', '-'])
        );

        rewind($this->cli->stdout);
        $regularExpression = "/^BEGIN:VCARD\r\nVERSION:2.1\r\nUID:[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}\r\nEND:VCARD\r\n$/";
        $data = stream_get_contents($this->cli->stdout);
        self::assertMatchesRegularExpression($regularExpression, $data);
    }

    public function testRepairNothing(): void
    {
        $inputStream = fopen('php://memory', 'r+');

        fwrite($inputStream, <<<VCARD
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject 3.1.0//EN
BEGIN:VEVENT
UID:foo
DTSTAMP:20140122T233226Z
DTSTART:20140101T120000Z
END:VEVENT
END:VCALENDAR

VCARD
        );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $result = $this->cli->main(['vobject', 'repair', '-']);

        rewind($this->cli->stderr);
        $error = stream_get_contents($this->cli->stderr);

        self::assertEquals(
            0,
            $result,
            "This should have been error free. stderr output:\n".$error
        );
    }

    /**
     * Note: this is a very shallow test, doesn't dig into the actual output,
     * but just makes sure there's no errors thrown.
     *
     * The colorizer is not a critical component, it's mostly a debugging tool.
     */
    public function testColorCalendar(): void
    {
        $inputStream = fopen('php://memory', 'r+');

        $version = Version::VERSION;

        /*
         * This object is not valid, but it's designed to hit every part of the
         * colorizer source.
         */
        fwrite($inputStream, <<<VCARD
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject {$version}//EN
BEGIN:VTIMEZONE
END:VTIMEZONE
BEGIN:VEVENT
ATTENDEE;RSVP=TRUE:mailto:foo@example.org
REQUEST-STATUS:5;foo
ATTACH:blabla
END:VEVENT
END:VCALENDAR

VCARD
        );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $result = $this->cli->main(['vobject', 'color', '-']);

        rewind($this->cli->stderr);
        $error = stream_get_contents($this->cli->stderr);

        self::assertEquals(
            0,
            $result,
            "This should have been error free. stderr output:\n".$error
        );
    }

    /**
     * Note: this is a very shallow test, doesn't dig into the actual output,
     * but just makes sure there's no errors thrown.
     *
     * The colorizer is not a critical component, it's mostly a debugging tool.
     */
    public function testColorVCard(): void
    {
        $inputStream = fopen('php://memory', 'r+');

        $version = Version::VERSION;

        /*
         * This object is not valid, but it's designed to hit every part of the
         * colorizer source.
         */
        fwrite($inputStream, <<<VCARD
BEGIN:VCARD
VERSION:4.0
PRODID:-//Sabre//Sabre VObject {$version}//EN
ADR:1;2;3;4a,4b;5;6
group.TEL:123454768
END:VCARD

VCARD
        );
        rewind($inputStream);
        $this->cli->stdin = $inputStream;

        $result = $this->cli->main(['vobject', 'color', '-']);

        rewind($this->cli->stderr);
        $error = stream_get_contents($this->cli->stderr);

        self::assertEquals(
            0,
            $result,
            "This should have been error free. stderr output:\n".$error
        );
    }
}

class CliMock extends Cli
{
    public bool $quiet = false;

    public ?string $format = null;

    public bool $pretty = false;

    /**
     * stdin.
     *
     * @var resource
     */
    public $stdin;

    /**
     * output stream.
     *
     * @var resource
     */
    public $stdout;

    /**
     * stderr.
     *
     * @var resource
     */
    public $stderr;

    public ?string $inputFormat = null;
}
