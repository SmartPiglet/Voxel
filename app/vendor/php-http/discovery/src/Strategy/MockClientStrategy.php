<?php

namespace Voxel\Vendor\Http\Discovery\Strategy;

use Voxel\Vendor\Http\Client\HttpAsyncClient;
use Voxel\Vendor\Http\Client\HttpClient;
use Voxel\Vendor\Http\Mock\Client as Mock;
/**
 * Find the Mock client.
 *
 * @author Sam Rapaport <me@samrapdev.com>
 */
final class MockClientStrategy implements DiscoveryStrategy
{
    public static function getCandidates($type)
    {
        if (is_a(HttpClient::class, $type, \true) || is_a(HttpAsyncClient::class, $type, \true)) {
            return [['class' => Mock::class, 'condition' => Mock::class]];
        }
        return [];
    }
}
