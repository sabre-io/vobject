<?php

namespace Sabre\VObject\Component;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;

class VCardTest extends TestCase
{
    /**
     * @dataProvider validateData
     */
    public function testValidate(string $input, array $expectedWarnings, string $expectedRepairedOutput): void
    {
        $vcard = VObject\Reader::read($input);

        $warnings = $vcard->validate();

        $warnMsg = [];
        foreach ($warnings as $warning) {
            $warnMsg[] = $warning['message'];
        }

        self::assertEquals($expectedWarnings, $warnMsg);

        $vcard->validate(VObject\Component::REPAIR);

        self::assertEquals(
            $expectedRepairedOutput,
            $vcard->serialize()
        );
    }

    public function validateData(): array
    {
        $tests = [];

        // Correct
        $tests[] = [
            "BEGIN:VCARD\r\nVERSION:4.0\r\nFN:John Doe\r\nUID:foo\r\nEND:VCARD\r\n",
            [],
            "BEGIN:VCARD\r\nVERSION:4.0\r\nFN:John Doe\r\nUID:foo\r\nEND:VCARD\r\n",
        ];

        // No VERSION
        $tests[] = [
            "BEGIN:VCARD\r\nFN:John Doe\r\nUID:foo\r\nEND:VCARD\r\n",
            [
                'VERSION MUST appear exactly once in a VCARD component',
            ],
            "BEGIN:VCARD\r\nVERSION:4.0\r\nFN:John Doe\r\nUID:foo\r\nEND:VCARD\r\n",
        ];

        // Unknown version
        $tests[] = [
            "BEGIN:VCARD\r\nVERSION:2.2\r\nFN:John Doe\r\nUID:foo\r\nEND:VCARD\r\n",
            [
                'Only vcard version 4.0 (RFC6350), version 3.0 (RFC2426) or version 2.1 (icm-vcard-2.1) are supported.',
            ],
            "BEGIN:VCARD\r\nVERSION:2.1\r\nFN:John Doe\r\nUID:foo\r\nEND:VCARD\r\n",
        ];

        // No FN
        $tests[] = [
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nEND:VCARD\r\n",
            [
                'The FN property must appear in the VCARD component exactly 1 time',
            ],
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nEND:VCARD\r\n",
        ];
        // No FN, N fallback
        $tests[] = [
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nN:Doe;John;;;;;\r\nEND:VCARD\r\n",
            [
                'The FN property must appear in the VCARD component exactly 1 time',
            ],
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nN:Doe;John;;;;;\r\nFN:John Doe\r\nEND:VCARD\r\n",
        ];
        // No FN, N fallback, no first name
        $tests[] = [
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nN:Doe;;;;;;\r\nEND:VCARD\r\n",
            [
                'The FN property must appear in the VCARD component exactly 1 time',
            ],
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nN:Doe;;;;;;\r\nFN:Doe\r\nEND:VCARD\r\n",
        ];
        // No FN, ORG fallback
        $tests[] = [
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nORG:Acme Co.\r\nEND:VCARD\r\n",
            [
                'The FN property must appear in the VCARD component exactly 1 time',
            ],
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nORG:Acme Co.\r\nFN:Acme Co.\r\nEND:VCARD\r\n",
        ];
        // No FN, NICKNAME fallback
        $tests[] = [
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nNICKNAME:JohnDoe\r\nEND:VCARD\r\n",
            [
                'The FN property must appear in the VCARD component exactly 1 time',
            ],
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nNICKNAME:JohnDoe\r\nFN:JohnDoe\r\nEND:VCARD\r\n",
        ];
        // No FN, EMAIL fallback
        $tests[] = [
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nEMAIL:1@example.org\r\nEND:VCARD\r\n",
            [
                'The FN property must appear in the VCARD component exactly 1 time',
            ],
            "BEGIN:VCARD\r\nVERSION:4.0\r\nUID:foo\r\nEMAIL:1@example.org\r\nFN:1@example.org\r\nEND:VCARD\r\n",
        ];

        return $tests;
    }

    public function testGetDocumentType(): void
    {
        $vcard = new VCard([], false);
        $vcard->VERSION = '2.1';
        self::assertEquals(VCard::VCARD21, $vcard->getDocumentType());

        $vcard = new VCard([], false);
        $vcard->VERSION = '3.0';
        self::assertEquals(VCard::VCARD30, $vcard->getDocumentType());

        $vcard = new VCard([], false);
        $vcard->VERSION = '4.0';
        self::assertEquals(VCard::VCARD40, $vcard->getDocumentType());

        $vcard = new VCard([], false);
        self::assertEquals(VCard::UNKNOWN, $vcard->getDocumentType());
    }

