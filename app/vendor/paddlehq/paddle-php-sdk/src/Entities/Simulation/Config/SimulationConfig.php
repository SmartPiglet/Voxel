<?php

declare (strict_types=1);
namespace Voxel\Vendor\Paddle\SDK\Entities\Simulation\Config;

use Voxel\Vendor\Paddle\SDK\Entities\Simulation\Config\Subscription\Cancellation\SubscriptionCancellationConfig;
use Voxel\Vendor\Paddle\SDK\Entities\Simulation\Config\Subscription\Creation\SubscriptionCreationConfig;
use Voxel\Vendor\Paddle\SDK\Entities\Simulation\Config\Subscription\Pause\SubscriptionPauseConfig;
use Voxel\Vendor\Paddle\SDK\Entities\Simulation\Config\Subscription\Renewal\SubscriptionRenewalConfig;
use Voxel\Vendor\Paddle\SDK\Entities\Simulation\Config\Subscription\Resume\SubscriptionResumeConfig;
class SimulationConfig
{
    public function __construct(public readonly SubscriptionCreationConfig|null $subscriptionCreation = null, public readonly SubscriptionRenewalConfig|null $subscriptionRenewal = null, public readonly SubscriptionPauseConfig|null $subscriptionPause = null, public readonly SubscriptionResumeConfig|null $subscriptionResume = null, public readonly SubscriptionCancellationConfig|null $subscriptionCancellation = null)
    {
    }
    public static function from(array $data): self
    {
        return new self(subscriptionCreation: isset($data['subscription_creation']) ? SubscriptionCreationConfig::from($data['subscription_creation']) : null, subscriptionRenewal: isset($data['subscription_renewal']) ? SubscriptionRenewalConfig::from($data['subscription_renewal']) : null, subscriptionPause: isset($data['subscription_pause']) ? SubscriptionPauseConfig::from($data['subscription_pause']) : null, subscriptionResume: isset($data['subscription_resume']) ? SubscriptionResumeConfig::from($data['subscription_resume']) : null, subscriptionCancellation: isset($data['subscription_cancellation']) ? SubscriptionCancellationConfig::from($data['subscription_cancellation']) : null);
    }
}
