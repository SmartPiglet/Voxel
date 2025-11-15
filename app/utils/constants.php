<?php

namespace Voxel;

if ( ! defined('ABSPATH') ) {
	exit;
}

const FOLLOW_REQUESTED = 0;
const FOLLOW_ACCEPTED  = 1;
const FOLLOW_BLOCKED   = -1;
const FOLLOW_NONE      = null;

const MODERATION_PENDING  = 0;
const MODERATION_APPROVED = 1;

const ORDER_PENDING_PAYMENT  = 'pending_payment';
const ORDER_PENDING_APPROVAL = 'pending_approval';
const ORDER_COMPLETED        = 'completed';
const ORDER_CANCELED         = 'canceled';
const ORDER_REFUNDED         = 'refunded';

const PRODUCT_ERR_OUT_OF_STOCK = 10;
const PRODUCT_ERR_NO_PLATFORM_SHIPPING_ZONES = 11;
const PRODUCT_ERR_NO_VENDOR_SHIPPING_ZONES = 12;