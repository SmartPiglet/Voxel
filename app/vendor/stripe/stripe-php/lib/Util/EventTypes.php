<?php

namespace Voxel\Vendor\Stripe\Util;

class EventTypes
{
    const thinEventMapping = [
        // The beginning of the section generated from our OpenAPI spec
        \Voxel\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEvent::LOOKUP_TYPE => \Voxel\Vendor\Stripe\Events\V1BillingMeterErrorReportTriggeredEvent::class,
        \Voxel\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEvent::LOOKUP_TYPE => \Voxel\Vendor\Stripe\Events\V1BillingMeterNoMeterFoundEvent::class,
        \Voxel\Vendor\Stripe\Events\V2CoreEventDestinationPingEvent::LOOKUP_TYPE => \Voxel\Vendor\Stripe\Events\V2CoreEventDestinationPingEvent::class,
    ];
}
