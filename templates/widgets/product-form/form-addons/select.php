<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-select">
	<template v-if="addon.props.display_mode === 'buttons'">
		<div class="ts-form-group">
			<label>{{ addon.label }}</label>
			<ul class="simplify-ul addon-buttons flexify">
				<li v-for="choice in addon.props.choices" class="flexify" :class="{ 'adb-selected': value.selected === choice.value }" @click.prevent="value.selected = ( choice.value === value.selected ? null : choice.value )">
					{{ choice.label }}
				</li>
			</ul>
		</div>
	</template>
	<template v-else-if="addon.props.display_mode === 'dropdown'">
		<div class="ts-form-group">
			<label>{{ addon.label }}</label>
			<div class="ts-filter">
				<select v-model="value.selected">
					<option v-if="!addon.required" :value="null"><?= _x( 'Select choice', 'product form dropdown', 'voxel' ) ?></option>
					<option v-for="choice in addon.props.choices" :value="choice.value">{{ choice.label }}</option>
				</select>
				<div class="ts-down-icon"></div>
			</div>
		</div>
	</template>
	<template v-else>
		<div class="ts-form-group inline-terms-wrapper ts-inline-filter">
			<label>{{ addon.label }}</label>
			<ul class="simplify-ul ts-addition-list flexify">
				<li v-for="choice in addon.props.choices" class="flexify" :class="{ 'ts-checked': value.selected === choice.value }">
					<div class="addition-body" @click.prevent="value.selected = ( choice.value === value.selected ? null : choice.value )">
						<label class="container-radio">
							<input type="radio" :value="choice.value" :checked="value.selected === choice.value" class="onoffswitch-checkbox" disabled hidden>
							<span class="checkmark"></span>
						</label>
						<span>{{ choice.label }}</span>
						<div class="vx-addon-price">
							{{ addons.getPriceForChoice(addon, choice) }}
						</div>
					</div>
				</li>
			</ul>
		</div>
	</template>
</script>
