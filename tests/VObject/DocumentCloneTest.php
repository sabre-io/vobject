<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class DocumentCloneTest extends TestCase
{
    private const ICS = <<<ICS
BEGIN:VCALENDAR
VERSION:2.0
PRODID:-//Sabre//Sabre VObject//EN
BEGIN:VTIMEZONE
TZID:Custom Eastern
X-LIC-LOCATION:America/New_York
BEGIN:STANDARD
TZOFFSETFROM:-0400
TZOFFSETTO:-0400
DTSTART:19700101T000000
END:STANDARD
END:VTIMEZONE
BEGIN:VEVENT
UID:clone-test
DTSTAMP:20260101T000000Z
DTSTART;TZID=Custom Eastern:20260322T090000
SUMMARY:Test
ATTENDEE;CN=Attendee;PARTSTAT=NEEDS-ACTION:mailto:attendee@example.org
BEGIN:VALARM
ACTION:DISPLAY
TRIGGER;RELATED=START:-PT15M
DESCRIPTION:Reminder
END:VALARM
END:VEVENT
END:VCALENDAR
ICS;

    public function testClonedDocumentIsTheRootOfAllItsNodes(): void
    {
        $doc = Reader::read(self::ICS);

        $copy = clone $doc;

        $nodes = $this->collectNodes($copy);
        self::assertContains($copy->VEVENT->VALARM, $nodes);
        self::assertContains($copy->VEVENT->VALARM->TRIGGER['RELATED'], $nodes);
        foreach ($nodes as $node) {
            self::assertSame($copy, $this->getRoot($node), $node->name.' should belong to the copy');
        }
    }

    public function testClonedDocumentDoesNotChangeTheOriginal(): void
    {
        $doc = Reader::read(self::ICS);

        clone $doc;

        foreach ($this->collectNodes($doc) as $node) {
            self::assertSame($doc, $this->getRoot($node), $node->name.' should belong to the original');
        }
    }

    public function testClonedDocumentResolvesCustomTimezoneAfterOriginalIsDestroyed(): void
    {
        $doc = Reader::read(self::ICS);

        $copy = clone $doc;
        $doc->destroy();

        self::assertEquals(
            new \DateTimeImmutable('2026-03-22T13:00:00Z'),
            $copy->VEVENT->DTSTART->getDateTime()
        );
    }

    public function testClonedComponentKeepsTheOriginalRoot(): void
    {
        $doc = Reader::read(self::ICS);

        $event = clone $doc->VEVENT;

        foreach ($this->collectNodes($event) as $node) {
            self::assertSame($doc, $this->getRoot($node), $node->name.' should belong to the original document');
        }
    }

    public function testClonedPropertyKeepsTheOriginalRoot(): void
    {
        $doc = Reader::read(self::ICS);

        $dtStart = clone $doc->VEVENT->DTSTART;

        self::assertSame($doc, $this->getRoot($dtStart));
        self::assertSame($doc, $this->getRoot($dtStart['TZID']));
        self::assertSame($dtStart, $dtStart['TZID']->parent);
    }

    /**
     * @return Node[]
     */
    private function collectNodes(Node $node): array
    {
        $nodes = [$node];
        if ($node instanceof Component) {
            foreach ($node->children() as $child) {
                array_push($nodes, ...$this->collectNodes($child));
            }
        } elseif ($node instanceof Property) {
            array_push($nodes, ...array_values($node->parameters()));
        }

        return $nodes;
    }

    private function getRoot(Node $node): ?Component
    {
        return (new \ReflectionProperty(Node::class, 'root'))->getValue($node);
    }
}
