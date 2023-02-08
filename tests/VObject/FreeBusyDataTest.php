<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class FreeBusyDataTest extends TestCase
{
    public function testGetData(): void
    {
        $fb = new FreeBusyData(100, 200);

        self::assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 200,
                    'type' => 'FREE',
                ],
            ],
            $fb->getData()
        );
    }

    /**
     * @depends testGetData
     */
    public function testAddBeginning(): void
    {
        $fb = new FreeBusyData(100, 200);

        // Overwriting the first half
        $fb->add(100, 150, 'BUSY');

        self::assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 150,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 150,
                    'end' => 200,
                    'type' => 'FREE',
                ],
            ],
            $fb->getData()
        );

        // Overwriting the first half again
        $fb->add(100, 150, 'BUSY-TENTATIVE');

        self::assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 150,
                    'type' => 'BUSY-TENTATIVE',
                ],
                [
                    'start' => 150,
                    'end' => 200,
                    'type' => 'FREE',
                ],
            ],
            $fb->getData()
        );
    }

    /**
     * @depends testAddBeginning
     */
    public function testAddEnd(): void
    {
        $fb = new FreeBusyData(100, 200);

        // Overwriting the first half
        $fb->add(150, 200, 'BUSY');

        self::assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 150,
                    'type' => 'FREE',
                ],
                [
                    'start' => 150,
                    'end' => 200,
                    'type' => 'BUSY',
                ],
            ],
            $fb->getData()
        );
    }

    /**
     * @depends testAddEnd
     */
    public function testAddMiddle(): void
    {
        $fb = new FreeBusyData(100, 200);

        // Overwriting the first half
        $fb->add(150, 160, 'BUSY');

        self::assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 150,
                    'type' => 'FREE',
                ],
                [
                    'start' => 150,
                    'end' => 160,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 160,
                    'end' => 200,
                    'type' => 'FREE',
                ],
            ],
            $fb->getData()
        );
    }

    /**
     * @depends testAddMiddle
     */
    public function testAddMultiple(): void
    {
        $fb = new FreeBusyData(100, 200);

        $fb->add(110, 120, 'BUSY');
        $fb->add(130, 140, 'BUSY');

        self::assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 110,
                    'type' => 'FREE',
                ],
                [
                    'start' => 110,
                    'end' => 120,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 120,
                    'end' => 130,
                    'type' => 'FREE',
                ],
                [
                    'start' => 130,
                    'end' => 140,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 140,
                    'end' => 200,
                    'type' => 'FREE',
                ],
            ],
            $fb->getData()
        );
    }

    /**
     * @depends testAddMultiple
     */
    public function testAddMultipleOverlap(): void
    {
        $fb = new FreeBusyData(100, 200);

        $fb->add(110, 120, 'BUSY');
        $fb->add(130, 140, 'BUSY');

        self::assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 110,
                    'type' => 'FREE',
                ],
                [
                    'start' => 110,
                    'end' => 120,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 120,
                    'end' => 130,
                    'type' => 'FREE',
                ],
                [
                    'start' => 130,
                    'end' => 140,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 140,
                    'end' => 200,
                    'type' => 'FREE',
                ],
            ],
            $fb->getData()
        );

        $fb->add(115, 135, 'BUSY-TENTATIVE');

        self::assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 110,
                    'type' => 'FREE',
                ],
                [
                    'start' => 110,
                    'end' => 115,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 115,
                    'end' => 135,
                    'type' => 'BUSY-TENTATIVE',
                ],
                [
                    'start' => 135,
                    'end' => 140,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 140,
                    'end' => 200,
                    'type' => 'FREE',
                ],
            ],
            $fb->getData()
        );
    }

    /**
     * @depends testAddMultipleOverlap
     */
    public function testAddMultipleOverlapAndMerge(): void
    {
        $fb = new FreeBusyData(100, 200);

        $fb->add(110, 120, 'BUSY');
        $fb->add(130, 140, 'BUSY');

        self::assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 110,
                    'type' => 'FREE',
                ],
                [
                    'start' => 110,
                    'end' => 120,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 120,
                    'end' => 130,
                    'type' => 'FREE',
                ],
                [
                    'start' => 130,
                    'end' => 140,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 140,
                    'end' => 200,
                    'type' => 'FREE',
                ],
            ],
            $fb->getData()
        );

        $fb->add(115, 135, 'BUSY');

        self::assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 110,
                    'type' => 'FREE',
                ],
                [
                    'start' => 110,
                    'end' => 140,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 140,
                    'end' => 200,
                    'type' => 'FREE',
                ],
            ],
            $fb->getData()
        );
    }
}
