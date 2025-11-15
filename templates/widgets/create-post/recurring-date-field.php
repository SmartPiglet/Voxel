<script type="text/html" id="create-post-recurring-date-field">
	<div class="ts-form-group">
		<label>
			{{ field.label }}
			<slot name="errors"></slot>
			<div class="vx-dialog" v-if="field.description">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
				<div class="vx-dialog-content min-scroll">
					<p>{{ field.description }}</p>
				</div>
			</div>
		</label>

		<template v-for="date, index in field.value">
			<div class="ts-repeater-container">
				<div class="ts-field-repeater">
					<div class="ts-repeater-head" @click.prevent="$root.toggleRow($event)">
						<label><?= _x( 'Date', 'recurring date field', 'voxel' ) ?></label>
						<div class="ts-repeater-controller">
							<a href="#" @click.prevent="remove(date)" class="ts-icon-btn ts-smaller">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
							</a>
							<a href="#" class="ts-icon-btn ts-smaller" @click.prevent>
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
							</a>
						</div>
					</div>
					<div class="medium form-field-grid">
						<div class="ts-form-group switcher-label vx-1-3">
							<label>
								<div class="switch-slider">
									<div class="onoffswitch">
									    <input type="checkbox" class="onoffswitch-checkbox" v-model="date.multiday">
									    <label class="onoffswitch-label" @click.prevent="date.multiday = !date.multiday"></label>
									</div>
								</div>
								<?= _x( 'Multi-day?', 'recurring date field', 'voxel' ) ?>
							</label>
						</div>
						<form-group
							v-if="date.multiday"
							:popup-key="id(index,'from')"
							:ref="id(index,'from')"
							class="vx-1-1"
							wrapper-class="ts-availability-wrapper xl-width xl-height"
							@mousedown="$root.activePopup = id(index,'from')"
							@save="$refs[id(index,'from')][0].blur()"
							@clear="clearDate(date)"
						>
							<template #trigger>
								<div class="ts-filter ts-popup-target" :class="{'ts-filled': date.startDate !== null && date.endDate !== null}">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
									<div class="ts-filter-text">
										<template v-if="date.startDate === null">
											<?= _x( 'Select dates', 'recurring date field', 'voxel' ) ?>
										</template>
										<template v-else>
											{{ ! getStartDate(date)
											? <?= wp_json_encode( _x( 'From', 'recurring date field', 'voxel' ) ) ?>
											: formatDate( getStartDate( date ) ) }}
											&mdash;
											{{ ! getEndDate(date)
											? <?= wp_json_encode( _x( 'To', 'recurring date field', 'voxel' ) ) ?>
											: formatDate( getEndDate( date ) ) }}
										</template>
									</div>
								</div>
							</template>
							<template #popup>
								<date-range-picker ref="rangePicker" :date="date" @save="$refs[id(index,'from')][0].blur()"></date-range-picker>
							</template>
						</form-group>
						<form-group
							v-else
							:popup-key="id(index,'from')"
							:ref="id(index,'from')"
							class="vx-1-1"
							wrapper-class="md-width xl-height"
							@mousedown="$root.activePopup = id(index,'from')"
							@save="$refs[id(index,'from')][0].blur()"
							@clear="clearDate(date)"
						>
							<template #trigger>
								<div class="ts-filter ts-popup-target" :class="{'ts-filled': date.startDate !== null}">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
									<div class="ts-filter-text">
									{{ ! getStartDate(date)
										? <?= wp_json_encode( _x( 'Select date', 'recurring date field', 'voxel' ) ) ?>
										: ( field.props.enable_timepicker
											? format( getStartDate( date ) )
											: formatDate( getStartDate( date ) )
										) }}
									</div>
								</div>
							</template>
							<template #popup>
								<date-picker v-model="date.startDate" @update:model-value="$refs[id(index,'from')][0].blur(); singleDatePicked(date)"></date-picker>
							</template>
						</form-group>

						<template v-if="field.props.enable_timepicker">
							<div class="ts-form-group switcher-label">
								<label @click.prevent="date.allday = !date.allday">

									<div class="switch-slider">
										<div class="onoffswitch">
											<input type="checkbox" :checked="date.allday" class="onoffswitch-checkbox">
											<label class="onoffswitch-label" ></label>
										</div>
									</div>
									<?= _x( 'All-day event', 'recurring date field', 'voxel' ) ?>
								</label>
							</div>

							<template v-if="!date.allday">
								<div class="ts-form-group vx-1-2">
									<label><?= _x( 'Start time', 'recurring date field', 'voxel' ) ?></label>
									<input type="time" v-model="date.startTime" class="ts-filter" onfocus="this.showPicker()">
								</div>
								<div class="ts-form-group vx-1-2">
									<label><?= _x( 'End time', 'recurring date field', 'voxel' ) ?></label>
									<input type="time" v-model="date.endTime" class="ts-filter" onfocus="this.showPicker()">
								</div>
							</template>
						</template>

						<template v-if="field.props.allow_recurrence">
							<div class="ts-form-group switcher-label">
								<label>
									<div class="switch-slider">
										<div class="onoffswitch">
											<input type="checkbox" v-model="date.repeat" class="onoffswitch-checkbox">
											<label class="onoffswitch-label" @click.prevent="date.repeat=!date.repeat"></label>
										</div>
									</div>
									<?= _x( 'Recurring event?', 'recurring date field', 'voxel' ) ?>
								</label>
							</div>
							<template v-if="date.repeat">
								<div class="ts-form-group vx-1-3">
									<label><?= _x( 'Repeats every', 'recurring date field', 'voxel' ) ?></label>
									<input v-model="date.frequency" type="number" class="ts-filter">
								</div>
								<form-group
									:popup-key="id(index,'unit')"
									:ref="id(index,'unit')"
									class="vx-1-3"
									:show-clear="false"
									:show-save="false"
									:show-close="true"
									save-label="<?= esc_attr( _x( 'Close', 'recurring date field', 'voxel' ) ) ?>"
								>
									<template #trigger>
										<label><?= _x( 'Period', 'recurring date field', 'voxel' ) ?></label>
										<div class="ts-filter ts-filled" @mousedown="$root.activePopup = id(index,'unit')">
											<div class="ts-filter-text">{{ field.props.units[ date.unit ] }}</div>
										</div>
									</template>
									<template #popup>
										<div class="ts-term-dropdown ts-md-group">
											<ul class="simplify-ul ts-term-dropdown-list min-scroll">
												<li v-for="unit_label, unit in field.props.units">
													<a href="#" class="flexify" @click.prevent="date.unit = unit; $refs[id(index,'unit')][0].blur()">
														<div class="ts-checkbox-container">
															<label class="container-radio">
																<input type="radio" :value="unit" :checked="date.unit === unit" disabled hidden>
																<span class="checkmark"></span>
															</label>
														</div>
														<span>{{ unit_label }}</span>
													</a>
												</li>
											</ul>
										</div>
									</template>
								</form-group>

								<form-group
									:popup-key="id(index,'until')"
									:ref="id(index,'until')"
									class="vx-1-3"
									@clear="date.until = null"
									@save="$refs[id(index,'until')][0].blur()"
									wrapper-class="xl-height md-width"
								>
									<template #trigger>
										<label><?= _x( 'Until', 'recurring date field', 'voxel' ) ?></label>
										<div class="ts-filter ts-popup-target" :class="{'ts-filled': date.until !== null}" @mousedown="$root.activePopup = id(index,'until')">
											<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
											<div class="ts-filter-text">
												<template v-if="getUntilDate(date)">
													{{ formatDate( getUntilDate(date) ) }}
												</template>
												<template v-else>
													<?= _x( 'Date', 'recurring date field', 'voxel' ) ?>
												</template>
											</div>
										</div>
									</template>
									<template #popup>
										<date-picker v-model="date.until" @update:model-value="$refs[id(index,'until')][0].blur()"></date-picker>
									</template>
								</form-group>
							</template>
						</template>
					</div>
				</div>
			</div>
		</template>
		<a
			href="#"
			v-if="field.value.length < field.props.max_date_count"
			@click.prevent="add"
			class="ts-repeater-add ts-btn ts-btn-4 form-btn"
		>
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
			<?= _x( 'Add date', 'recurring date field', 'voxel' ) ?>
		</a>
	</div>
</script>

<script type="text/html" id="recurring-date-picker">
	<div class="ts-form-group ts-booking-date ts-booking-date-single" ref="calendar">
		<input type="hidden" ref="input">
	</div>
</script>

<script type="text/html" id="recurring-date-range-picker">
	<div class="ts-popup-head flexify">
		<div class="ts-popup-name flexify">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
			<span>
				<span :class="{chosen: activePicker === 'start'}" @click.prevent="activePicker = 'start'">
					{{ startLabel }}
				</span>
				<span v-if="value.start"> &mdash; </span>
				<span v-if="value.start" :class="{chosen: activePicker === 'end'}" @click.prevent="activePicker = 'end'">
					{{ endLabel }}
				</span>
			</span>
		</div>
	</div>
	<div class="ts-booking-date ts-booking-date-range ts-form-group" ref="calendar">
		<input type="hidden" ref="input">
	</div>
</script>
