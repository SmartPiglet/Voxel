<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-custom-select">
	<template v-if="addon.props.display_mode === 'radio' && shouldShowAddon()">
		<div class="ts-form-group ts-custom-additions">
			<label>{{ addon.label }}</label>
			<ul class="simplify-ul ts-addition-list flexify">
				<template v-for="choice in addon.props.choices">
					<li v-if="shouldShowChoice(choice)" class="flexify" :class="{ 'ts-checked': value.selected.item === choice.value }">
						<div class="addition-body" @click.prevent="toggleChoice(choice)">
							<label class="container-radio">
								<input type="radio" :checked="value.selected.item === choice.value" class="onoffswitch-checkbox" disabled hidden>
								<span class="checkmark"></span>
							</label>
							<span>{{ choice.label }}</span>
							<div class="vx-addon-price">
								{{ addons.getPriceForChoice(addon, choice) }}
							</div>
						</div>
						<div v-if="value.selected.item === choice.value && choice.quantity.enabled && choice.value" class="ts-stepper-input flexify custom-addon-stepper">
							<button class="ts-stepper-left ts-icon-btn ts-smaller" @click.prevent="decrementQuantity(choice)"
								:class="{'vx-disabled': choice.quantity.min >= value.selected.quantity}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
							</button>
							<input v-model="value.selected.quantity" type="number" class="ts-input-box ts-smaller" @change="validateQuantity(choice)">
							<button class="ts-stepper-right ts-icon-btn ts-smaller" @click.prevent="incrementQuantity(choice)"
								:class="{'vx-disabled': choice.quantity.max <= value.selected.quantity}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
							</button>
						</div>
					</li>
				</template>
			</ul>
		</div>
	</template>
	<template v-else-if="addon.props.display_mode === 'buttons' && shouldShowAddon()">
		<div class="ts-form-group">
			<label>{{ addon.label }}</label>
			<ul class="simplify-ul addon-buttons flexify">
				<template v-for="choice in addon.props.choices">
					<li v-if="shouldShowChoice(choice)" class="flexify" :class="{'adb-selected': value.selected.item === choice.value}" @click.prevent="toggleChoice(choice)">
						{{ choice.label }}
					</li>
				</template>
			</ul>
		</div>
	</template>
	<template v-else-if="addon.props.display_mode === 'cards' && shouldShowAddon()">
		<div class="ts-form-group">
			<label>{{ addon.label }}</label>
			<ul class="simplify-ul addon-cards flexify">
				<template v-for="choice in addon.props.choices">
					<li v-if="shouldShowChoice(choice)" class="flexify" :class="{ 'adc-selected': value.selected.item === choice.value }" @click.prevent="toggleChoice(choice)">
						<img v-if="choice.image !== null" :src="choice.image.url" :title="choice.image.alt" :alt="choice.image.alt">
						<div class="addon-details">
							<span class="adc-title">
								{{ choice.label }}
							</span>
							<span v-if="choice.subheading !== null && choice.subheading.length" class="adc-subtitle">
								{{ choice.subheading }}
							</span>
							<div class="vx-addon-price">
								{{ addons.getPriceForChoice(addon, choice) }}
							</div>
						</div>
						<div v-if="value.selected.item === choice.value && choice.quantity.enabled && choice.value" class="ts-stepper-input flexify custom-addon-stepper" @click.stop>
							<button class="ts-stepper-left ts-icon-btn ts-smaller" @click.prevent="decrementQuantity(choice)"
								:class="{'vx-disabled': choice.quantity.min >= value.selected.quantity}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
							</button>
							<input v-model="value.selected.quantity" type="number" class="ts-input-box" @change="validateQuantity(choice)">
							<button class="ts-stepper-right ts-icon-btn ts-smaller" @click.prevent="incrementQuantity(choice)"
								:class="{'vx-disabled': choice.quantity.max <= value.selected.quantity}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
							</button>
						</div>
					</li>
				</template>
			</ul>
		</div>
	</template>
	<template v-else-if="addon.props.display_mode === 'dropdown' && shouldShowAddon()">
		<div class="ts-form-group">
			<label>{{ addon.label }}</label>
			<div class="ts-filter">
				<select @change="toggleChoice(addon.props.choices[$event.target.value])">
					<option v-if="!addon.required" :value="null"><?= _x( 'Select choice', 'product form dropdown', 'voxel' ) ?></option>
					<template v-for="choice in addon.props.choices">
						<option v-if="shouldShowChoice(choice)" :value="choice.value">{{ choice.label }}</option>
					</template>
				</select>
				<div class="ts-down-icon"></div>
			</div>
		</div>
	</template>
</script>
