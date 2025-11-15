<?php

declare (strict_types=1);
namespace Voxel\Vendor\Http\Client\Common\Exception;

use Voxel\Vendor\Http\Client\Exception\TransferException;
/**
 * Thrown when a http client cannot be chosen in a pool.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
final class HttpClientNotFoundException extends TransferException
{
}
