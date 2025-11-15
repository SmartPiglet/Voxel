<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-multiselect">
	<template v-if="addon.props.display_mode === 'buttons'">
		<div class="ts-form-group">
			<label>{{ addon.label }}</label>
			<ul class="simplify-ul addon-buttons flexify">
				<li v-for="choice in addon.props.choices" class="flexify" :class="{ 'adb-selected': value.selected.includes(choice.value) }" @click.prevent="toggle(choice)">
					{{ choice.label }}
				</li>
			</ul>
		</div>
	</template>
	<template v-else>
		<div class="ts-form-group inline-terms-wrapper ts-inline-filter">
			<label>{{ addon.label }}</label>
			<ul class="simplify-ul ts-addition-list flexify">
				<li v-for="choice in addon.props.choices" class="flexify" :class="{ 'ts-checked': value.selected.includes(choice.value) }">
					<div class="addition-body" @click.prevent="toggle(choice)">
						<label class="container-checkbox">
							<input type="checkbox" :value="choice.value" :checked="value.selected.includes(choice.value)" class="onoffswitch-checkbox" disabled hidden>
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
