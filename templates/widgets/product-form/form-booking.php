<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-booking">
	<div class="ts-form-group">
		<label><?= _x( 'Calendar', 'product form booking', 'voxel' ) ?></label>
		<template v-if="field.props.mode === 'date_range'">
			<book-date-range :booking="this" :field="field" ref="dateRange"></book-date-range>
		</template>
		<template v-else-if="field.props.mode === 'single_day'">
			<book-single-day :booking="this" :field="field" ref="singleDay"></book-single-day>
		</template>
		<template v-else-if="field.props.mode === 'timeslots'">
			<book-timeslot :booking="this" :field="field" ref="timeslot"></book-timeslot>
		</template>
	</div>
</script>

<script type="text/html" id="product-form-book-date-range">
	<form-group popup-key="booking" ref="booking" save-label="<?= esc_attr( _x( 'Close', 'booking calendar', 'voxel' ) ) ?>" wrapper-class="ts-booking-range-wrapper xl-height xl-width" @save="onSave" @blur="onBlur" @clear="onClear">
		<template #trigger>
			<div class="ts-filter ts-popup-target" :class="{'ts-filled': booking.value.start_date}" @mousedown="$root.activePopup = 'booking'; onOpen();">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
				<div v-if="booking.value.start_date" class="ts-filter-text">{{ displayDates() }}</div>
				<div v-else class="ts-filter-text"><?= _x( 'Select a period', 'product form booking', 'voxel' ) ?></div>
				<div class="ts-down-icon"></div>
			</div>
		</template>
		<template #popup>
			<div class="ts-form-group datepicker-head">
				<h3>
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
					{{ popupTitle }}
				</h3>
				<p>{{ popupDescription }}</p>
			</div>
			<div class="ts-booking-date ts-booking-date-range ts-form-group" ref="calendar">
				<input type="hidden" ref="input">
			</div>
		</template>
	</form-group>
</script>

<script type="text/html" id="product-form-book-single-day">
	<form-group popup-key="booking" wrapper-class="lg-width md-height" ref="booking" @save="onSave" @blur="onBlur" @clear="onClear">
		<template #trigger>
			<div class="ts-filter ts-popup-target" :class="{'ts-filled': booking.value.date}" @mousedown="$root.activePopup = 'booking'; onOpen();">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
				<div v-if="booking.value.date" class="ts-filter-text">{{ displayDate() }}</div>
				<div v-else class="ts-filter-text"><?= _x( 'Select date', 'product form booking', 'voxel' ) ?></div>
				<div class="ts-down-icon"></div>
			</div>
		</template>
		<template #popup>
			<div class="ts-form-group datepicker-head">
				<h3>
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
					<template v-if="date">
						{{ booking.dateFormat(date) }}
					</template>
					<template v-else>
						<?= _x( 'Pick a date', 'product form booking', 'voxel' ) ?>
					</template>
				</h3>
			</div>
			<div class="ts-booking-date ts-booking-date-single ts-form-group" ref="calendar">
				<input type="hidden" ref="input">
			</div>
		</template>
	</form-group>
</script>

<script type="text/html" id="product-form-book-timeslot">
	<form-group popup-key="booking" ref="booking" save-label="<?= esc_attr( _x( 'Close', 'booking calendar', 'voxel' ) ) ?>" wrapper-class="ts-booking-timeslots lg-width md-height" @save="onSave" @blur="onBlur" @clear="onClear">
		<template #trigger>
			<div class="ts-filter ts-popup-target" :class="{'ts-filled': booking.value.date && booking.value.slot}" @mousedown="$root.activePopup = 'booking'; onOpen();">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
				<div v-if="booking.value.date && booking.value.slot" class="ts-filter-text">{{ displayDate() }}</div>
				<div v-else class="ts-filter-text"><?= _x( 'Select day and time', 'product form booking', 'voxel' ) ?></div>
				<div class="ts-down-icon"></div>
			</div>
		</template>
		<template #popup>
			<template v-if="date">
				<div class="ts-form-group datepicker-head">
					<h3>
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_timeslot_icon') ) ?: \Voxel\svg( 'clock.svg' ) ?>
						<?= _x( 'Choose slot', 'product form booking', 'voxel' ) ?>
					</h3>
					<p><?= _x( 'Pick a slot for', 'product form booking', 'voxel' ) ?> {{ date ? booking.dateFormat(date) : '' }}</p>
					<a href="#" class="ts-icon-btn" @click.prevent="showCalendar">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
					</a>
				</div>
				<div class="ts-booking-slot ts-form-group">
					<div class="simplify-ul flexify ts-slot-list">
						<template v-for="s in currentDaySlots">
							<a class="ts-btn ts-btn-1" href="#" @click.prevent="pickSlot(s)" :class="{'ts-filled': slot && slot.from === s.from && slot.to === s.to, 'vx-disabled': s._disabled}">
								{{ getSlotLabel(s) }}
							</a>
						</template>
					</div>
				</div>
			</template>
			<template v-else>
				<div class="ts-form-group datepicker-head">
					<h3>
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
						<?= _x( 'Select date', 'product form booking', 'voxel' ) ?>
					</h3>
					<p><?= _x( 'Select a date to view available timeslots', 'product form booking', 'voxel' ) ?></p>
				</div>
				<div class="ts-booking-date ts-booking-date-single ts-form-group" ref="calendar">
					<input type="hidden" ref="input">
				</div>
			</template>
		</template>
	</form-group>
</script>
