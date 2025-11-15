<?php

declare (strict_types=1);
namespace Voxel\Vendor;

use Voxel\Vendor\Rector\Config\RectorConfig;
use Rector\Php81\Rector\Class_\MyCLabsClassToEnumRector;
use Voxel\Vendor\Rector\Php81\Rector\MethodCall\MyCLabsMethodCallToEnumConstRector;
use Voxel\Vendor\Rector\Set\ValueObject\LevelSetList;
use Voxel\Vendor\Rector\Set\ValueObject\SetList;
return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([LevelSetList::UP_TO_PHP_81, SetList::CODE_QUALITY, SetList::DEAD_CODE, SetList::TYPE_DECLARATION]);
    $rectorConfig->paths([__DIR__ . '/src', __DIR__ . '/tests']);
    $rectorConfig->skip(['*.json', '*/Fixture/*', MyCLabsClassToEnumRector::class, MyCLabsMethodCallToEnumConstRector::class]);
    $rectorConfig->importNames();
    $rectorConfig->importShortClasses(\false);
};
