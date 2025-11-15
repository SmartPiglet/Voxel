<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-form-variations">
	<template v-for="attribute in field.props.attributes">
		<variation-attribute
			:attribute="attribute"
			:variations="this"
			:ref="'attribute:'+attribute.key"
		></variation-attribute>
	</template>

	<template v-if="field.props.stock.enabled && currentVariation.config.stock.enabled && !currentVariation.config.stock.sold_individually">
		<div class="ts-form-group">
			<label><?= _x( 'Select quantity', 'product form quantity', 'voxel' ) ?></label>
			<div class="ts-stepper-input flexify">
				<button class="ts-stepper-left ts-icon-btn ts-smaller" @click.prevent="decrementQuantity"
					:class="{'vx-disabled': value.quantity <= 1}">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
				</button>
				<input v-model="value.quantity" type="number" class="ts-input-box" @change="validateQuantityInBounds">
				<button class="ts-stepper-right ts-icon-btn ts-smaller" @click.prevent="incrementQuantity"
					:class="{'vx-disabled': currentVariation.config.stock.quantity <= value.quantity}">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
				</button>
			</div>
		</div>
	</template>
</script>

<script type="text/html" id="product-form-attribute">
	<template v-if="attribute.props.display_mode === 'buttons'">
		<div class="ts-form-group">
			<label>{{ attribute.label }}: {{ selectionLabel }}</label>
			<ul class="simplify-ul addon-buttons flexify">
				<template v-for="choice in getActiveChoices()">
					<li
						class="flexify"
						:class="{ 'adb-selected': isSelected(choice), 'vx-disabled': getChoiceStatus(choice) !== 'active' }"
						@click.prevent="selectChoice(choice)"
					>
						{{ choice.label }}
					</li>
				</template>
			</ul>
		</div>
	</template>
	<template v-else-if="attribute.props.display_mode === 'radio'">
		<div class="ts-form-group ts-custom-additions">
			<label>{{ attribute.label }}: {{ selectionLabel }}</label>
			<ul class="simplify-ul ts-addition-list flexify">
				<li v-for="choice in getActiveChoices()" class="flexify" :class="{ 'ts-checked': isSelected(choice), 'vx-disabled': getChoiceStatus(choice) !== 'active' }">
					<div class="addition-body" @click.prevent="selectChoice(choice)">
						<label class="container-radio">
							<input type="radio" :checked="isSelected(choice)" class="onoffswitch-checkbox" disabled hidden>
							<span class="checkmark"></span>
						</label>
						<span>{{ choice.label }}</span>
					</div>
				</li>
			</ul>
		</div>
	</template>
	<template v-else-if="attribute.props.display_mode === 'dropdown'">
		<div class="ts-form-group">
			<label>{{ attribute.label }}: {{ selectionLabel }}</label>
			<div class="ts-filter">
				<select @change="selectChoice(attribute.props.choices[$event.target.value])">
					<option v-for="choice in getActiveChoices()" :value="'choice_'+choice.value" :disabled="getChoiceStatus(choice) !== 'active'">
						{{ choice.label }}
					</option>
				</select>
				<div class="ts-down-icon"></div>
			</div>
		</div>
	</template>
	<template v-else-if="attribute.props.display_mode === 'cards'">
		<div class="ts-form-group">
			<label>{{ attribute.label }}: {{ selectionLabel }}</label>
			<ul class="simplify-ul addon-cards flexify">
				<template v-for="choice in getActiveChoices()">
					<li class="flexify" :class="{ 'adc-selected': isSelected(choice), 'vx-disabled': getChoiceStatus(choice) !== 'active' }"
						@click.prevent="selectChoice(choice)">
						<img v-if="choice.image !== null" :src="choice.image.url" :title="choice.image.alt" :alt="choice.image.alt">
						<div class="addon-details">
							<span class="adc-title">
								{{ choice.label }}
							</span>
							<span v-if="choice.subheading !== null && choice.subheading.length" class="adc-subtitle">
								{{ choice.subheading }}
							</span>
						</div>
					</li>
				</template>
			</ul>
		</div>
	</template>
	<template v-else-if="attribute.props.display_mode === 'colors'">
		<div class="ts-form-group">
			<label>{{ attribute.label }}: {{ selectionLabel }}</label>
			<ul class="simplify-ul addon-colors flexify">
				<template v-for="choice in getActiveChoices()">
					<li class="flexify" :class="{ 'color-selected': isSelected(choice), 'vx-disabled': getChoiceStatus(choice) !== 'active' }"
						:style="{'--vx-var-color': choice.color}" @click.prevent="selectChoice(choice)"></li>
				</template>
			</ul>
		</div>
	</template>
	<template v-else-if="attribute.props.display_mode === 'images'">
		<div class="ts-form-group">
			<label>{{ attribute.label }}: {{ selectionLabel }}</label>
			<ul class="simplify-ul addon-images flexify">
				<template v-for="choice in getActiveChoices()">
					<li class="flexify" :class="{ 'adm-selected': isSelected(choice), 'vx-disabled': getChoiceStatus(choice) !== 'active' }"
						@click.prevent="selectChoice(choice)">
						<img v-if="choice.image !== null" :src="choice.image.url" :title="choice.image.alt" :alt="choice.image.alt">
					</li>
				</template>
			</ul>
		</div>
	</template>
</script>
