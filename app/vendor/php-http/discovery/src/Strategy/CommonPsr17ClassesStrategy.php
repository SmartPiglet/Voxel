<?php

namespace Voxel\Vendor\Http\Discovery\Strategy;

use Voxel\Vendor\Psr\Http\Message\RequestFactoryInterface;
use Voxel\Vendor\Psr\Http\Message\ResponseFactoryInterface;
use Voxel\Vendor\Psr\Http\Message\ServerRequestFactoryInterface;
use Voxel\Vendor\Psr\Http\Message\StreamFactoryInterface;
use Voxel\Vendor\Psr\Http\Message\UploadedFileFactoryInterface;
use Voxel\Vendor\Psr\Http\Message\UriFactoryInterface;
/**
 * @internal
 *
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 *
 * Don't miss updating src/Composer/Plugin.php when adding a new supported class.
 */
final class CommonPsr17ClassesStrategy implements DiscoveryStrategy
{
    /**
     * @var array
     */
    private static $classes = [RequestFactoryInterface::class => ['Voxel\Vendor\Phalcon\Http\Message\RequestFactory', 'Voxel\Vendor\Nyholm\Psr7\Factory\Psr17Factory', 'Voxel\Vendor\GuzzleHttp\Psr7\HttpFactory', 'Voxel\Vendor\Http\Factory\Diactoros\RequestFactory', 'Voxel\Vendor\Http\Factory\Guzzle\RequestFactory', 'Voxel\Vendor\Http\Factory\Slim\RequestFactory', 'Voxel\Vendor\Laminas\Diactoros\RequestFactory', 'Voxel\Vendor\Slim\Psr7\Factory\RequestFactory', 'Voxel\Vendor\HttpSoft\Message\RequestFactory'], ResponseFactoryInterface::class => ['Voxel\Vendor\Phalcon\Http\Message\ResponseFactory', 'Voxel\Vendor\Nyholm\Psr7\Factory\Psr17Factory', 'Voxel\Vendor\GuzzleHttp\Psr7\HttpFactory', 'Voxel\Vendor\Http\Factory\Diactoros\ResponseFactory', 'Voxel\Vendor\Http\Factory\Guzzle\ResponseFactory', 'Voxel\Vendor\Http\Factory\Slim\ResponseFactory', 'Voxel\Vendor\Laminas\Diactoros\ResponseFactory', 'Voxel\Vendor\Slim\Psr7\Factory\ResponseFactory', 'Voxel\Vendor\HttpSoft\Message\ResponseFactory'], ServerRequestFactoryInterface::class => ['Voxel\Vendor\Phalcon\Http\Message\ServerRequestFactory', 'Voxel\Vendor\Nyholm\Psr7\Factory\Psr17Factory', 'Voxel\Vendor\GuzzleHttp\Psr7\HttpFactory', 'Voxel\Vendor\Http\Factory\Diactoros\ServerRequestFactory', 'Voxel\Vendor\Http\Factory\Guzzle\ServerRequestFactory', 'Voxel\Vendor\Http\Factory\Slim\ServerRequestFactory', 'Voxel\Vendor\Laminas\Diactoros\ServerRequestFactory', 'Voxel\Vendor\Slim\Psr7\Factory\ServerRequestFactory', 'Voxel\Vendor\HttpSoft\Message\ServerRequestFactory'], StreamFactoryInterface::class => ['Voxel\Vendor\Phalcon\Http\Message\StreamFactory', 'Voxel\Vendor\Nyholm\Psr7\Factory\Psr17Factory', 'Voxel\Vendor\GuzzleHttp\Psr7\HttpFactory', 'Voxel\Vendor\Http\Factory\Diactoros\StreamFactory', 'Voxel\Vendor\Http\Factory\Guzzle\StreamFactory', 'Voxel\Vendor\Http\Factory\Slim\StreamFactory', 'Voxel\Vendor\Laminas\Diactoros\StreamFactory', 'Voxel\Vendor\Slim\Psr7\Factory\StreamFactory', 'Voxel\Vendor\HttpSoft\Message\StreamFactory'], UploadedFileFactoryInterface::class => ['Voxel\Vendor\Phalcon\Http\Message\UploadedFileFactory', 'Voxel\Vendor\Nyholm\Psr7\Factory\Psr17Factory', 'Voxel\Vendor\GuzzleHttp\Psr7\HttpFactory', 'Voxel\Vendor\Http\Factory\Diactoros\UploadedFileFactory', 'Voxel\Vendor\Http\Factory\Guzzle\UploadedFileFactory', 'Voxel\Vendor\Http\Factory\Slim\UploadedFileFactory', 'Voxel\Vendor\Laminas\Diactoros\UploadedFileFactory', 'Voxel\Vendor\Slim\Psr7\Factory\UploadedFileFactory', 'Voxel\Vendor\HttpSoft\Message\UploadedFileFactory'], UriFactoryInterface::class => ['Voxel\Vendor\Phalcon\Http\Message\UriFactory', 'Voxel\Vendor\Nyholm\Psr7\Factory\Psr17Factory', 'Voxel\Vendor\GuzzleHttp\Psr7\HttpFactory', 'Voxel\Vendor\Http\Factory\Diactoros\UriFactory', 'Voxel\Vendor\Http\Factory\Guzzle\UriFactory', 'Voxel\Vendor\Http\Factory\Slim\UriFactory', 'Voxel\Vendor\Laminas\Diactoros\UriFactory', 'Voxel\Vendor\Slim\Psr7\Factory\UriFactory', 'Voxel\Vendor\HttpSoft\Message\UriFactory']];
    public static function getCandidates($type)
    {
        $candidates = [];
        if (isset(self::$classes[$type])) {
            foreach (self::$classes[$type] as $class) {
                $candidates[] = ['class' => $class, 'condition' => [$class]];
            }
        }
        return $candidates;
    }
}
