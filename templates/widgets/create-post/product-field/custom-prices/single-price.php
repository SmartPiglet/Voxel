<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-custom-prices-single">

	<div class="ts-form-group ts-pricing-label vx-1-1">
		<label><?= _x( 'Set name', 'product field custom prices', 'voxel' ) ?></label>
			<div class="input-container">
			<input v-model="pricing.label" type="text" class="ts-filter" placeholder="<?= esc_attr( _x( 'Name your custom price', 'product field custom prices', 'voxel' ) ) ?>">
		</div>
	</div>
	<div class="ts-form-group">
		<div class="form-field-grid medium">
			<div class="ts-form-group vx-1-1 ui-heading-field">
				<label><?= _x( 'Conditions', 'product field custom prices', 'voxel' ) ?></label>
			</div>
			<template v-for="condition, index in pricing.conditions">
				<div class="ts-form-group vx-1-3">
					<div class="ts-filter">
						<select v-model="condition.type">
							<option value="day_of_week"><?= _x( 'Week day is', 'product field custom prices', 'voxel' ) ?></option>
							<option value="date"><?= _x( 'Date is', 'product field custom prices', 'voxel' ) ?></option>
							<option value="date_range"><?= _x( 'Date range is', 'product field custom prices', 'voxel' ) ?></option>
						</select>
						<div class="ts-down-icon"></div>
					</div>
				</div>
				<template v-if="condition.type === 'day_of_week'">
					<form-group
						:popup-key="conditionKey(index, 'days_of_week')"
						:ref="conditionKey(index, 'days_of_week')"
						class="ts-form-group vx-1-3"
						@save="$refs[conditionKey(index, 'days_of_week')][0].blur()"
						@clear="condition.days = []"
					>
						<template #trigger>
							<div class="ts-filter ts-popup-target" @mousedown="$root.activePopup = conditionKey(index, 'days_of_week')" :class="{'ts-filled': condition.days.length}">
								<div class="ts-filter-text">
									{{ conditionLabel(condition) || <?= wp_json_encode( _x( 'Select day(s)', 'product field', 'voxel' ) ) ?> }}
								</div>
								 <div class="ts-down-icon"></div>
							</div>
						</template>
						<template #popup>
							<div class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
								<ul class="simplify-ul ts-term-dropdown-list min-scroll">
									<li v-for="day_label, day_key in customPrices.field.props.weekdays">
										<a href="#" class="flexify" @click.prevent="toggleDay( day_key, condition )">
											<div class="ts-checkbox-container">
												<label class="container-checkbox">
													<input type="checkbox" :value="day_key" :checked="condition.days.indexOf(day_key) !== -1" disabled hidden>
													<span class="checkmark"></span>
												</label>
											</div>
											<span>{{ day_label }}</span>
										</a>
									</li>
								</ul>
							</div>
						</template>
					</form-group>
				</template>
				<template v-else-if="condition.type === 'date'">
					<form-group
						:popup-key="conditionKey(index, 'date')"
						:ref="conditionKey(index, 'date')"
						class="ts-form-group vx-1-3"
						@save="$refs[conditionKey(index, 'date')][0].blur()"
						@clear="condition.date = null"
						wrapper-class="lg-width md-height"
					>
						<template #trigger>
							<div class="ts-filter ts-popup-target" @mousedown="$root.activePopup = conditionKey(index, 'date')" :class="{'ts-filled': condition.date?.length}">
								<div class="ts-filter-text">
									{{ conditionLabel(condition) || <?= wp_json_encode( _x( 'Select date', 'product field', 'voxel' ) ) ?> }}
								</div>
								 <div class="ts-down-icon"></div>
							</div>
						</template>
						<template #popup>
							<div class="ts-form-group">
								<single-date-input
									v-model="condition.date"
									:min-date="new Date(Date.now() - 24 * 60 * 60 * 1000)"
									@update:modelValue="$refs[conditionKey(index, 'date')][0].blur()"
								></single-date-input>
							</div>
						</template>
					</form-group>
				</template>
				<template v-else-if="condition.type === 'date_range'">
					<form-group
						:popup-key="conditionKey(index, 'date')"
						:ref="conditionKey(index, 'date')"
						class="ts-form-group vx-1-3"
						@save="$refs[conditionKey(index, 'date')][0].blur()"
						@clear="condition.range.from = null; condition.range.to = null; $refs[conditionKey(index, 'range')][0].value.start = null; $refs[conditionKey(index, 'range')][0].value.end = null;"
						wrapper-class="xl-height xl-width"
					>
						<template #trigger>
							<div class="ts-filter ts-popup-target" @mousedown="$root.activePopup = conditionKey(index, 'date')" :class="{'ts-filled': condition.range.from && condition.range.to}">
								<div class="ts-filter-text">
									{{ conditionLabel(condition) || <?= wp_json_encode( _x( 'Select dates', 'product field', 'voxel' ) ) ?> }}
								</div>
								 <div class="ts-down-icon"></div>
							</div>
						</template>
						<template #popup>
							<div class="ts-form-group">
								<date-range-input
									:ref="conditionKey(index, 'range')"
									:start-date="condition.range.from ? new Date(condition.range.from+'T00:00:00') : null"
									:end-date="condition.range.from ? new Date(condition.range.to+'T00:00:00') : null"
									:min-date="new Date(Date.now() - 24 * 60 * 60 * 1000)"
									@update:modelValue="condition.range.from = $event.start; condition.range.to = $event.end;"
								></date-range-input>
							</div>
						</template>
					</form-group>
				</template>
				<div class="ts-form-group vx-1-3 vx-center-right">
					<a href="#" class="ts-btn ts-btn-1 form-btn" @click.prevent="deleteCondition(condition)">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
						<?= _x( 'Remove', 'product field custom prices', 'voxel' ) ?>
					</a>
				</div>
			</template>
			<div class="ts-form-group">
				<a href="#" v-if="pricing.conditions.length < customPrices.field.props.limits.custom_price_conditions" class="ts-btn ts-btn-4 form-btn" @click.prevent="createCondition">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
					<?= _x( 'Add condition', 'product field custom prices', 'voxel' ) ?>
				</a>
			</div>
			<div class="ts-form-group vx-1-1">
				<div class="form-field-grid medium custom-price-table">
					<div class="ts-form-group vx-1-1 ui-heading-field">
						<label><?= _x( 'Set prices', 'product field custom prices', 'voxel' ) ?></label>
					</div>
					<template v-if="customPrices.field.props.base_price.enabled">
						<div class="ts-form-group vx-1-2">
							<label><?= _x( 'Price', 'product field custom prices', 'voxel' ) ?></label>
							<div class="input-container">
								<input v-model="pricing.prices.base_price.amount" type="number" class="ts-filter" min="0" placeholder="<?= esc_attr( _x( 'Add price', 'product field custom prices', 'voxel' ) ) ?>">
								<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
							</div>
						</div>
						<div v-if="customPrices.field.props.base_price.discount_price.enabled" class="ts-form-group vx-1-2">
							<label><?= _x( 'Discount price', 'product field custom prices', 'voxel' ) ?></label>
							<div class="input-container">
								<input v-model="pricing.prices.base_price.discount_amount" type="number" class="ts-filter" min="0" placeholder="<?= esc_attr( _x( 'Add price', 'product field custom prices', 'voxel' ) ) ?>">
								<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
							</div>
						</div>
					</template>

					<template v-if="customPrices.field.props.addons.enabled">
						<template v-for="addon in productType.fields.addons.props.addons">
							<template v-if="isAddonActive(addon.key) && ( addon.type === 'numeric' || addon.type === 'switcher' )">
								<div class="ts-form-group vx-1-2">
									<label>{{ addon.label }}</label>
									<div class="input-container">
										<input v-model="pricing.prices.addons[addon.key].price" type="number" class="ts-filter" min="0" placeholder="<?= esc_attr( _x( 'Add price', 'product field custom prices', 'voxel' ) ) ?>">
										<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
									</div>
								</div>
							</template>
						</template>

						<template v-for="addon in productType.fields.addons.props.addons">
							<template v-if="isAddonActive(addon.key)">
								<template v-if="addon.type === 'custom-select' || addon.type === 'custom-multiselect'">
									<div class="ts-form-group vx-1-1">
										<div class="form-field-grid medium">
											<div class="ts-form-group ui-heading-field">
												<label>{{ addon.label }}</label>
											</div>
											<template v-for="choice in getAddonRef(addon.key).list">
												<div v-if="isChoiceActive(choice, addon.key)" class="ts-form-group vx-1-2">
													<label>{{ choice.value }}</label>
													<div class="input-container">
														<input v-model="pricing.prices.addons[addon.key][choice.value].price" type="number" class="ts-filter" min="0" placeholder="<?= esc_attr( _x( 'Add price', 'product field custom prices', 'voxel' ) ) ?>">
														<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
													</div>
												</div>
											</template>
										</div>
									</div>
								</template>
								<template v-else-if="addon.type === 'select' || addon.type === 'multiselect'">
									<div class="ts-form-group vx-1-1">
										<div class="form-field-grid medium custom-price-table">
											<div class="ts-form-group ui-heading-field">
												<label>{{ addon.label }}</label>
											</div>
											<template v-for="choice in addon.props.choices">
												<div v-if="isChoiceActive(choice, addon.key)" class="ts-form-group vx-1-2">
													<label>{{ choice.label }}</label>
													<div class="input-container">
														<input v-model="pricing.prices.addons[addon.key][choice.value].price" type="number" class="ts-filter" min="0" placeholder="<?= esc_attr( _x( 'Add price', 'product field custom prices', 'voxel' ) ) ?>">
														<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
													</div>
												</div>
											</template>
										</div>
									</div>
								</template>
							</template>
						</template>
					</template>
				</div>
			</div>
		</div>
	</div>
</script>
