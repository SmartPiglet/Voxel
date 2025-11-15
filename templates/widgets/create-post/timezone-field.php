<script type="text/html" id="create-post-timezone-field">
	<form-group :popup-key="field.key" ref="formGroup" @save="onSave" @clear="onClear">
		<template #trigger>
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
			<div class="ts-filter ts-popup-target" :class="{'ts-filled': field.value !== null && field.value !== ''}" @mousedown="$root.activePopup = field.key">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_clock_icon') ) ?: \Voxel\svg( 'clock.svg' ) ?>
				<div class="ts-filter-text">
					<span>{{ field.value || field.props.default }}</span>
				</div>
			</div>
		</template>
		<template #popup>
			<div class="ts-sticky-top uib b-bottom">
				<div class="ts-input-icon flexify">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
					<input v-model="search" ref="searchInput" type="text" placeholder="<?= esc_attr( _x( 'Search timezones', 'timezone field', 'voxel' ) ) ?>" class="autofocus">
				</div>
			</div>
			<div class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
				<ul class="simplify-ul ts-term-dropdown-list min-scroll">
					<li v-for="timezone in choices">
						<a href="#" class="flexify" @click.prevent="field.value = timezone">
							<div class="ts-radio-container">
								<label class="container-radio">
									<input type="radio" :value="timezone" :checked="field.value === timezone" disabled hidden>
									<span class="checkmark"></span>
								</label>
							</div>

							<span>{{ timezone }}</span>

						</a>
					</li>
				</ul>
			</div>
		</template>
	</form-group>
</script>
