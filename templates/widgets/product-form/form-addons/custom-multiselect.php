<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-custom-multiselect">
	<template v-if="addon.props.display_mode === 'checkboxes' && shouldShowAddon()">
		<div class="ts-form-group ts-custom-additions">
			<label>{{ addon.label }}</label>
			<ul class="simplify-ul ts-addition-list flexify">
				<template v-for="choice in addon.props.choices">
					<li v-if="shouldShowChoice(choice)" class="flexify" :class="{ 'ts-checked': isChecked(choice) }">
						<div class="addition-body" @click.prevent="toggleChoice(choice)">
							<label class="container-checkbox">
								<input type="checkbox" :checked="isChecked(choice)" class="onoffswitch-checkbox" disabled hidden>
								<span class="checkmark"></span>
							</label>
							<span>{{ choice.label }}</span>
							<div class="vx-addon-price">
								{{ addons.getPriceForChoice(addon, choice) }}
							</div>
						</div>
						<div v-if="isChecked(choice) && choice.quantity.enabled && choice.value" class="ts-stepper-input flexify custom-addon-stepper">
							<button class="ts-stepper-left ts-icon-btn ts-smaller" @click.prevent="decrementQuantity(choice)"
								:class="{'vx-disabled': choice.quantity.min >= value.selected[ getSelectionIndex(choice) ].quantity}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
							</button>
							<input v-model="value.selected[ getSelectionIndex(choice) ].quantity" type="number" class="ts-input-box" @change="validateQuantity(choice)">
							<button class="ts-stepper-right ts-icon-btn ts-smaller" @click.prevent="incrementQuantity(choice)"
								:class="{'vx-disabled': choice.quantity.max <= value.selected[ getSelectionIndex(choice) ].quantity}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
							</button>
						</div>
					</li>
				</template>
			</ul>
		</div>
	</template>
	<template v-if="addon.props.display_mode === 'buttons' && shouldShowAddon()">
		<div class="ts-form-group">
			<label>{{ addon.label }}</label>
			<ul class="simplify-ul addon-buttons flexify">
				<template v-for="choice in addon.props.choices">
					<li v-if="shouldShowChoice(choice)" class="flexify" :class="{'adb-selected':isChecked(choice)}" @click.prevent="toggleChoice(choice)">
						{{ choice.label }}
					</li>
				</template>
			</ul>
		</div>
	</template>
	<template v-if="addon.props.display_mode === 'cards' && shouldShowAddon()">
		<div class="ts-form-group">
			<label>{{ addon.label }}</label>
			<ul class="simplify-ul addon-cards flexify">
				<template v-for="choice in addon.props.choices">
					<li v-if="shouldShowChoice(choice)" class="flexify" :class="{'adc-selected':isChecked(choice)}" @click.prevent="toggleChoice(choice)">
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
						<div v-if="isChecked(choice) && choice.quantity.enabled && choice.value" class="ts-stepper-input flexify custom-addon-stepper" @click.stop>
							<button class="ts-stepper-left ts-icon-btn ts-smaller" @click.prevent="decrementQuantity(choice)"
								:class="{'vx-disabled': choice.quantity.min >= value.selected[ getSelectionIndex(choice) ].quantity}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
							</button>
							<input v-model="value.selected[ getSelectionIndex(choice) ].quantity" type="number" class="ts-input-box" @change="validateQuantity(choice)">
							<button class="ts-stepper-right ts-icon-btn ts-smaller" @click.prevent="incrementQuantity(choice)"
								:class="{'vx-disabled': choice.quantity.max <= value.selected[ getSelectionIndex(choice) ].quantity}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
							</button>
						</div>
					</li>
				</template>
			</ul>
		</div>
	</template>
</script>
