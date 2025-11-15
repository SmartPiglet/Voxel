<?php

namespace Voxel\Vendor\Http\Client\Common\Plugin;

use Voxel\Vendor\Http\Client\Common\Plugin;
use Voxel\Vendor\Http\Client\Exception;
use Voxel\Vendor\Http\Message\Formatter;
use Voxel\Vendor\Http\Message\Formatter\SimpleFormatter;
use Voxel\Vendor\Http\Promise\Promise;
use Voxel\Vendor\Psr\Http\Message\RequestInterface;
use Voxel\Vendor\Psr\Http\Message\ResponseInterface;
use Voxel\Vendor\Psr\Log\LoggerInterface;
/**
 * Log request, response and exception for an HTTP Client.
 *
 * @author Joel Wurtz <joel.wurtz@gmail.com>
 */
final readonly class LoggerPlugin implements Plugin
{
    private Formatter $formatter;
    public function __construct(private LoggerInterface $logger, ?Formatter $formatter = null)
    {
        $this->formatter = $formatter ?? new SimpleFormatter();
    }
    public function handleRequest(RequestInterface $request, callable $next, callable $first): Promise
    {
        $start = hrtime(\true) / 1000000.0;
        $uid = uniqid('', \true);
        $this->logger->info(sprintf("Sending request:\n%s", $this->formatter->formatRequest($request)), ['uid' => $uid]);
        return $next($request)->then(function (ResponseInterface $response) use ($start, $uid, $request) {
            $milliseconds = (int) round(hrtime(\true) / 1000000.0 - $start);
            $formattedResponse = $this->formatter->formatResponseForRequest($response, $request);
            $this->logger->info(sprintf("Received response:\n%s", $formattedResponse), ['milliseconds' => $milliseconds, 'uid' => $uid]);
            return $response;
        }, function (Exception $exception) use ($request, $start, $uid) {
            $milliseconds = (int) round(hrtime(\true) / 1000000.0 - $start);
            if ($exception instanceof Exception\HttpException) {
                $formattedResponse = $this->formatter->formatResponseForRequest($exception->getResponse(), $exception->getRequest());
                $this->logger->error(sprintf("Error:\n%s\nwith response:\n%s", $exception->getMessage(), $formattedResponse), ['exception' => $exception, 'milliseconds' => $milliseconds, 'uid' => $uid]);
            } else {
                $this->logger->error(sprintf("Error:\n%s\nwhen sending request:\n%s", $exception->getMessage(), $this->formatter->formatRequest($request)), ['exception' => $exception, 'milliseconds' => $milliseconds, 'uid' => $uid]);
            }
            throw $exception;
        });
    }
}
