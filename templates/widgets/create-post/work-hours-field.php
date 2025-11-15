<script type="text/html" id="create-post-work-hours-field">
	<div class="ts-work-hours-field ts-form-group">
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
		<template v-for="group, index in field.value">
			<form-group
				:ref="id(index)"
				class="work-hours-field ts-repeater-container"
				:popup-key="id(index)"
				:default-class="false"
				@clear="group.days = []"
				@save="$refs[id(index)][0].blur()"
				wrapper-class="prmr-popup"
			>
				<template #trigger>
					<div class="ts-field-repeater">
						<div class="ts-repeater-head"  @click.prevent="$root.toggleRow($event)">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('clock_icon') ) ?: \Voxel\svg( 'clock.svg' ) ?>
							<label>
								<?= _x( 'Schedule', 'work hours field', 'voxel' ) ?>
							</label>
							<div class="ts-repeater-controller">
								<a href="#" @click.prevent="removeGroup(group)" class="ts-icon-btn ts-smaller">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
								</a>
								<a href="#" class="ts-icon-btn ts-smaller" @click.prevent>
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
								</a>
							</div>
						</div>
						<div class="elementor-row medium form-field-grid">
							<div class="ts-form-group">
								<label>
									<?= _x( 'Select days with this schedule', 'work hours field', 'voxel' ) ?>
								</label>
								<div class="ts-filter ts-popup-target ts-datepicker-input" :class="{'ts-filled': group.days.length}" @mousedown="$root.activePopup = id(index)">
									
									<div v-if="group.days.length" class="ts-filter-text">{{ displayDays( group.days ) }}</div>
									<div v-else class="ts-filter-text"><?= _x( 'Select day(s)', 'work hours field', 'voxel' ) ?></div>
									 <div class="ts-down-icon"></div>
								</div>
							</div>
							<template v-if="group.days.length">
								<div class="ts-form-group">
									<label>
										<?= _x( 'Set availability', 'work hours field', 'voxel' ) ?>
									</label>
									<div class="ts-filter">
									    <select v-model="group.status">
									        <option v-for="(label, status) in field.props.statuses" :value="status">
									            {{ label }}
									        </option>
									    </select>
									    <div class="ts-down-icon"></div>
									</div>

								</div>
								<template v-if="group.status === 'hours'">
									<div class="ts-form-group">
										<label>
											<?= _x( 'Add work hours', 'work hours field', 'voxel' ) ?>
										</label>
						 				<div class="form-field-grid medium">
											<template v-if="group.hours.length">
												<div class="ts-form-group ">
													<div class="form-field-grid medium">
														<template v-for="hours in group.hours">
															<div class="ts-form-group vx-1-3">
																<input type="time" class="ts-filter" v-model="hours.from" onfocus="this.showPicker()">
															</div>
															<div class="ts-form-group vx-1-3">
																<input type="time" class="ts-filter" v-model="hours.to" onfocus="this.showPicker()">
															</div>
															<div class="ts-form-group vx-1-3 vx-center-right">
																<a href="#" @click.prevent="removeHours(hours, group)" class="ts-btn ts-btn-1 form-btn">
																	<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?><?= _x( 'Remove', 'work hours field', 'voxel' ) ?>
																</a>
															</div>
														</template>
													</div>
												</div>
											</template>
											<div class="ts-form-group">
												<a href="#" @click.prevent="addHours(group)" class="ts-repeater-add add-hours form-btn ts-btn ts-btn-4">
													<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
													<?= _x( 'Add hours', 'work hours field', 'voxel' ) ?>
												</a>
											</div>
										</div>
									</div>
								</template>
							</template>
						</div>
					</div>
				</template>
				<template #popup>
					<div class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
						<ul class="simplify-ul ts-term-dropdown-list min-scroll">
							<li v-for="label, key in field.props.weekdays">
								<a href="#" v-if="isDayAvailable( key, group )" @click.prevent="check( key, group.days )" class="flexify">
									<div class="ts-checkbox-container">
										<label class="container-checkbox">
											<input :checked="isChecked( key, group.days )" type="checkbox" disabled hidden>
											<span class="checkmark"></span>
										</label>
									</div>

									<span>{{ label }}</span>
									
								</a>
							</li>
						</ul>
					</div>
				</template>
			</form-group>
		</template>

		<a v-if="unusedDays.length" href="#" @click.prevent="addGroup" class="ts-repeater-add ts-btn form-btn ts-btn-4">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
			<?= _x( 'Create schedule', 'work hours field', 'voxel' ) ?>
		</a>
	</div>
</script>
