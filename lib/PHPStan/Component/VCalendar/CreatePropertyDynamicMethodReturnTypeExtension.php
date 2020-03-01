<?php

namespace Sabre\VObject\PHPStan\Component\VCalendar;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Property;

class CreatePropertyDynamicMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return VCalendar::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return 'createProperty' === $methodReflection->getName();
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        if ($methodCall->args[0]->value instanceof \PhpParser\Node\Scalar\String_) {
            if (array_key_exists($methodCall->args[0]->value->value, VCalendar::$propertyMap)) {
                return new ObjectType(VCalendar::$propertyMap[$methodCall->args[0]->value->value]);
            }
        }

        return new ObjectType(Property::class);
    }
}
