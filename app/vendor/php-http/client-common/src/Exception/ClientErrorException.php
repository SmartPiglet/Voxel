<?php

declare (strict_types=1);
namespace Voxel\Vendor\Http\Client\Common\Exception;

use Voxel\Vendor\Http\Client\Exception\HttpException;
/**
 * Thrown when there is a client error (4xx).
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
final class ClientErrorException extends HttpException
{
}
