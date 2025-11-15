<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK;

use Voxel\Vendor\Http\Client\Common\Plugin\AuthenticationPlugin;
use Voxel\Vendor\Http\Client\Common\Plugin\ContentLengthPlugin;
use Voxel\Vendor\Http\Client\Common\Plugin\ContentTypePlugin;
use Voxel\Vendor\Http\Client\Common\Plugin\DecoderPlugin;
use Voxel\Vendor\Http\Client\Common\Plugin\HeaderSetPlugin;
use Voxel\Vendor\Http\Client\Common\Plugin\LoggerPlugin;
use Voxel\Vendor\Http\Client\Common\Plugin\ResponseSeekableBodyPlugin;
use Voxel\Vendor\Http\Client\Common\Plugin\RetryPlugin;
use Voxel\Vendor\Http\Client\Common\PluginClient;
use Voxel\Vendor\Http\Client\HttpAsyncClient;
use Voxel\Vendor\Http\Discovery\HttpAsyncClientDiscovery;
use Voxel\Vendor\Http\Discovery\Psr17FactoryDiscovery;
use Voxel\Vendor\Http\Message\Authentication\Bearer;
use Voxel\Vendor\Paddle\SDK\Logger\Formatter;
use Voxel\Vendor\Paddle\SDK\Resources\Addresses\AddressesClient;
use Voxel\Vendor\Paddle\SDK\Resources\Adjustments\AdjustmentsClient;
use Voxel\Vendor\Paddle\SDK\Resources\Businesses\BusinessesClient;
use Voxel\Vendor\Paddle\SDK\Resources\CustomerPortalSessions\CustomerPortalSessionsClient;
use Voxel\Vendor\Paddle\SDK\Resources\Customers\CustomersClient;
use Voxel\Vendor\Paddle\SDK\Resources\DiscountGroups\DiscountGroupsClient;
use Voxel\Vendor\Paddle\SDK\Resources\Discounts\DiscountsClient;
use Voxel\Vendor\Paddle\SDK\Resources\Events\EventsClient;
use Voxel\Vendor\Paddle\SDK\Resources\EventTypes\EventTypesClient;
use Voxel\Vendor\Paddle\SDK\Resources\NotificationLogs\NotificationLogsClient;
use Voxel\Vendor\Paddle\SDK\Resources\Notifications\NotificationsClient;
use Voxel\Vendor\Paddle\SDK\Resources\NotificationSettings\NotificationSettingsClient;
use Voxel\Vendor\Paddle\SDK\Resources\PaymentMethods\PaymentMethodsClient;
use Voxel\Vendor\Paddle\SDK\Resources\Prices\PricesClient;
use Voxel\Vendor\Paddle\SDK\Resources\PricingPreviews\PricingPreviewsClient;
use Voxel\Vendor\Paddle\SDK\Resources\Products\ProductsClient;
use Voxel\Vendor\Paddle\SDK\Resources\Reports\ReportsClient;
use Voxel\Vendor\Paddle\SDK\Resources\SimulationRunEvents\SimulationRunEventsClient;
use Voxel\Vendor\Paddle\SDK\Resources\SimulationRuns\SimulationRunsClient;
use Voxel\Vendor\Paddle\SDK\Resources\Simulations\SimulationsClient;
use Voxel\Vendor\Paddle\SDK\Resources\SimulationTypes\SimulationTypesClient;
use Voxel\Vendor\Paddle\SDK\Resources\Subscriptions\SubscriptionsClient;
use Voxel\Vendor\Paddle\SDK\Resources\Transactions\TransactionsClient;
use Voxel\Vendor\Psr\Http\Message\RequestFactoryInterface;
use Voxel\Vendor\Psr\Http\Message\ResponseInterface;
use Voxel\Vendor\Psr\Http\Message\StreamFactoryInterface;
use Voxel\Vendor\Psr\Http\Message\UriFactoryInterface;
use Voxel\Vendor\Psr\Http\Message\UriInterface;
use Voxel\Vendor\Psr\Log\LoggerInterface;
use Voxel\Vendor\Psr\Log\NullLogger;
use Voxel\Vendor\Symfony\Component\Uid\Ulid;
class Client
{
    private const SDK_VERSION = '1.10.0';
    public readonly LoggerInterface $logger;
    public readonly Options $options;
    public readonly ProductsClient $products;
    public readonly PricesClient $prices;
    public readonly TransactionsClient $transactions;
    public readonly AdjustmentsClient $adjustments;
    public readonly CustomersClient $customers;
    public readonly CustomerPortalSessionsClient $customerPortalSessions;
    public readonly AddressesClient $addresses;
    public readonly BusinessesClient $businesses;
    public readonly DiscountsClient $discounts;
    public readonly DiscountGroupsClient $discountGroups;
    public readonly SubscriptionsClient $subscriptions;
    public readonly EventTypesClient $eventTypes;
    public readonly EventsClient $events;
    public readonly PricingPreviewsClient $pricingPreviews;
    public readonly PaymentMethodsClient $paymentMethods;
    public readonly NotificationSettingsClient $notificationSettings;
    public readonly NotificationsClient $notifications;
    public readonly NotificationLogsClient $notificationLogs;
    public readonly ReportsClient $reports;
    public readonly SimulationsClient $simulations;
    public readonly SimulationRunsClient $simulationRuns;
    public readonly SimulationRunEventsClient $simulationRunEvents;
    public readonly SimulationTypesClient $simulationTypes;
    private readonly HttpAsyncClient $httpClient;
    private readonly RequestFactoryInterface $requestFactory;
    private readonly StreamFactoryInterface $streamFactory;
    private readonly UriFactoryInterface $uriFactory;
    private string|null $transactionId = null;
    public function __construct(
        #[\SensitiveParameter]
        private readonly string $apiKey,
        Options|null $options = null,
        HttpAsyncClient|null $httpClient = null,
        LoggerInterface|null $logger = null,
        RequestFactoryInterface|null $requestFactory = null,
        StreamFactoryInterface|null $streamFactory = null,
        UriFactoryInterface|null $uriFactory = null
    )
    {
        $this->options = $options ?: new Options();
        $this->logger = $logger ?: new NullLogger();
        $this->requestFactory = $requestFactory ?: Psr17FactoryDiscovery::findRequestFactory();
        $this->streamFactory = $streamFactory ?: Psr17FactoryDiscovery::findStreamFactory();
        $this->uriFactory = $uriFactory ?: Psr17FactoryDiscovery::findUriFactory();
        $this->httpClient = $this->buildClient($httpClient ?: HttpAsyncClientDiscovery::find());
        $this->products = new ProductsClient($this);
        $this->prices = new PricesClient($this);
        $this->transactions = new TransactionsClient($this);
        $this->adjustments = new AdjustmentsClient($this);
        $this->customers = new CustomersClient($this);
        $this->addresses = new AddressesClient($this);
        $this->customerPortalSessions = new CustomerPortalSessionsClient($this);
        $this->businesses = new BusinessesClient($this);
        $this->discounts = new DiscountsClient($this);
        $this->discountGroups = new DiscountGroupsClient($this);
        $this->subscriptions = new SubscriptionsClient($this);
        $this->eventTypes = new EventTypesClient($this);
        $this->events = new EventsClient($this);
        $this->pricingPreviews = new PricingPreviewsClient($this);
        $this->paymentMethods = new PaymentMethodsClient($this);
        $this->notificationSettings = new NotificationSettingsClient($this);
        $this->notifications = new NotificationsClient($this);
        $this->notificationLogs = new NotificationLogsClient($this);
        $this->reports = new ReportsClient($this);
        $this->simulations = new SimulationsClient($this);
        $this->simulationRuns = new SimulationRunsClient($this);
        $this->simulationRunEvents = new SimulationRunEventsClient($this);
        $this->simulationTypes = new SimulationTypesClient($this);
    }
    public function getRaw(string|UriInterface $uri, array|HasParameters $parameters = []): ResponseInterface
    {
        if ($parameters) {
            $parameters = $parameters instanceof HasParameters ? $parameters->getParameters() : $parameters;
            $query = \http_build_query($parameters);
            if ($uri instanceof UriInterface) {
                $uri = $uri->withQuery($query);
            } else {
                $uri .= '?' . $query;
            }
        }
        return $this->requestRaw('GET', $uri);
    }
    public function patchRaw(string|UriInterface $uri, array|\JsonSerializable $payload): ResponseInterface
    {
        return $this->requestRaw('PATCH', $uri, $payload);
    }
    public function postRaw(string|UriInterface $uri, array|\JsonSerializable|null $payload = [], array|HasParameters $parameters = []): ResponseInterface
    {
        if ($parameters) {
            $parameters = $parameters instanceof HasParameters ? $parameters->getParameters() : $parameters;
            $query = \http_build_query($parameters);
            if ($uri instanceof UriInterface) {
                $uri = $uri->withQuery($query);
            } else {
                $uri .= '?' . $query;
            }
        }
        return $this->requestRaw('POST', $uri, $payload);
    }
    public function deleteRaw(string|UriInterface $uri): ResponseInterface
    {
        return $this->requestRaw('DELETE', $uri);
    }
    private function requestRaw(string $method, string|UriInterface $uri, array|\JsonSerializable|null $payload = null): ResponseInterface
    {
        if (\is_string($uri)) {
            $components = \parse_url($this->options->environment->baseUrl());
            $uri = $this->uriFactory->createUri($uri)->withScheme($components['scheme'])->withHost($components['host']);
        }
        $request = $this->requestFactory->createRequest($method, $uri);
        if ($payload !== null) {
            $body = JsonEncoder::default()->encode($payload);
            $request = $request->withBody(
                // Satisfies empty body requests.
                $this->streamFactory->createStream($body === '[]' ? '{}' : $body)
            );
        }
        $request = $request->withAddedHeader('X-Transaction-ID', $this->transactionId ?? (string) new Ulid());
        return $this->httpClient->sendAsyncRequest($request)->wait();
    }
    private function buildClient(HttpAsyncClient $httpClient): PluginClient
    {
        return new PluginClient($httpClient, [new AuthenticationPlugin(new Bearer($this->apiKey)), new ContentTypePlugin(), new ContentLengthPlugin(), new DecoderPlugin(['use_content_encoding' => \false]), new HeaderSetPlugin(['User-Agent' => 'PaddleSDK/php ' . self::SDK_VERSION]), new RetryPlugin(['retries' => $this->options->retries]), new LoggerPlugin($this->logger, new Formatter()), new ResponseSeekableBodyPlugin()]);
    }
}
