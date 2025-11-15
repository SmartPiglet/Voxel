<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

	<ul class="inner-tabs">
		<li :class="{'current-item': tab === 'general'}">
			<a href="#" @click.prevent="tab = 'general'">General</a>
		</li>
		<li :class="{'current-item': tab === 'payments'}">
			<a href="#" @click.prevent="tab = 'payments'">Payments</a>
		</li>
		<li :class="{'current-item': tab === 'subscriptions'}">
			<a href="#" @click.prevent="tab = 'subscriptions'">Subscriptions</a>
		</li>
		<li :class="{'current-item': tab === 'tax'}">
			<a href="#" @click.prevent="tab = 'tax'">Tax collection</a>
		</li>
		<li :class="{'current-item': tab === 'stripe_connect'}">
			<a href="#" @click.prevent="tab = 'stripe_connect'">Stripe Connect</a>
		</li>
	</ul>

	<template v-if="tab ==='general'">
		<?php require locate_template('app/modules/stripe-payments/templates/backend/stripe-settings-general.php') ?>
	</template>
	<template v-else-if="tab ==='payments'">
		<?php require locate_template('app/modules/stripe-payments/templates/backend/stripe-settings-payments.php') ?>
	</template>
	<template v-else-if="tab ==='subscriptions'">
		<?php require locate_template('app/modules/stripe-payments/templates/backend/stripe-settings-subscriptions.php') ?>
	</template>
	<template v-else-if="tab ==='tax'">
		<?php require locate_template('app/modules/stripe-payments/templates/backend/stripe-settings-tax.php') ?>
	</template>
	<template v-else-if="tab ==='stripe_connect'">
		<?php require locate_template('app/modules/stripe-payments/templates/backend/stripe-settings-connect.php') ?>
	</template>

