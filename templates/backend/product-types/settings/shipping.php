<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<div class="ts-group">
	<div class="ts-group-head">
		<h3>Shipping classes</h3>
		<div class="vx-info-box">
			<?php \Voxel\svg( 'info.svg' ) ?>
			<p>Shipping classes are globally defined. They are used both for platform and vendor sold products</p>
		</div>
	</div>
	<div class="x-row">
		<div class="x-col-12 field-container ts-drag-animation">
			<template v-if="config.shipping.shipping_classes.length">
				<draggable
					v-model="config.shipping.shipping_classes"
					group="shipping_classes"
					handle=".field-head"
					item-key="key"
				>
					<template #item="{element: shipping_class, index: index}">
						<div class="single-field wide" :class="{open: state.activeShippingClass === shipping_class}">
							<div class="field-head" @click="state.activeShippingClass = state.activeShippingClass === shipping_class ? null : shipping_class">
								<p class="field-name">{{ shipping_class.label || '(untitled)' }}</p>
								<p class="field-type">
									<span style="display: none;">{{ shipping_class.key }}</span>
								</p>
								<div class="field-actions">
									<span class="field-action all-center">
										<a href="#" @click.prevent="config.shipping.shipping_classes.splice(index, 1)">
											<i class="lar la-trash-alt icon-sm"></i>
										</a>
									</span>
								</div>
							</div>
							<div v-if="state.activeShippingClass === shipping_class" class="field-body">
								<div class="x-row">
									<?php \Voxel\Utils\Form_Models\Text_Model::render( [
										'v-model' => 'shipping_class.label',
										'label' => 'Label',
										'classes' => 'x-col-6',
									] ) ?>

									<?php \Voxel\Utils\Form_Models\Text_Model::render( [
										'v-model' => 'shipping_class.description',
										'label' => 'Description',
										'classes' => 'x-col-6',
									] ) ?>
								</div>
							</div>
						</div>
					</template>
				</draggable>
			</template>
			<div v-else class="ts-form-group">
				<p>You have not added any shipping classes yet.</p>
			</div>

		</div>

		<div class="x-col-12">
			<div class="add-field">
				<div
					class="ts-button ts-outline"
					@click.prevent="config.shipping.shipping_classes.push( state.activeShippingClass = {
						key: $w.Voxel_Backend.helpers.randomId(8),
						label: null,
						description: null,
					} )"
				>
					<p class="field-name">Add shipping class</p>
				</div>
			</div>
		</div>

	</div>
</div>

