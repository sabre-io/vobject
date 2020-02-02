<?php

namespace Sabre\VObject\PHPStan\Component;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Sabre\VObject\Component;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Component\VCard;
use Sabre\VObject\Property;

class AddDynamicMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Component::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return 'add' === $methodReflection->getName();
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        if ($methodCall->args[0]->value instanceof \PhpParser\Node\Scalar\String_) {
            if (array_key_exists($methodCall->args[0]->value->value, VCalendar::$componentMap)) {
                return new ObjectType(VCalendar::$componentMap[$methodCall->args[0]->value->value]);
            }

            if (array_key_exists($methodCall->args[0]->value->value, VCalendar::$propertyMap)) {
                return new ObjectType(VCalendar::$propertyMap[$methodCall->args[0]->value->value]);
            }

            if (array_key_exists($methodCall->args[0]->value->value, VCard::$componentMap)) {
                return new ObjectType(VCard::$componentMap[$methodCall->args[0]->value->value]);
            }

            if (array_key_exists($methodCall->args[0]->value->value, VCard::$propertyMap)) {
                return new ObjectType(VCard::$propertyMap[$methodCall->args[0]->value->value]);
            }
        }

        if (2 === count($methodCall->args)) {
            return new ObjectType(Component::class);
        }

        if (3 === count($methodCall->args)) {
            return new ObjectType(Property::class);
        }

        return $scope->getType($methodCall->args[0]->value);
    }
}