    public function testGetByType(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:3.0
EMAIL;TYPE=home:1@example.org
EMAIL;TYPE=work:2@example.org
END:VCARD
VCF;

        $vcard = VObject\Reader::read($vcard);
        self::assertEquals('1@example.org', $vcard->getByType('EMAIL', 'home')->getValue());
        self::assertEquals('2@example.org', $vcard->getByType('EMAIL', 'work')->getValue());
        self::assertNull($vcard->getByType('EMAIL', 'non-existent'));
        self::assertNull($vcard->getByType('ADR', 'non-existent'));
    }

    public function testGetByTypes(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:3.0
TEL;TYPE=HOME,CELL:112233445566
TEL;TYPE=WORK,cell:665544332211
TEL;TYPE=WORK:7778889994455
TEL;TYPE=EXTERNAL:555555555
END:VCARD
VCF;

        $vcard = VObject\Reader::read($vcard);
        self::assertEquals('112233445566', $vcard->getByTypes('TEL', ['home', 'cell'])->getValue());
        self::assertEquals('665544332211', $vcard->getByTypes('TEL', ['work', 'cell'])->getValue());
        self::assertEquals('7778889994455', $vcard->getByTypes('TEL', ['work'])->getValue());
        self::assertEquals('555555555', $vcard->getByTypes('TEL', ['external'])->getValue());
        self::assertNull($vcard->getByTypes('TEL', ['non-existent']));
        self::assertNull($vcard->getByTypes('EMAIL', ['non-existent']));
    }

    public function testPreferredNoPref(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:3.0
EMAIL:1@example.org
EMAIL:2@example.org
END:VCARD
VCF;

        $vcard = VObject\Reader::read($vcard);
        self::assertEquals('1@example.org', $vcard->preferred('EMAIL')->getValue());
    }

    public function testPreferredWithPref(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:3.0
EMAIL:1@example.org
EMAIL;TYPE=PREF:2@example.org
END:VCARD
VCF;

        $vcard = VObject\Reader::read($vcard);
        self::assertEquals('2@example.org', $vcard->preferred('EMAIL')->getValue());
    }

    public function testPreferredWith40Pref(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:4.0
EMAIL:1@example.org
EMAIL;PREF=3:2@example.org
EMAIL;PREF=2:3@example.org
END:VCARD
VCF;

        $vcard = VObject\Reader::read($vcard);
        self::assertEquals('3@example.org', $vcard->preferred('EMAIL')->getValue());
    }

    public function testPreferredNotFound(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:4.0
END:VCARD
VCF;

        $vcard = VObject\Reader::read($vcard);
        self::assertNull($vcard->preferred('EMAIL'));
    }

    public function testNoUIDCardDAV(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:4.0
FN:John Doe
END:VCARD
VCF;
        self::assertValidate(
            $vcard,
            VCard::PROFILE_CARDDAV,
            3,
            'vCards on CardDAV servers MUST have a UID property.'
        );
    }

    public function testNoUIDNoCardDAV(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:4.0
FN:John Doe
END:VCARD
VCF;
        self::assertValidate(
            $vcard,
            0,
            2,
            'Adding a UID to a vCard property is recommended.'
        );
    }

    public function testNoUIDNoCardDAVRepair(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:4.0
FN:John Doe
END:VCARD
VCF;
        self::assertValidate(
            $vcard,
            VCard::REPAIR,
            1,
            'Adding a UID to a vCard property is recommended.'
        );
    }

    public function testVCard21CardDAV(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:2.1
FN:John Doe
UID:foo
END:VCARD
VCF;
        self::assertValidate(
            $vcard,
            VCard::PROFILE_CARDDAV,
            3,
            'CardDAV servers are not allowed to accept vCard 2.1.'
        );
    }

    public function testVCard21NoCardDAV(): void
    {
        $vcard = <<<VCF
BEGIN:VCARD
VERSION:2.1
FN:John Doe
UID:foo
END:VCARD
VCF;
        self::assertValidate(
            $vcard,
            0,
            0
        );
    }

    public function assertValidate($vcf, $options, int $expectedLevel, ?string $expectedMessage = null): void
    {
        $vcal = VObject\Reader::read($vcf);
        $result = $vcal->validate($options);

        self::assertValidateResult($result, $expectedLevel, $expectedMessage);
    }

    public function assertValidateResult($input, int $expectedLevel, ?string $expectedMessage = null): void
    {
        $messages = [];
        foreach ($input as $warning) {
            $messages[] = $warning['message'];
        }

        if (0 === $expectedLevel) {
            self::assertCount(0, $input, 'No validation messages were expected. We got: '.implode(', ', $messages));
        } else {
            self::assertCount(1, $input, 'We expected exactly 1 validation message, We got: '.implode(', ', $messages));

            self::assertEquals($expectedMessage, $input[0]['message']);
            self::assertEquals($expectedLevel, $input[0]['level']);
        }
    }
}
