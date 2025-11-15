<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-form-group" :class="{'vx-1-2': !field.props.quantity_per_slot.enabled, 'vx-1-3': field.props.quantity_per_slot.enabled}">
	<label><?= _x( 'Availability', 'product field timeslots', 'voxel' ) ?></label>
	<div class="input-container">
		<input type="number" v-model="value.availability.max_days" min="0" class="ts-filter">
		<span class="input-suffix"><?= _x( 'Days', 'product field timeslots', 'voxel' ) ?></span>
	</div>
</div>

<div class="ts-form-group" :class="{'vx-1-2': !field.props.quantity_per_slot.enabled, 'vx-1-3': field.props.quantity_per_slot.enabled}">
	<label><?= _x( 'Buffer period', 'product field booking', 'voxel' ) ?></label>
	<div class="input-container">
		<input type="number" v-model="value.availability.buffer.amount" min="0" class="ts-filter">
		<span
			class="input-suffix suffix-action"
			@click.prevent="value.availability.buffer.unit = value.availability.buffer.unit === 'days' ? 'hours' : 'days'"
		>
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('switch_ico') ) ?: \Voxel\svg( 'switch.svg' ) ?>
			<template v-if="value.availability.buffer.unit === 'hours'">
				<?= _x( 'Hours', 'product field timeslots', 'voxel' ) ?>
			</template>
			<template v-else>
				<?= _x( 'Days', 'product field timeslots', 'voxel' ) ?>
			</template>
		</span>
	</div>
</div>

<template v-if="field.props.quantity_per_slot.enabled">
	<div class="ts-form-group vx-1-3">
		<label><?= _x( 'Quantity per slot', 'product field timeslots', 'voxel' ) ?></label>
		<input type="number" v-model="value.quantity_per_slot" class="ts-filter">
	</div>
</template>

<div class="ts-form-group">
	<label><?= _x( 'Timeslots', 'product field', 'voxel' ) ?></label>
	<time-slots ref="timeslots" :booking="this"></time-slots>
</div>

<div class="ts-form-group switcher-label">
	<label>
		<div class="switch-slider">
			<div class="onoffswitch">
				<input type="checkbox" class="onoffswitch-checkbox" v-model="value.excluded_days_enabled">
				<label
					class="onoffswitch-label"
					@click.prevent="value.excluded_days_enabled = ! value.excluded_days_enabled"
				></label>
			</div>
		</div>
		<?= _x( 'Exclude specific dates', 'product field timeslots', 'voxel' ) ?>
	</label>
</div>

<div v-if="value.excluded_days_enabled" class="ts-form-group">
	<label>
		<?= _x( 'Calendar', 'product field', 'voxel' ) ?>
		<div class="vx-dialog">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
			<div class="vx-dialog-content min-scroll">
				<p><?= _x( 'Availability visualization based on your settings. Click to exclude specific days', 'product field', 'voxel' ) ?></p>
			</div>
		</div>
	</label>
	<booking-calendar ref="calendar" :booking="this"></booking-calendar>
</div>
