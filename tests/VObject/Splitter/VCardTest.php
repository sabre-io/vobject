<?php

namespace Sabre\VObject\Splitter;

use PHPUnit\Framework\TestCase;
use Sabre\VObject\ParseException;

class VCardTest extends TestCase
{
    public function createStream($data)
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, $data);
        rewind($stream);

        return $stream;
    }

    public function testVCardImportValidVCard(): void
    {
        $data = <<<EOT
BEGIN:VCARD
UID:foo
END:VCARD
EOT;
        $tempFile = $this->createStream($data);

        $objects = new VCard($tempFile);

        $count = 0;
        while ($objects->getNext()) {
            ++$count;
        }
        $this->assertEquals(1, $count);
    }

    public function testVCardImportWrongType(): void
    {
        $this->expectException(ParseException::class);
        $event[] = <<<EOT
BEGIN:VEVENT
UID:foo1
DTSTAMP:20140122T233226Z
DTSTART:20140101T050000Z
END:VEVENT
EOT;

        $event[] = <<<EOT
BEGIN:VEVENT
UID:foo2
DTSTAMP:20140122T233226Z
DTSTART:20140101T060000Z
END:VEVENT
EOT;

        $data = <<<EOT
BEGIN:VCALENDAR
$event[0]
$event[1]
END:VCALENDAR

EOT;
        $tempFile = $this->createStream($data);

        $splitter = new VCard($tempFile);

        while ($splitter->getNext()) {
        }
    }

    public function testVCardImportValidVCardsWithCategories(): void
    {
        $data = <<<EOT
BEGIN:VCARD
UID:card-in-foo1-and-foo2
CATEGORIES:foo1,foo2
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
        $tempFile = $this->createStream($data);

        $splitter = new VCard($tempFile);

        $count = 0;
        while ($splitter->getNext()) {
            ++$count;
        }
        $this->assertEquals(4, $count);
    }

    public function testVCardImportVCardNoComponent(): void
    {
        $this->expectException(ParseException::class);
        $data = <<<EOT
BEGIN:VCARD
FN:first card

BEGIN:VCARD
FN:ok
END:VCARD
EOT;
        $tempFile = $this->createStream($data);

        $splitter = new VCard($tempFile);

        $this->expectException(ParseException::class);
        $this->expectExceptionMessage('Invalid MimeDir file. Unexpected component: "BEGIN:VCARD" in document type VCARD');
        while ($splitter->getNext()) {
        }
    }

    public function testVCardImportQuotedPrintableOptionForgivingLeading(): void
    {
        $data = <<<EOT
BEGIN:VCARD
FN;card
TITLE;CHARSET=UTF-8;ENCODING=QUOTED-PRINTABLE:=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=D0=

END:VCARD
BEGIN:VCARD
FN;card
END:VCARD
EOT;
        $tempFile = $this->createStream($data);

        $splitter = new VCard($tempFile, \Sabre\VObject\Parser\Parser::OPTION_FORGIVING);

        $count = 0;
        while ($splitter->getNext()) {
            ++$count;
        }
        $this->assertEquals(2, $count);
    }

    public function testVCardImportEndOfData(): void
    {
        $data = <<<EOT
BEGIN:VCARD
UID:foo
END:VCARD
EOT;
        $tempFile = $this->createStream($data);

        $objects = new VCard($tempFile);
        $objects->getNext();

        $this->assertNull($objects->getNext());
    }

    public function testVCardImportCheckInvalidArgumentException(): void
    {
        $this->expectException(ParseException::class);
        $data = <<<EOT
BEGIN:FOO
END:FOO
EOT;
        $tempFile = $this->createStream($data);

        $objects = new VCard($tempFile);
        while ($objects->getNext()) {
        }
    }

    public function testVCardImportMultipleValidVCards(): void
    {
        $data = <<<EOT
BEGIN:VCARD
UID:foo
END:VCARD
BEGIN:VCARD
UID:foo
END:VCARD
EOT;
        $tempFile = $this->createStream($data);

        $objects = new VCard($tempFile);

        $count = 0;
        while ($objects->getNext()) {
            ++$count;
        }
        $this->assertEquals(2, $count);
    }

    public function testImportMultipleSeparatedWithNewLines(): void
    {
        $data = <<<EOT
BEGIN:VCARD
UID:foo
END:VCARD


BEGIN:VCARD
UID:foo
END:VCARD


EOT;
        $tempFile = $this->createStream($data);
        $objects = new VCard($tempFile);

        $count = 0;
        while ($objects->getNext()) {
            ++$count;
        }
        $this->assertEquals(2, $count);
    }

    public function testVCardImportVCardWithoutUID(): void
    {
        $data = <<<EOT
BEGIN:VCARD
END:VCARD
EOT;
        $tempFile = $this->createStream($data);

        $objects = new VCard($tempFile);

        $count = 0;
        while ($objects->getNext()) {
            ++$count;
        }

        $this->assertEquals(1, $count);
    }
}
