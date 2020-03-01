<?php

namespace Sabre\VObject\PHPStan;

use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertyReflection;
use PHPStan\TrinaryLogic;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Sabre\VObject\Property\ICalendar\DateTime;
use Sabre\VObject\Property\ICalendar\Duration;

class ComponentProperty implements PropertyReflection
{
    /** @var \PHPStan\Reflection\ClassReflection */
    private $declaringClass;

    /** @var string */
    private $className;

    public function __construct(ClassReflection $declaringClass, string $className)
    {
        $this->declaringClass = $declaringClass;
        $this->className = $className;
    }

    public function getDeclaringClass(): ClassReflection
    {
        return $this->declaringClass;
    }

    public function isStatic(): bool
    {
        return false;
    }

    public function isPrivate(): bool
    {
        return false;
    }

    public function isPublic(): bool
    {
        return true;
    }

    public function getReadableType(): Type
    {
        if (Duration::class === $this->className) {
            return new UnionType([
                new ObjectType($this->className),
                new ObjectType(DateTime::class),
            ]);
        }

        return new ObjectType($this->className);
    }

    public function getWritableType(): Type
    {
        return new ObjectType($this->className);
    }

    public function canChangeTypeAfterAssignment(): bool
    {
        return false;
    }

    public function isReadable(): bool
    {
        return true;
    }

    public function isWritable(): bool
    {
        return true;
    }

    public function isDeprecated(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getDeprecatedDescription(): ?string
    {
        return null;
    }

    public function isInternal(): TrinaryLogic
    {
        return TrinaryLogic::createNo();
    }

    public function getDocComment(): ?string
    {
        return null;
    }
}
