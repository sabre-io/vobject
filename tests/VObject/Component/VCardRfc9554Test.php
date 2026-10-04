<?php

namespace Sabre\VObject\Component;

use PHPUnit\Framework\TestCase;
use Sabre\VObject;

class VCardRfc9554Test extends TestCase
{
    private function read(string $properties): VCard
    {
        return VObject\Reader::read("BEGIN:VCARD\r\nVERSION:4.0\r\nFN:Jane Doe\r\nUID:example\r\n".$properties."\r\nEND:VCARD\r\n");
    }

    public function testPropertyTypesAndRoundTrip(): void
    {
        $card = $this->read(implode("\r\n", [
            'CREATED:20220705T093412Z',
            'GRAMGENDER;LANGUAGE=de:feminine',
            'LANGUAGE:de-AT',
            'PRONOUNS;LANGUAGE=en;PREF=1:they/them',
            'SOCIALPROFILE;SERVICE-TYPE=Mastodon;USERNAME=Jane:https://example.com/@Jane',
            'SOCIALPROFILE;SERVICE-TYPE=SomeSite;VALUE=text:Jane94',
        ]));
        self::assertSame('TIMESTAMP', $card->CREATED->getValueType());
        self::assertSame('TEXT', $card->GRAMGENDER->getValueType());
        self::assertSame('LANGUAGE-TAG', $card->LANGUAGE->getValueType());
        self::assertInstanceOf(VObject\Property\FlatText::class, $card->PRONOUNS);
        $profiles = array_values($card->select('SOCIALPROFILE'));
        self::assertSame('URI', $profiles[0]->getValueType());
        self::assertSame('TEXT', $profiles[1]->getValueType());
        self::assertSame([], $card->validate());
        self::assertSame($card->serialize(), VObject\Reader::read($card->serialize())->serialize());
        $json = VObject\Writer::writeJson($card);
        self::assertSame($json, VObject\Writer::writeJson(VObject\Reader::readJson($json)));
    }

    public function testExtendedComponentsAndParameters(): void
    {
        $card = $this->read(implode("\r\n", [
            'N;PROP-ID=name;SORT-AS=Doe,Jane:Doe;Jane;;;Jr.;Smith;Jr.',
            'ADR;TYPE=billing,delivery;LABEL="123 Main Street^nAny Town";PROP-ID=address:;;123 Main Street;Any Town;CA;91921;U.S.A.;;;;123;Main Street;;;;;;',
            'NOTE;AUTHOR="mailto:jane@example.com";AUTHOR-NAME="Jane: Doe";CREATED=20221122T151823Z:A note',
            'FN;DERIVED=true:Jane Doe',
        ]));
        // FN is already present in the fixture.
        $names = $card->select('FN');
        $card->remove('FN');
        $card->add(end($names));
        self::assertCount(7, $card->N->getParts());
        self::assertCount(18, $card->ADR->getParts());
        self::assertTrue($card->ADR['TYPE']->has('billing'));
        self::assertTrue($card->ADR['TYPE']->has('delivery'));
        self::assertSame("123 Main Street\nAny Town", (string) $card->ADR['LABEL']);
        self::assertSame([], $card->validate());
        $roundTrip = VObject\Reader::read($card->serialize());
        self::assertSame($card->N->getParts(), $roundTrip->N->getParts());
        self::assertSame($card->ADR->getParts(), $roundTrip->ADR->getParts());
        $jsonCard = VObject\Reader::readJson(VObject\Writer::writeJson($card));
        self::assertSame($card->N->getParts(), $jsonCard->N->getParts());
        self::assertSame($card->ADR->getParts(), $jsonCard->ADR->getParts());
    }

    public function testPhoneticAlternatives(): void
    {
        $card = $this->read(implode("\r\n", [
            'N;ALTID=1;LANGUAGE=zh-Hant:孫;中山;文,逸仙;;;;',
            'N;ALTID=1;PHONETIC=jyut;SCRIPT=Latn;LANGUAGE=yue:syun1;zung1saan1;man4,jat6sin1;;;;',
            'N;ALTID=1;PHONETIC=script;SCRIPT=Latn:Sun;Zhongshan;;;;;',
        ]));
        $roundTrip = VObject\Reader::read($card->serialize());
        self::assertCount(3, $roundTrip->select('N'));
        self::assertSame($card->serialize(), $roundTrip->serialize());
        self::assertSame(
            VObject\Writer::writeJson($card),
            VObject\Writer::writeJson(VObject\Reader::readJson(VObject\Writer::writeJson($card)))
        );
    }

    public function testCreatedCardinality(): void
    {
        $card = $this->read("CREATED:20220705T093412Z\r\nCREATED:20220706T093412Z");
        self::assertContains(
            'CREATED MUST NOT appear more than once in a VCARD component',
            array_column($card->validate(), 'message')
        );
    }

    public function testLanguageCardinality(): void
    {
        $card = $this->read("LANGUAGE:en\r\nLANGUAGE:de");
        self::assertContains(
            'LANGUAGE MUST NOT appear more than once in a VCARD component',
            array_column($card->validate(), 'message')
        );
    }

    public function testRepeatableProperties(): void
    {
        $card = $this->read(implode("\r\n", [
            'GRAMGENDER;LANGUAGE=de:feminine',
            'GRAMGENDER;LANGUAGE=en:common',
            'PRONOUNS;LANGUAGE=en;PREF=1:they/them',
            'PRONOUNS;LANGUAGE=en;PREF=2:xe/xir',
            'SOCIALPROFILE:https://example.com/one',
            'SOCIALPROFILE:https://example.com/two',
        ]));
        self::assertSame([], $card->validate());
        foreach (['GRAMGENDER', 'PRONOUNS', 'SOCIALPROFILE'] as $name) {
            self::assertCount(2, $card->select($name));
        }
    }
}
