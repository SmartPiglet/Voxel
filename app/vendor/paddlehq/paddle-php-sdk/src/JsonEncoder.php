<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK;

use Voxel\Vendor\Paddle\SDK\Entities\DateTime;
use Voxel\Vendor\Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Voxel\Vendor\Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Voxel\Vendor\Symfony\Component\Serializer\Normalizer\BackedEnumNormalizer;
use Voxel\Vendor\Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Voxel\Vendor\Symfony\Component\Serializer\Normalizer\JsonSerializableNormalizer;
use Voxel\Vendor\Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Voxel\Vendor\Symfony\Component\Serializer\Serializer;
use Voxel\Vendor\Symfony\Component\Serializer\SerializerInterface;
final class JsonEncoder
{
    private function __construct(private readonly SerializerInterface $serializer)
    {
    }
    public static function default(): self
    {
        return new self(new Serializer([new BackedEnumNormalizer(), new DateTimeNormalizer([DateTimeNormalizer::FORMAT_KEY => DateTime::PADDLE_RFC3339]), new JsonSerializableNormalizer(), new ObjectNormalizer(nameConverter: new CamelCaseToSnakeCaseNameConverter())], [new \Voxel\Vendor\Symfony\Component\Serializer\Encoder\JsonEncoder()]));
    }
    public function encode(mixed $payload): string
    {
        return $this->serializer->serialize($payload, 'json', [AbstractObjectNormalizer::PRESERVE_EMPTY_OBJECTS => \true]);
    }
}
