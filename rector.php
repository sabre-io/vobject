<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\AnnotationsToAttributes\Rector\ClassMethod\DataProviderAnnotationToAttributeRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/lib',
        __DIR__.'/tests',
    ])
    // uncomment to reach your current PHP version
    ->withPhpSets(false, true)
    ->withRules([
        DataProviderAnnotationToAttributeRector::class,
    ])
    ->withTypeCoverageLevel(0)
    ->withDeadCodeLevel(0)
    ->withCodeQualityLevel(0);
