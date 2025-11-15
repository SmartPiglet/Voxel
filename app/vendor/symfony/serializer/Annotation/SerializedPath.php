<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Voxel\Vendor\Symfony\Component\Serializer\Annotation;

class_exists(\Voxel\Vendor\Symfony\Component\Serializer\Attribute\SerializedPath::class);
if (\false) {
    #[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
    class SerializedPath extends \Voxel\Vendor\Symfony\Component\Serializer\Attribute\SerializedPath
    {
    }
}
