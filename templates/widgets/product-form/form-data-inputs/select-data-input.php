<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-data-input-select">
	<div v-if="dataInput.props.display_mode === 'buttons'" class="ts-form-group">
		<label>{{ dataInput.label }}</label>
		<ul class="simplify-ul addon-buttons flexify">
			<li v-for="choice in dataInput.props.choices" class="flexify" :class="{ 'adb-selected': isChecked(choice) }" @click.prevent="toggleChoice(choice)">
				{{ choice.label }}
			</li>
		</ul>
	</div>
	<div v-else-if="dataInput.props.display_mode === 'radio'" class="ts-form-group inline-terms-wrapper ts-inline-filter">
		<label>{{ dataInput.label }}</label>
		<ul class="simplify-ul ts-addition-list flexify">
			<li v-for="choice in dataInput.props.choices" class="flexify" :class="{ 'ts-checked': isChecked(choice) }">
				<div class="addition-body" @click.prevent="toggleChoice(choice)">
					<label class="container-radio">
						<input type="radio" :value="choice.value" :checked="isChecked(choice)" class="onoffswitch-checkbox" disabled hidden>
						<span class="checkmark"></span>
					</label>
					<span>{{ choice.label }}</span>
				</div>
			</li>
		</ul>
	</div>
	<div v-else class="ts-form-group">
		<label>{{ dataInput.label }}</label>
		<div class="ts-filter">
			<select v-model="dataInputs.values[ dataInput.key ]">
				<option :value="null">{{ dataInput.props.placeholder || dataInput.props.l10n.default_placeholder }}</option>
				<option v-for="choice in dataInput.props.choices" :value="choice.value">{{ choice.label }}</option>
			</select>
			<div class="ts-down-icon"></div>
		</div>
	</div>
</script>