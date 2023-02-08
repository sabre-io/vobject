<?php

namespace Sabre\VObject\Parser;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\ParseException;

/**
 * Note that most MimeDir related tests can actually be found in the ReaderTest
 * class one level up.
 */
class MimeDirTest extends TestCase
{
    public function testParseError(): void
    {
        $this->expectException(ParseException::class);
        $mimeDir = new MimeDir();
        $mimeDir->parse(fopen(__FILE__, 'a+'));
    }

    public function testDecodeLatin1(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:3.0
FN:umlaut u - \xFC
END:VCARD\n
VCF;

        $mimeDir = new MimeDir();
        $mimeDir->setCharset('ISO-8859-1');
        $vcard = $mimeDir->parse($vcard);
        self::assertEquals("umlaut u - \xC3\xBC", $vcard->FN->getValue());
    }

    public function testDecodeInlineLatin1(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:2.1
FN;CHARSET=ISO-8859-1:umlaut u - \xFC
END:VCARD\n
VCF;

        $mimeDir = new MimeDir();
        $vcard = $mimeDir->parse($vcard);
        self::assertEquals("umlaut u - \xC3\xBC", $vcard->FN->getValue());
    }

    public function testIgnoreCharsetVCard30(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:3.0
FN;CHARSET=unknown:foo-bar - \xFC
END:VCARD\n
VCF;

        $mimeDir = new MimeDir();
        $vcard = $mimeDir->parse($vcard);
        self::assertEquals("foo-bar - \xFC", $vcard->FN->getValue());
    }

    public function testDontDecodeLatin1(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:4.0
FN:umlaut u - \xFC
END:VCARD\n
VCF;

        $mimeDir = new MimeDir();
        $vcard = $mimeDir->parse($vcard);
        // This basically tests that we don't touch the input string if
        // the encoding was set to UTF-8. The result is actually invalid
        // and the validator should report this, but it tests effectively
        // that we pass through the string byte-by-byte.
        self::assertEquals("umlaut u - \xFC", $vcard->FN->getValue());
    }

    public function testDecodeUnsupportedCharset(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $mimeDir = new MimeDir();
        $mimeDir->setCharset('foobar');
    }

    public function testDecodeUnsupportedInlineCharset(): void
    {
        $this->expectException(ParseException::class);
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:2.1
FN;CHARSET=foobar:nothing
END:VCARD\n
VCF;

        $mimeDir = new MimeDir();
        $mimeDir->parse($vcard);
    }

    public function testDecodeWindows1252(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:3.0
FN:Euro \x80
END:VCARD\n
VCF;

        $mimeDir = new MimeDir();
        $mimeDir->setCharset('Windows-1252');
        $vcard = $mimeDir->parse($vcard);
        self::assertEquals("Euro \xE2\x82\xAC", $vcard->FN->getValue());
    }

    public function testDecodeWindows1252Inline(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:2.1
FN;CHARSET=Windows-1252:Euro \x80
END:VCARD\n
VCF;

        $mimeDir = new MimeDir();
        $vcard = $mimeDir->parse($vcard);
        self::assertEquals("Euro \xE2\x82\xAC", $vcard->FN->getValue());
    }

    public function testCaseInsensitiveInlineCharset(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:2.1
FN;CHARSET=iSo-8859-1:Euro
N;CHARSET=utf-8:Test2
END:VCARD\n
VCF;

        $mimeDir = new MimeDir();
        $vcard = $mimeDir->parse($vcard);
        // we can do a simple assertion here. As long as we don't get an exception, everything is thing
        self::assertEquals('Euro', $vcard->FN->getValue());
        self::assertEquals('Test2', $vcard->N->getValue());
    }

    public function testParsingTwiceSameContent(): void
    {
        $card = <<<EOF
BEGIN:VCALENDAR
VERSION:2.0
PRODID:PRODID
BEGIN:VEVENT
DTSTAMP;TZID=Europe/Busingen:20220712T172312
UID:UID
DTSTART;VALUE=DATE;VALUE=DATE;VALUE=DATE:20220612
END:VEVENT
END:VCALENDAR
EOF;

        $mimeDir = new MimeDir();
        $vcard = $mimeDir->parse($card);
        // we can do a simple assertion here. As long as we don't get an exception, everything is fine
        self::assertEquals('20220612', $vcard->VEVENT->DTSTART->getValue());
    }

