<?php

namespace Voxel\Vendor;

if (\PHP_VERSION_ID < 80000 && !\interface_exists('Stringable')) {
    interface Stringable
    {
        /**
         * @return string
         */
        public function __toString();
    }
    \class_alias('Voxel\Vendor\Stringable', 'Stringable', \false);
}
