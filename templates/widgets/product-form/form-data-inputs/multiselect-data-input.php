<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-data-input-multiselect">
	<div v-if="dataInput.props.display_mode === 'buttons'" class="ts-form-group">
		<label>{{ dataInput.label }}</label>
		<ul class="simplify-ul addon-buttons flexify">
			<template v-for="choice in dataInput.props.choices">
				<li class="flexify" :class="{'adb-selected': isChecked(choice)}" @click.prevent="toggleChoice(choice)">
					{{ choice.label }}
				</li>
			</template>
		</ul>
	</div>
	<div v-else class="ts-form-group ts-custom-additions">
		<label>{{ dataInput.label }}</label>
		<ul class="simplify-ul ts-addition-list flexify">
			<template v-for="choice in dataInput.props.choices">
				<li class="flexify" :class="{ 'ts-checked': isChecked(choice) }">
					<div class="addition-body" @click.prevent="toggleChoice(choice)">
						<label class="container-checkbox">
							<input type="checkbox" :checked="isChecked(choice)" class="onoffswitch-checkbox" disabled hidden>
							<span class="checkmark"></span>
						</label>
						<span>{{ choice.label }}</span>
					</div>
				</li>
			</template>
		</ul>
	</div>
</script>