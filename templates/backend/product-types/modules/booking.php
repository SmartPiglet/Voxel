<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="pte-booking-module">
	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Booking settings</h3>
		</div>

		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Select_Model::render( [
				'v-model' => 'config.type',
				'classes' => 'x-col-12',
				'label' => 'Vendor can create bookable',
				'choices' => [
					'days' => 'Days',
					'timeslots' => 'Time slots',
				],
			] ) ?>

			<template v-if="config.type === 'days'">
				<?php \Voxel\Utils\Form_Models\Select_Model::render( [
					'v-model' => 'config.date_ranges.count_mode',
					'label' => 'Count range length using',
					'classes' => 'x-col-12',
					'choices' => [
						'days' => 'Days: Count the number of days in the selected range',
						'nights' => 'Nights: Count the number of nights in the selected range',
					],
				] ) ?>
			</template>

			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.quantity_per_slot.enabled',
				'label' => 'Slots can be booked multiple times',
				'classes' => 'x-col-12',
			] ) ?>
		</div>
	</div>

	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Customer actions</h3>
		</div>
		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.actions.add_to_gcal.customer.enabled',
				'label' => 'Add to Google Calendar',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.actions.add_to_ical.customer.enabled',
				'label' => 'Add to iCal',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.actions.cancel.customer.enabled',
				'label' => 'Cancel booking',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.actions.reschedule.customer.enabled',
				'label' => 'Reschedule booking',
				'classes' => 'x-col-12',
			] ) ?>
		</div>
	</div>

	<div class="ts-group">
		<div class="ts-group-head">
			<h3>Vendor actions</h3>
		</div>
		<div class="x-row">
			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.actions.add_to_gcal.vendor.enabled',
				'label' => 'Add to Google Calendar',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.actions.add_to_ical.vendor.enabled',
				'label' => 'Add to iCal',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.actions.cancel.vendor.enabled',
				'label' => 'Cancel booking',
				'classes' => 'x-col-12',
			] ) ?>

			<?php \Voxel\Utils\Form_Models\Switcher_Model::render( [
				'v-model' => 'config.actions.reschedule.vendor.enabled',
				'label' => 'Reschedule booking',
				'classes' => 'x-col-12',
			] ) ?>
		</div>
	</div>
</script>
