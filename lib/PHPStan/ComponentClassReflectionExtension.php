<?php

namespace Sabre\VObject\PHPStan;

use PHPStan\Broker\Broker;
use PHPStan\Reflection\BrokerAwareExtension;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\PropertiesClassReflectionExtension;
use PHPStan\Reflection\PropertyReflection;
use Sabre\VObject\Component;
use Sabre\VObject\Component\VCalendar;
use Sabre\VObject\Document;
use Sabre\VObject\Property\Unknown;

class ComponentClassReflectionExtension implements PropertiesClassReflectionExtension, BrokerAwareExtension
{
    const MAP_NAMES = [
        'valueMap',
        'componentMap',
        'propertyMap',
    ];

    /** @var Broker */
    private $broker;

    public function setBroker(Broker $broker): void
    {
        $this->broker = $broker;
    }

    public function hasProperty(ClassReflection $classReflection, string $propertyName): bool
    {
        if ($classReflection->isSubclassOf(Document::class)) {
            return true;
        }

        if ($classReflection->isSubclassOf(Component::class)) {
            if (in_array($classReflection->getName(), VCalendar::$componentMap)) {
                return $this->hasProperty(
                    $this->broker->getClass(VCalendar::class),
                    $propertyName
                );
            }
        }

        return false;
    }

    public function getProperty(ClassReflection $classReflection, string $propertyName): PropertyReflection
    {
        if ($classReflection->isSubclassOf(Document::class) && ($property = $this->getMappedProperty($classReflection, $propertyName))) {
            return $property;
        }

        if (!$classReflection->isSubclassOf(Document::class) && $classReflection->isSubclassOf(Component::class)) {
            if (in_array($classReflection->getName(), VCalendar::$componentMap)) {
                return $this->getProperty(
                    $this->broker->getClass(VCalendar::class),
                    $propertyName
                );
            }
        }

        return new ComponentProperty($classReflection, Unknown::class);
    }

    private function getMappedProperty(ClassReflection $classReflection, string $propertyName): ?PropertyReflection
    {
        $propertyName = strtoupper($propertyName);

        foreach (self::MAP_NAMES as $mapName) {
            $map = $classReflection->getName()::$$mapName;

            if (isset($map[$propertyName])) {
                return new ComponentProperty($classReflection, $map[$propertyName]);
            }
        }

        return null;
    }
}
