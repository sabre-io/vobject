<?php

namespace Sabre\VObject\PHPStan\Component\VCard;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Sabre\VObject\Component;
use Sabre\VObject\Component\VCard;

class CreateComponentDynamicMethodReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return VCard::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return 'createComponent' === $methodReflection->getName();
    }

    public function getTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        if ($methodCall->args[0]->value instanceof \PhpParser\Node\Scalar\String_) {
            if (array_key_exists($methodCall->args[0]->value->value, VCard::$componentMap)) {
                return new ObjectType(VCard::$componentMap[$methodCall->args[0]->value->value]);
            }
        }

        return new ObjectType(Component::class);
    }
}