    /**
     * @covers \Sabre\VObject\Parser\MimeDir::readProperty
     *
     * @dataProvider provideBrokenVCalendar
     */
    public function testBrokenMultilineContentDoesNotBreakImportWhenSetToIgnoreBrokenLines(string $vcalendar): void
    {
        $mimeDir = new MimeDir(null, MimeDir::OPTION_IGNORE_INVALID_LINES);
        $vcalendar = $mimeDir->parse($vcalendar);
        self::assertInstanceOf(VCalendar::class, $vcalendar);
    }

    /**
     * @covers \Sabre\VObject\Parser\MimeDir::readProperty
     *
     * @dataProvider provideBrokenVCalendar
     *
     * @param string $vcalendar
     */
    public function testBrokenMultilineContentDoesBreakImport($vcalendar): void
    {
        $mimeDir = new MimeDir();
        $this->expectException(ParseException::class);
        $mimeDir->parse($vcalendar);
    }

    public function provideBrokenVCalendar(): array
    {
        return [[<<<EOF
BEGIN:VCALENDAR
BEGIN:VEVENT
CREATED:20160501T180854Z
UID:15C11082-9FC5-4159-A888-4A4B92D0DB71
DTEND;TZID=America/Los_Angeles:20160504T133000
SUMMARY:Interment
DTSTART;TZID=America/Los_Angeles:20160504T123000
DTSTAMP:20160501T180924Z
X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-MAPKIT-HANDLE=CAES8gEaEglZw
 0Xu6epCQBFdwwyNJ49ewCKOAQoNVW5pdGVkIFN0YXRlcxICVVMaCkNhbGlmb3JuaWEiAkNBK
 gdBbGFtZWRhMgdPYWtsYW5kOgU5NDYxMVIMUGllZG1vbnQgQXZlWgQ1MDAwYhE1MDAwIFBpZ
 WRtb250IEF2ZWoENDIyMHIWTW91bnRhaW4gVmlldyBDZW1ldGVyeaIBCjk0NjExLTQyMjAqE
 TUwMDAgUGllZG1vbnQgQXZlMhE1MDAwIFBpZWRtb250IEF2ZTIST2FrbGFuZCwgQ0EgIDk0N
 jExMg1Vbml0ZWQgU3RhdGVzODlAAA==;X-APPLE-RADIUS=1001.127625592278;X-TITLE
 =Mountain View Cemetery:5000 Piedmont Avenue
OAKLAND, CA 94611
SEQUENCE:0
END:VEVENT
END:VCALENDAR
EOF
        ], [
            <<<EOF
BEGIN:VCALENDAR
BEGIN:VEVENT
CREATED:20160501T180854Z
UID:15C11082-9FC5-4159-A888-4A4B92D0DB71
DTEND;TZID=America/Los_Angeles:20160504T133000
SUMMARY:Interment
DTSTART;TZID=America/Los_Angeles:20160504T123000
DTSTAMP:20160501T180924Z
X-APPLE-STRUCTURED-LOCATION;VALUE=URI;X-APPLE-MAPKIT-HANDLE=CAES8gEaEglZw
 0Xu6epCQBFdwwyNJ49ewCKOAQoNVW5pdGVkIFN0YXRlcxICVVMaCkNhbGlmb3JuaWEiAkNBK
 gdBbGFtZWRhMgdPYWtsYW5kOgU5NDYxMVIMUGllZG1vbnQgQXZlWgQ1MDAwYhE1MDAwIFBpZ
 WRtb250IEF2ZWoENDIyMHIWTW91bnRhaW4gVmlldyBDZW1ldGVyeaIBCjk0NjExLTQyMjAqE
 TUwMDAgUGllZG1vbnQgQXZlMhE1MDAwIFBpZWRtb250IEF2ZTIST2FrbGFuZCwgQ0EgIDk0N
 jExMg1Vbml0ZWQgU3RhdGVzODlAAA==;X-APPLE-RADIUS=1001.127625592278;X-TITLE
 =Mountain View Cemetery:5000 Piedmont Avenue
OAKLAND, CA 94611:
SEQUENCE:0
END:VEVENT
END:VCALENDAR
EOF
        ]];
    }
}
