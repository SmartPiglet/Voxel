<script type="text/html" id="create-post-select-field">
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
						<a href="#" class="flexify" @click.prevent="value = ( choice.value === value ? null : choice.value ); saveValue();">
							<div class="ts-radio-container">
								<label class="container-radio">
									<input
										type="radio"
										:value="choice.value"
										:checked="value === choice.value"
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
	<template v-else>
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
			<div class="ts-filter">
			    <select v-model="field.value" :required="field.required">
			        <option v-if="!field.required" :value="null">{{ field.props.placeholder || field.label }}</option>
			        <option v-for="choice in field.props.choices" :value="choice.value">{{ choice.label }}</option>
			    </select>
			    <div class="ts-down-icon"></div>
			</div>
		</div>
	</template>
</script>
