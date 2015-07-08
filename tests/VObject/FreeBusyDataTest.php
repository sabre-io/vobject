<?php

namespace Sabre\VObject;

use DateTimeImmutable as DT;

class FreeBusyDataTest extends \PHPUnit_Framework_TestCase {

    function testGetData() {

        $fb = new FreeBusyData(100, 200);

        $this->assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 200,
                    'type' => 'FREE',
                ]
            ],
            $fb->getData()
        );

    }

    /**
     * @depends testGetData
     */
    function testAddBeginning() {

        $fb = new FreeBusyData(100, 200);

        // Overwriting the first half
        $fb->add(100,150,'BUSY');


        $this->assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 150,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 151,
                    'end' => 200,
                    'type' => 'FREE',
                ]
            ],
            $fb->getData()
        );

        // Overwriting the first half again
        $fb->add(100,150,'BUSY-TENTATIVE');

        $this->assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 150,
                    'type' => 'BUSY-TENTATIVE',
                ],
                [
                    'start' => 151,
                    'end' => 200,
                    'type' => 'FREE',
                ]
            ],
            $fb->getData()
        );

    }

    /**
     * @depends testAddBeginning
     */
    function testAddEnd() {

        $fb = new FreeBusyData(100, 200);

        // Overwriting the first half
        $fb->add(151,200,'BUSY');


        $this->assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 150,
                    'type' => 'FREE',
                ],
                [
                    'start' => 151,
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
    function testAddMiddle() {

        $fb = new FreeBusyData(100, 200);

        // Overwriting the first half
        $fb->add(150,160,'BUSY');


        $this->assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 149,
                    'type' => 'FREE',
                ],
                [
                    'start' => 150,
                    'end' => 160,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 161,
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
    function testAddMultiple() {

        $fb = new FreeBusyData(100, 200);

        $fb->add(110, 120, 'BUSY');
        $fb->add(130, 140, 'BUSY');

        $this->assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 109,
                    'type' => 'FREE',
                ],
                [
                    'start' => 110,
                    'end' => 120,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 121,
                    'end' => 129,
                    'type' => 'FREE',
                ],
                [
                    'start' => 130,
                    'end' => 140,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 141,
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
    function testAddMultipleOverlap() {

        $fb = new FreeBusyData(100, 200);

        $fb->add(110, 120, 'BUSY');
        $fb->add(130, 140, 'BUSY');

        $this->assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 109,
                    'type' => 'FREE',
                ],
                [
                    'start' => 110,
                    'end' => 120,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 121,
                    'end' => 129,
                    'type' => 'FREE',
                ],
                [
                    'start' => 130,
                    'end' => 140,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 141,
                    'end' => 200,
                    'type' => 'FREE',
                ],
            ],
            $fb->getData()
        );

        $fb->add(115, 135, 'BUSY-TENTATIVE');

        $this->assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 109,
                    'type' => 'FREE',
                ],
                [
                    'start' => 110,
                    'end' => 114,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 115,
                    'end' => 135,
                    'type' => 'BUSY-TENTATIVE',
                ],
                [
                    'start' => 136,
                    'end' => 140,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 141,
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
    function testAddMultipleOverlapAndMerge() {

        $fb = new FreeBusyData(100, 200);

        $fb->add(110, 120, 'BUSY');
        $fb->add(130, 140, 'BUSY');

        $this->assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 109,
                    'type' => 'FREE',
                ],
                [
                    'start' => 110,
                    'end' => 120,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 121,
                    'end' => 129,
                    'type' => 'FREE',
                ],
                [
                    'start' => 130,
                    'end' => 140,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 141,
                    'end' => 200,
                    'type' => 'FREE',
                ],
            ],
            $fb->getData()
        );

        $fb->add(115, 135, 'BUSY');

        $this->assertEquals(
            [
                [
                    'start' => 100,
                    'end' => 109,
                    'type' => 'FREE',
                ],
                [
                    'start' => 110,
                    'end' => 140,
                    'type' => 'BUSY',
                ],
                [
                    'start' => 141,
                    'end' => 200,
                    'type' => 'FREE',
                ],
            ],
            $fb->getData()
        );
    }
}
