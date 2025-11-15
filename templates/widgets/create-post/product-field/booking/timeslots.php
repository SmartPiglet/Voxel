<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-timeslots">
	<div class="ts-repeater-container">
		<div v-for="group, groupIndex in groups" class="ts-field-repeater" :class="{collapsed: active !== group}">
			<div class="ts-repeater-head" @click.prevent="active = group === active ? null : group; showGenerate = false;">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_clock_icon') ) ?: \Voxel\svg( 'clock.svg' ) ?>
				<label>
					{{ groupLabelShort(group) || <?= wp_json_encode( _x( 'Schedule', 'product field', 'voxel' ) ) ?> }}
				</label>
				<em>
					<template v-if="group.slots.length < 1"><?= _x( 'No slots', 'product field timeslots', 'voxel' ) ?></template>
					<template v-else-if="group.slots.length === 1"><?= _x( '1 slot', 'product field timeslots', 'voxel' ) ?></template>
					<template v-else><?= \Voxel\replace_vars( _x( '@count slots', 'product field timeslots', 'voxel' ), [
						'@count' => '{{ group.slots.length }}',
					] ) ?></template>
				</em>
				<div class="ts-repeater-controller">
					<a href="#" @click.stop.prevent="removeGroup(group)" class="ts-icon-btn ts-smaller">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
					</a>
					<a href="#" class="ts-icon-btn ts-smaller" @click.prevent>
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
					</a>
				</div>
			</div>
			<div v-if="active === group" class="elementor-row medium form-field-grid">
				<form-group
					:popup-key="groupKey(groupIndex)"
					:ref="groupKey(groupIndex)"
					class="ts-form-group"
					@save="$refs[groupKey(groupIndex)][0].blur()"
					@clear="group.days = []"
					wrapper-class="prmr-popup"
				>
					<template #trigger>
						<label><?= _x( 'Select days with this schedule', 'product field', 'voxel' ) ?></label>
						<div class="ts-filter ts-popup-target" @mousedown="$root.activePopup = groupKey(groupIndex)" :class="{'ts-filled': group.days.length}">
							<div class="ts-filter-text">
								{{ groupLabelShort(group) || <?= wp_json_encode( _x( 'Choose day(s)', 'product field', 'voxel' ) ) ?> }}
							</div>
							 <div class="ts-down-icon"></div>
						</div>
					</template>
					<template #popup>
						<div class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
							<ul class="simplify-ul ts-term-dropdown-list min-scroll">
								<template v-for="day_label, day_key in booking.field.props.weekdays" >
									<li  v-if="isDayAvailable( day_key, group )">
										<a href="#" class="flexify" @click.prevent="toggleDay( day_key, group )">
											<div class="ts-checkbox-container">
												<label class="container-checkbox">
													<input type="checkbox" :value="day_key" :checked="isDayUsed( day_key, group )" disabled hidden>
													<span class="checkmark"></span>
												</label>
											</div>
											<span>{{ day_label }}</span>
											<div class="ts-term-icon">
												<span><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calalt_icon') ) ?: \Voxel\svg( 'cal-alt.svg' ) ?></span>
											</div>
										</a>
									</li>
								</template>
							</ul>
						</div>
					</template>
				</form-group>

				<div v-if="showGenerate" class="ts-form-group">
					<div class="form-field-grid medium">
						<div class="ts-form-group ui-heading-field">
							<label><?= _x( 'Generate timeslots', 'product field timeslots', 'voxel' ) ?></label>
						</div>
						<div class="ts-form-group">
							<label>
								<?= _x( 'Time range', 'product field', 'voxel' ) ?>
							</label>
							<div class="form-field-grid medium">
								<div class="ts-form-group vx-1-2">
									<input type="time" v-model="generate.from"  onfocus="this.showPicker()" class="ts-filter">
								</div>
								<div class="ts-form-group vx-1-2">
									<input type="time" v-model="generate.to" class="ts-filter" onfocus="this.showPicker()">
								</div>
							</div>
						</div>
						<div class="ts-form-group vx-1-2">
							<label>
								<?= _x( 'Slot length (Minutes)', 'product field', 'voxel' ) ?>
							</label>
							<input type="number" v-model="generate.length" min="5" class="ts-filter">
						</div>
						<div class="ts-form-group vx-1-2">
							<label>
								<?= _x( 'Time between slots (Minutes)', 'product field', 'voxel' ) ?>
							</label>
							<input type="number" v-model="generate.gap" min="5" class="ts-filter">
						</div>
						<div class="ts-form-group vx-2-3">
							<a href="#" @click.prevent="generateSlots(group)" class="ts-repeater-add ts-btn ts-btn-2 form-btn">
								<?= _x( 'Generate', 'product field timeslots', 'voxel' ) ?>
							</a>
						</div>
						<div class="ts-form-group vx-1-3">
							<a href="#" @click.prevent="showGenerate = false" class="ts-repeater-add ts-btn ts-btn-1 form-btn">
								<?= _x( 'Cancel', 'product field timeslots', 'voxel' ) ?>
							</a>
						</div>
					</div>
				</div>
				<div v-else class="ts-form-group">
					<div class="form-field-grid medium">
						<div class="ts-form-group ui-heading-field">
							<label><?= _x( 'Time slots', 'product field', 'voxel' ) ?></label>
						</div>
						<template v-for="slot, slotIndex in group.slots">
							<div class="ts-form-group vx-1-3">
								<input type="time" class="ts-filter" v-model="slot.from" onfocus="this.showPicker()">
							</div>
							<div class="ts-form-group vx-1-3">

								<input type="time" class="ts-filter" v-model="slot.to" onfocus="this.showPicker()">
							</div>
							<div class="ts-form-group vx-1-3 vx-center-right">

								<a href="#" @click.prevent="removeSlot(slot, group)" class="ts-btn ts-btn-1 form-btn">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?><?= _x( 'Remove', 'product field timeslots', 'voxel' ) ?>

								</a>
							</div>
						</template>

						<div class="ts-form-group vx-1-2" :class="{'vx-disabled': group.slots.length >= 50}">
							<a href="#" @click.prevent="createSlot(group)" class="ts-repeater-add ts-btn ts-btn-4 form-btn">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
								<?= _x( 'Create timeslot', 'product field', 'voxel' ) ?>
							</a>
						</div>
						<div class="ts-form-group vx-1-2">
							<a href="#" @click.prevent="showGenerate = !showGenerate" class="ts-repeater-add ts-btn ts-btn-4 form-btn">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_generate_icon') ) ?: \Voxel\svg( 'cog.svg' ) ?>
								<?= _x( 'Generate timeslots', 'product field', 'voxel' ) ?>
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<a v-if="unusedDays.length" href="#" @click.prevent="addGroup" class="ts-repeater-add ts-btn ts-btn-4 form-btn">
		<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
		<?= _x( 'Add schedule', 'product field', 'voxel' ) ?>
	</a>
</script>
