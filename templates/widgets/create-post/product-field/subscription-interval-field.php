<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-subscription-interval">
	<div class="ts-form-group">
		<label><?= _x( 'Subscription interval', 'product field subscriptions', 'voxel' ) ?></label>
		<div class="form-field-grid">
			<div class="ts-form-group vx-1-2">
				<input v-model="value.frequency" type="number" class="ts-filter" min="1" :max="maxFrequency">
			</div>
			<div class="ts-form-group vx-1-2">
				<div class="ts-filter">
					<select v-model="value.unit">
						<option value="day"><?= _x( 'Day(s)', 'product field subscriptions', 'voxel' ) ?></option>
						<option value="week"><?= _x( 'Week(s)', 'product field subscriptions', 'voxel' ) ?></option>
						<option value="month"><?= _x( 'Month(s)', 'product field subscriptions', 'voxel' ) ?></option>
						<option value="year"><?= _x( 'Year(s)', 'product field subscriptions', 'voxel' ) ?></option>
					</select>
					<div class="ts-down-icon"></div>
				</div>
			</div>
		</div>
	</div>
</script>
