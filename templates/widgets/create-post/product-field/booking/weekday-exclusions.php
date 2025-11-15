<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-weekday-exclusions">
	<form-group
		:popup-key="booking.product.field.key+':weekdays'"
		ref="formGroup"
		@save="$refs.formGroup.blur()"
		@clear="booking.value.excluded_weekdays = []"
	>
		<template #trigger>
			<label><?= _x( 'Exclude days of week', 'product field', 'voxel' ) ?></label>
			<div
				class="ts-filter ts-popup-target"
				:class="{'ts-filled': booking.value.excluded_weekdays.length}"
				@mousedown="$root.activePopup = booking.product.field.key+':weekdays'"
			>
				<span><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calminus_icon') ) ?: \Voxel\svg( 'calendar-minus.svg' ) ?></span>
				<div class="ts-filter-text">
					<template v-if="booking.value.excluded_weekdays.length">
						{{ label }}
					</template>
					<template v-else>
						<?= _x( 'Click to exclude weekdays', 'product field', 'voxel' ) ?>
					</template>
				</div>
			</div>
		</template>
		<template #popup>
			<div class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
				<ul class="simplify-ul ts-term-dropdown-list min-scroll">
					<li v-for="day_label, day in booking.field.props.weekdays">
						<a href="#" class="flexify" @click.prevent="toggleDay(day)">
							<div class="ts-checkbox-container">
								<label class="container-checkbox">
									<input type="checkbox" :checked="booking.value.excluded_weekdays.includes(day)" disabled hidden>
									<span class="checkmark"></span>
								</label>
							</div>
							<span>{{ day_label }}</span>
							<div class="ts-term-icon">
								<span><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calminus_icon') ) ?: \Voxel\svg( 'calendar-minus.svg' ) ?></span>
							</div>
						</a>
					</li>
				</ul>
			</div>
		</template>
	</form-group>
</script>
