<?php

namespace Sabre\VObject\PHPStan\Component;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use Sabre\VObject\Component;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Property;

class SelectDynamicMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Component::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return 'select' === $methodReflection->getName();
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        if ($methodCall->args[0]->value instanceof \PhpParser\Node\Scalar\String_) {
            if (array_key_exists($methodCall->args[0]->value->value, VCalendar::$propertyMap)) {
                return $this->createArrayType(
                    VCalendar::$propertyMap[$methodCall->args[0]->value->value]
                );
            }

            if (array_key_exists($methodCall->args[0]->value->value, VCalendar::$componentMap)) {
                return $this->createArrayType(
                    VCalendar::$componentMap[$methodCall->args[0]->value->value]
                );
            }

            if (array_key_exists($methodCall->args[0]->value->value, VCard::$propertyMap)) {
                return $this->createArrayType(
                    VCard::$propertyMap[$methodCall->args[0]->value->value]
                );
            }

            if (array_key_exists($methodCall->args[0]->value->value, VCard::$componentMap)) {
                return $this->createArrayType(
                    VCard::$componentMap[$methodCall->args[0]->value->value]
                );
            }
        }

        return new ArrayType(
            new IntegerType(),
            new UnionType([
                new ObjectType(Component::class),
                new ObjectType(Property::class),
            ])
        );
    }

    private function createArrayType(string $class): ArrayType
    {
        return new ArrayType(
            new IntegerType(),
            new ObjectType($class)
        );
    }
}
