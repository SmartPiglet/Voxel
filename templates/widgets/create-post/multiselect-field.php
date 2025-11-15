<script type="text/html" id="create-post-multiselect-field">
	<template v-if="field.props.display_as === 'inline'">
		<div class="ts-form-group inline-terms-wrapper ts-inline-filter">
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
			<div class="ts-term-dropdown ts-md-group ts-multilevel-dropdown inline-multilevel min-scroll">
				<ul class="simplify-ul ts-term-dropdown-list">
					<li v-for="choice in field.props.choices" :class="{'ts-selected': choice.value === value}">
						<a href="#" class="flexify" @click.prevent="selectChoice(choice)">
							<div class="ts-checkbox-container">
								<label class="container-checkbox">
									<input
										type="checkbox"
										:value="choice.value"
										:checked="value[choice.value]"
										disabled
										hidden
									>
									<span class="checkmark"></span>
								</label>
							</div>
							<span>{{ choice.label }}</span>
							<div class="ts-term-icon">
								<span v-html="choice.icon"></span>
							</div>
						</a>
					</li>
				</ul>
			</div>
		</div>
	</template>
	<form-group v-else wrapper-class="prmr-popup vx-full-popup" :popup-key="field.id+':'+index" ref="formGroup" @blur="saveValue" @save="onSave" @clear="onClear">
		<template #trigger>
			<!-- <pre debug>{{ field.value }}</pre> -->
			<!-- <pre debug>{{ value }}</pre> -->
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
			<div class="ts-filter ts-popup-target" :class="{'ts-filled': field.value !== null}" @mousedown="$root.activePopup = field.id+':'+index">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('popup_icon') ) ?: \Voxel\svg( 'menu.svg' ) ?>
				<div class="ts-filter-text">
					<span v-if="field.value !== null">{{ displayValue }}</span>
					<span v-else>{{ field.props.placeholder }}</span>
				</div>
				<div class="ts-down-icon"></div>
			</div>
		</template>
		<template #popup>
			<div class="ts-sticky-top uib b-bottom" v-if="field.props.choices.length >= 15">
				<div class="ts-input-icon flexify">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
					<input
						v-model="search" ref="searchInput" type="text" class="autofocus"
						:placeholder="<?= esc_attr( wp_json_encode( _x( 'Search', 'taxonomy field', 'voxel' ) ) ) ?>"
					>
				</div>
			</div>

			<div v-if="searchResults" class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
				<ul class="simplify-ul ts-term-dropdown-list">
					<li v-for="choice in searchResults" :class="{'ts-selected': !!value[choice.value]}">
						<a href="#" class="flexify" @click.prevent="selectChoice(choice)">
							<div class="ts-checkbox-container">
								<label class="container-checkbox">
									<input type="checkbox" :value="choice.value" :checked="value[choice.value]" disabled hidden>
									<span class="checkmark"></span>
								</label>
							</div>
							<span>{{ choice.label }}</span>
							<div class="ts-term-icon">
								<span v-html="choice.icon"></span>
							</div>
						</a>
					</li>
				</ul>
				<div v-if="!searchResults.length" class="ts-empty-user-tab">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('popup_icon') ) ?: \Voxel\svg( 'menu.svg' ) ?>
					<p><?= _x( 'No results found', 'terms filter', 'voxel' ) ?></p>
				</div>
			</div>
			<div v-else class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
				<ul class="simplify-ul ts-term-dropdown-list">
					<li v-for="choice in field.props.choices" :class="{'ts-selected': !!value[choice.value]}">
						<a href="#" class="flexify" @click.prevent="selectChoice(choice)">
							<div class="ts-checkbox-container">
								<label class="container-checkbox">
									<input type="checkbox" :value="choice.value" :checked="value[choice.value]" disabled hidden>
									<span class="checkmark"></span>
								</label>
							</div>
							<span>{{ choice.label }}</span>
							<div class="ts-term-icon">
								<span v-html="choice.icon"></span>
							</div>
						</a>
					</li>
				</ul>
			</div>
		</template>
	</form-group>
</script>
