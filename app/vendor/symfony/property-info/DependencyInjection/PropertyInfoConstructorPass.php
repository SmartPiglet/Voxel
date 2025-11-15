<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Voxel\Vendor\Symfony\Component\PropertyInfo\DependencyInjection;

use Voxel\Vendor\Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Voxel\Vendor\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Voxel\Vendor\Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Voxel\Vendor\Symfony\Component\DependencyInjection\ContainerBuilder;
/**
 * Adds extractors to the property_info.constructor_extractor service.
 *
 * @author Dmitrii Poddubnyi <dpoddubny@gmail.com>
 */
final class PropertyInfoConstructorPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;
    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition('property_info.constructor_extractor')) {
            return;
        }
        $definition = $container->getDefinition('property_info.constructor_extractor');
        $listExtractors = $this->findAndSortTaggedServices('property_info.constructor_extractor', $container);
        $definition->replaceArgument(0, new IteratorArgument($listExtractors));
    }
}