<div class="ts-group">
	<div class="ts-group-head">
		<h3>Shipping zones (Platform)</h3>
		<div class="vx-info-box">
			<?php \Voxel\svg( 'info.svg' ) ?>
			<p>Shipping zones used for products sold by the platform.</p>
		</div>
	</div>
	<div class="x-row">
		<div class="x-col-12 field-container ts-drag-animation">
			<template v-if="config.shipping.shipping_zones.length">
				<draggable
					v-model="config.shipping.shipping_zones"
					group="shipping_zones"
					handle=".field-head"
					item-key="key"
				>
					<template #item="{element: zone, index: index}">
						<div class="single-field wide" :class="{open: state.activeShippingZone === zone}">
							<div class="field-head" @click="state.activeShippingZone = state.activeShippingZone === zone ? null : zone">
								<p class="field-name">{{ zone.label || '(untitled)' }}</p>
								<p class="field-type">
									<template v-if="zone.rates.length > 1">{{ zone.rates.length }} shipping rates</template>
									<template v-else-if="zone.rates.length === 1">{{ zone.rates.length }} shipping rate</template>
									<template v-else>No shipping rates</template>
									<span style="display: none;">{{ zone.key }}</span>
								</p>
								<div class="field-actions">
									<span class="field-action all-center">
										<a href="#" @click.prevent="config.shipping.shipping_zones.splice(index, 1)">
											<i class="lar la-trash-alt icon-sm"></i>
										</a>
									</span>
								</div>
							</div>
							<div v-if="state.activeShippingZone === zone" class="field-body">
								<div class="x-row">
									<?php \Voxel\Utils\Form_Models\Text_Model::render( [
										'v-model' => 'zone.label',
										'label' => 'Label',
										'classes' => 'x-col-12',
									] ) ?>

									<div class="ts-form-group x-col-12">
										<label>
											Countries
											<a  href="#" @click.prevent="zone.regions = Object.keys( props.shipping_countries ).map( country_code => { return { type: 'country', country: country_code }; } )">Select all</a>
													/
													<a href="#" @click.prevent="zone.regions = []">Unselect all</a>
										</label>



										<div class="x-row">
											<div class="ts-form-group x-col-12">
												<select @change="zone.regions.push( {
													type: 'country',
													country: $event.target.value,
												} );
												$event.target.value = '';
												zone.regions.sort( (a, b) => props.shipping_countries[a.country] < props.shipping_countries[b.country] ? -1 : ( props.shipping_countries[a.country] > props.shipping_countries[b.country] ? 1 : 0 ) );">
													<option value="">Add country</option>
													<template v-for="country_name, country_code in props.shipping_countries">
														<option :disabled="zone.regions.find( region => region.country === country_code )" :value="country_code">{{ country_name }}</option>
													</template>
												</select>

											</div>
											<div class="ts-form-group x-col-12 selected-cn">
												<div v-if="zone.regions.length"  style="gap: 5px; display: flex; flex-wrap: wrap;">
													<template v-for="region, regionIndex in zone.regions">
														<div class="ts-button ts-outline shipping-cn" >
															<!-- <div class="field-head" @mousedown.stop.prevent> -->
																<p>
																	{{ props.shipping_countries[region.country] || region.country }}
																</p>
																<!-- <p class="field-type">
																	{{ region.country }}
																</p> -->

																<a class="vx-button-delete" href="#" @click.stop.prevent="zone.regions.splice(regionIndex, 1)">
																	<?php \Voxel\svg( 'close.svg' ) ?>
																</a>

															<!-- </div> -->
														</div>
													</template>
												</div>
											</div>

										</div>
									</div>

									<div class="ts-form-group x-col-12">
										<label>Shipping rates</label>

										<draggable
											v-model="zone.rates"
											group="zone_shipping_rates"
											handle=".field-head"
											item-key="key"
										>
											<template #item="{element: shipping_rate, index: rateIndex}">
												<div class="single-field wide" :class="{open: state.activeShippingRate === shipping_rate}">
													<div class="field-head" @click="state.activeShippingRate = state.activeShippingRate === shipping_rate ? null : shipping_rate">
														<p class="field-name" style="color: #fff;">{{ shipping_rate.label || '(untitled)' }}</p>
														<p class="field-type">
															<span>{{ shipping_rate.type }}</span>
															<span style="display: none;">{{ shipping_rate.key }}</span>
														</p>
														<div class="field-actions">
															<span class="field-action all-center">
																<a href="#" @click.prevent="zone.rates.splice(rateIndex, 1)">
																	<i class="lar la-trash-alt icon-sm"></i>
																</a>
															</span>
														</div>
													</div>
													<div v-if="state.activeShippingRate === shipping_rate" class="field-body">
														<div class="x-row">
															<?php \Voxel\Utils\Form_Models\Text_Model::render( [
																'v-model' => 'shipping_rate.label',
																'label' => 'Label',
																'classes' => 'x-col-8',
															] ) ?>

															<?php \Voxel\Utils\Form_Models\Select_Model::render( [
																'v-model' => 'shipping_rate.type',
																'label' => 'Type',
																'classes' => 'x-col-4',
																'choices' => [
																	'free_shipping' => 'Free shipping',
																	'fixed_rate' => 'Fixed rate',
																],
															] ) ?>

															<template v-if="shipping_rate.type === 'free_shipping'">
																<?php \Voxel\Utils\Form_Models\Select_Model::render( [
																	'v-model' => 'shipping_rate.free_shipping.requirements',
																	'label' => 'Free shipping requires',
																	'classes' => 'x-col-12',
																	'choices' => [
																		'none' => 'No requirement',
																		'minimum_order_amount' => 'Minimum order amount',
																	],
																] ) ?>

																<template v-if="shipping_rate.free_shipping.requirements === 'minimum_order_amount'">
																	<?php \Voxel\Utils\Form_Models\Number_Model::render( [
																		'v-model' => 'shipping_rate.free_shipping.minimum_order_amount',
																		'label' => 'Min. order amount',
																		'classes' => 'x-col-12',
																		'step' => 'any',
																	] ) ?>
																</template>

																<div class="ts-form-group x-col-12" style="padding-bottom: 0;">
																	<label style="padding-bottom: 0;"><strong>Estimated shipping time (optional)</strong></label>
																</div>
																<?php \Voxel\Utils\Form_Models\Number_Model::render( [
																	'v-model' => 'shipping_rate.free_shipping.delivery_estimate.minimum.value',
																	'label' => 'Between',
																	'classes' => 'x-col-2',
																] ) ?>
																<?php \Voxel\Utils\Form_Models\Select_Model::render( [
																	'v-model' => 'shipping_rate.free_shipping.delivery_estimate.minimum.unit',
																	'label' => '&nbsp;',
																	'classes' => 'x-col-4',
																	'choices' => [
																		'hour' => 'Hour(s)',
																		'day' => 'Day(s)',
																		'business_day' => 'Business day(s)',
																		'week' => 'Week(s)',
																		'month' => 'Month(s)',
																	],
																] ) ?>
																<?php \Voxel\Utils\Form_Models\Number_Model::render( [
																	'v-model' => 'shipping_rate.free_shipping.delivery_estimate.maximum.value',
																	'label' => 'And',
																	'classes' => 'x-col-2',
																] ) ?>
																<?php \Voxel\Utils\Form_Models\Select_Model::render( [
																	'v-model' => 'shipping_rate.free_shipping.delivery_estimate.maximum.unit',
																	'label' => '&nbsp;',
																	'classes' => 'x-col-4',
																	'choices' => [
																		'hour' => 'Hour(s)',
																		'day' => 'Day(s)',
																		'business_day' => 'Business day(s)',
																		'week' => 'Week(s)',
																		'month' => 'Month(s)',
																	],
																] ) ?>
															</template>
															<template v-else-if="shipping_rate.type === 'fixed_rate'">
																<?php \Voxel\Utils\Form_Models\Select_Model::render( [
																	'v-model' => 'shipping_rate.fixed_rate.tax_code',
																	'label' => 'Shipping tax code',
																	'classes' => 'x-col-6',
																	'choices' => [
																		'nontaxable' => 'Nontaxable', // txcd_00000000
																		'shipping' => 'Shipping', // txcd_92010001
																	],
																] ) ?>

																<?php \Voxel\Utils\Form_Models\Select_Model::render( [
																	'v-model' => 'shipping_rate.fixed_rate.tax_behavior',
																	'label' => sprintf( 'Tax behavior <a style="float:right;" href="%s" target="_blank">Set default tax behavior</a>', esc_url( \Voxel\Modules\Stripe_Payments\Stripe_Client::dashboard_url( '/settings/tax' ) ) ),
																	'classes' => 'x-col-6',
																	'choices' => [
																		'default' => 'Default: Use default tax behavior configured in your Stripe dashboard',
																		'inclusive' => 'Inclusive: Tax is included in the price',
																		'exclusive' => 'Exclusive: Tax is added on top of the price',
																	],
																] ) ?>

																<div class="ts-form-group x-col-12" style="padding-bottom: 0;">
																	<label style="padding-bottom: 0;"><strong>Estimated shipping time (optional)</strong></label>
																</div>
																<?php \Voxel\Utils\Form_Models\Number_Model::render( [
																	'v-model' => 'shipping_rate.fixed_rate.delivery_estimate.minimum.value',
																	'label' => 'Between',
																	'classes' => 'x-col-2',
																] ) ?>
																<?php \Voxel\Utils\Form_Models\Select_Model::render( [
																	'v-model' => 'shipping_rate.fixed_rate.delivery_estimate.minimum.unit',
																	'label' => '&nbsp;',
																	'classes' => 'x-col-4',
																	'choices' => [
																		'hour' => 'Hour(s)',
																		'day' => 'Day(s)',
																		'business_day' => 'Business day(s)',
																		'week' => 'Week(s)',
																		'month' => 'Month(s)',
																	],
																] ) ?>
																<?php \Voxel\Utils\Form_Models\Number_Model::render( [
																	'v-model' => 'shipping_rate.fixed_rate.delivery_estimate.maximum.value',
																	'label' => 'And',
																	'classes' => 'x-col-2',
																] ) ?>
																<?php \Voxel\Utils\Form_Models\Select_Model::render( [
																	'v-model' => 'shipping_rate.fixed_rate.delivery_estimate.maximum.unit',
																	'label' => '&nbsp;',
																	'classes' => 'x-col-4',
																	'choices' => [
																		'hour' => 'Hour(s)',
																		'day' => 'Day(s)',
																		'business_day' => 'Business day(s)',
																		'week' => 'Week(s)',
																		'month' => 'Month(s)',
																	],
																] ) ?>

																<div class="ts-form-group x-col-12" style="padding-bottom: 0;">
																	<label style="padding-bottom: 0;"><strong>Pricing</strong></label>
																</div>
																<?php \Voxel\Utils\Form_Models\Number_Model::render( [
																	'v-model' => 'shipping_rate.fixed_rate.amount_per_unit',
																	'label' => 'Amount per unit (default)',
																	'classes' => 'x-col-12',
																	'step' => 'any',
																] ) ?>
																<div class="ts-form-group x-col-12" v-if="config.shipping.shipping_classes.length">
																	<label>Amount per unit (by shipping class)</label>

																	<div v-if="shipping_rate.fixed_rate.shipping_classes.length" class="x-row">
																		<template v-for="shipping_class, shipping_class_index in shipping_rate.fixed_rate.shipping_classes">
																			<div v-if="config.shipping.shipping_classes.find( cls => cls.key === shipping_class.shipping_class )" class="ts-form-group x-col-6">
																				<label>
																					{{ config.shipping.shipping_classes.find( cls => cls.key === shipping_class.shipping_class ).label }}

																					<a style="float:right;" href="#" @click.prevent="shipping_rate.fixed_rate.shipping_classes.splice(shipping_class_index, 1)">Remove</a>
																				</label>
																				<input v-model="shipping_class.amount_per_unit" type="number" step="any">
																			</div>
																		</template>
																	</div>

																	<div class="ts-form-group mt10">
																		<div class="add-field">
																			<template v-for="shipping_class in config.shipping.shipping_classes">
																				<div
																					v-if="!shipping_rate.fixed_rate.shipping_classes.find( cls => cls.shipping_class === shipping_class.key )"
																					class="ts-button ts-outline"
																					@click.prevent="shipping_rate.fixed_rate.shipping_classes.push( {
																						shipping_class: shipping_class.key,
																						amount_per_unit: null,
																					} )"
																				>
																					{{ shipping_class.label }}
																				</div>
																			</template>
																		</div>
																	</div>
																</div>
															</template>
														</div>
													</div>
												</div>
											</template>
										</draggable>

										<div class="ts-form-group mt10">
											<div class="add-field">
												<div
													class="ts-button ts-outline"
													@click.prevent="zone.rates.push( state.activeShippingRate = {
														key: $w.Voxel_Backend.helpers.randomId(8),
														label: null,
														type: 'free_shipping',
														free_shipping: {
															requirements: 'none',
															minimum_order_amount: 0,
															delivery_estimate: {
																minimum: {
																	unit: 'business_day',
																	value: 1,
																},
																maximum: {
																	unit: 'business_day',
																	value: 2,
																},
															},
														},
														fixed_rate: {
															tax_behavior: 'default',
															tax_code: 'shipping',
															delivery_estimate: {
																minimum: {
																	unit: 'business_day',
																	value: 1,
																},
																maximum: {
																	unit: 'business_day',
																	value: 2,
																},
															},
															amount_per_unit: 0,
															shipping_classes: [],
														},
													} )"
												>
													Add shipping rate
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</template>
				</draggable>
			</template>
			<div v-else class="ts-form-group">
				<p>You have not added any shipping zones yet.</p>
			</div>
		</div>

		<div class="x-col-12">
			<div class="add-field">
				<div
					class="ts-button ts-outline"
					@click.prevent="config.shipping.shipping_zones.push( state.activeShippingZone = {
						key: $w.Voxel_Backend.helpers.randomId(8),
						label: null,
						regions: [],
						rates: [],
					} )"
				>
					<p class="field-name">Add shipping zone</p>
				</div>
			</div>
		</div>

	</div>
</div>
