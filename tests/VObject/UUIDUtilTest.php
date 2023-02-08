<?php

namespace Sabre\VObject;

use PHPUnit\Framework\TestCase;

class UUIDUtilTest extends TestCase
{
    public function testValidateUUID(): void
    {
        self::assertTrue(
            UUIDUtil::validateUUID('11111111-2222-3333-4444-555555555555')
        );
        self::assertFalse(
            UUIDUtil::validateUUID(' 11111111-2222-3333-4444-555555555555')
        );
        self::assertTrue(
            UUIDUtil::validateUUID('ffffffff-2222-3333-4444-555555555555')
        );
        self::assertFalse(
            UUIDUtil::validateUUID('fffffffg-2222-3333-4444-555555555555')
        );
    }

    /**
     * @depends testValidateUUID
     */
    public function testGetUUID(): void
    {
        self::assertTrue(
            UUIDUtil::validateUUID(
                UUIDUtil::getUUID()
            )
        );
    }
}
