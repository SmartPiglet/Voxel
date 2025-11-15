<?php

declare (strict_types=1);
namespace Voxel\Vendor\Http\Client\Common\Plugin;

use Voxel\Vendor\Http\Client\Common\Plugin;
use Voxel\Vendor\Http\Promise\Promise;
use Voxel\Vendor\Psr\Http\Client\ClientExceptionInterface;
use Voxel\Vendor\Psr\Http\Message\RequestInterface;
use Voxel\Vendor\Psr\Http\Message\ResponseInterface;
/**
 * Record HTTP calls.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
final class HistoryPlugin implements Plugin
{
    /**
     * Journal use to store request / responses / exception.
     *
     * @var Journal
     */
    private $journal;
    public function __construct(Journal $journal)
    {
        $this->journal = $journal;
    }
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $journal = $this->journal;
        return $next($request)->then(function (ResponseInterface $response) use ($request, $journal) {
            $journal->addSuccess($request, $response);
            return $response;
        }, function (ClientExceptionInterface $exception) use ($request, $journal) {
            $journal->addFailure($request, $exception);
            throw $exception;
        });
    }
}
