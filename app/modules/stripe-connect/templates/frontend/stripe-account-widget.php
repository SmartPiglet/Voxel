<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script class="vxconfig" type="text/json"><?= wp_specialchars_decode( wp_json_encode( $config ) ) ?></script>
<div class="ts-vendor-settings hidden">
	<div v-if="screen === 'main'" class="ts-panel">
		<div class="ac-head">
		   <?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_connect_ico') ) ?: \Voxel\svg( 'stripe.svg' ) ?>
		   <b><?= _x( 'Stripe Connect', 'stripe vendor', 'voxel' ) ?></b>
		</div>
		<div class="ac-body">
			<?php if ( \Voxel\current_user()->has_cap('administrator') && apply_filters( 'voxel/stripe_connect/enable_onboarding_for_admins', false ) !== true ) : ?>
				<p><?= _x( 'Stripe vendor onboarding is disabled for admin accounts.', 'stripe vendor', 'voxel' ) ?></p>
			<?php else: ?>
				<?php if ( $account->charges_enabled ): ?>
					<p><?= _x( 'Your account is ready to accept payments.', 'stripe vendor', 'voxel' ) ?></p>
				<?php elseif ( $account->details_submitted ): ?>
					<p><?= _x( 'Your account is pending verification.', 'stripe vendor', 'voxel' ) ?></p>
				<?php else: ?>
					<p><?= _x( 'Setup your Stripe vendor account in order to accept payments.', 'stripe vendor', 'voxel' ) ?></p>
				<?php endif ?>
				<div class="ac-bottom">
					<ul class="simplify-ul current-plan-btn">
						<?php if ( ! $account->exists ): ?>
							<li>
								<a href="<?= esc_url( $onboard_link ) ?>" class="ts-btn ts-btn-1 ts-btn-large">
									 <?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_setup_ico') ) ?: \Voxel\svg( 'plus.svg' ) ?>
									<?= _x( 'Start setup', 'stripe vendor', 'voxel' ) ?>
								</a>

							</li>

						<?php elseif ( ! $account->details_submitted ): ?>
							<li>
								<a href="<?= esc_url( $onboard_link ) ?>" class="ts-btn ts-btn-1 ts-btn-large">
									 <?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_submit_ico') ) ?: \Voxel\svg( 'info.svg' ) ?>
									<?= _x( 'Complete onboarding', 'stripe vendor', 'voxel' ) ?>
								</a>
							</li>
							<?php if ( \Voxel\get( 'payments.stripe.stripe_connect.shipping.responsibility' ) === 'vendor' ): ?>
								<li>
									<a href="#" class="ts-btn ts-btn-1 ts-btn-large" @click.prevent="screen = 'shipping'">
										 <?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_shipping_ico') ) ?: \Voxel\svg( 'fast-delivery.svg' ) ?>
										<?= _x( 'Configure Shipping', 'stripe vendor', 'voxel' ) ?>
									</a>
								</li>
							<?php endif ?>
						<?php else: ?>

							<li>
								<a href="<?= esc_url( $dashboard_link ) ?>" target="_blank" class="ts-btn ts-btn-1 ts-btn-large">
									 <?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_stripe_ico') ) ?: \Voxel\svg( 'stripe.svg' ) ?>
									<?= _x( 'Vendor dashboard', 'stripe vendor', 'voxel' ) ?>
								</a>
							</li>
							<li>
								<a href="<?= esc_url( $onboard_link ) ?>" class="ts-btn ts-btn-1 ts-btn-large">
									 <?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_update_ico') ) ?: \Voxel\svg( 'menu.svg' ) ?>
									<?= _x( 'Update information', 'stripe vendor', 'voxel' ) ?>
								</a>
							</li>
							<?php if ( \Voxel\get( 'payments.stripe.stripe_connect.shipping.responsibility' ) === 'vendor' ): ?>
								<li>
									<a href="#" class="ts-btn ts-btn-1 ts-btn-large" @click.prevent="screen = 'shipping'">
										 <?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_shipping_ico') ) ?: \Voxel\svg( 'fast-delivery.svg' ) ?>
										<?= _x( 'Shipping', 'stripe vendor', 'voxel' ) ?>
									</a>
								</li>
							<?php endif ?>

						<?php endif ?>
					</ul>
				</div>
			<?php endif ?>
		</div>
	</div>

	<?php if ( \Voxel\get( 'payments.stripe.stripe_connect.shipping.responsibility' ) === 'vendor' ): ?>
		<div v-if="screen === 'shipping'" class="ts-vendor-shipping-zones" style="margin-top: 20px;">
			<div class="ac-body">
				<div class="ts-form">
					<div class="create-form-step form-field-grid">
						<div class="ts-form-group  vx-1-2">
							<a @click.prevent="screen = 'main'" href="#" class="ts-btn ts-btn-1 ts-btn-large">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_left') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?>
								<?= _x( 'Go back', 'stripe vendor shipping', 'voxel' ) ?>
							</a>
						</div>
						<div class="ts-form-group vx-1-2">
							<a @click.prevent="saveShipping" href="#" class="ts-btn ts-btn-2 ts-btn-large" :class="{'vx-disabled': savingShipping}">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('save_icon') ) ?: \Voxel\svg( 'floppy-disk.svg' ) ?>
								<?= _x( 'Save changes', 'stripe vendor shipping', 'voxel' ) ?>
							</a>
						</div>
						<div class="ts-form-group ui-heading-field field-key-ui-heading"><label><?= _x( 'Configure Shipping', 'stripe vendor', 'voxel' ) ?></label></div>
						<div class="ts-form-group">
							<label><?= _x( 'Shipping zones', 'stripe vendor', 'voxel' ) ?></label>
							<div v-if="config.zones.length" class="ts-repeater-container">
								<template v-for="zone, zoneIndex in config.zones">
									<div class="ts-field-repeater" :class="{collapsed: activeZone !== zone}">
										<div class="ts-repeater-head" @click.prevent="activeZone = activeZone === zone ? null : zone">
											<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_zone_ico') ) ?: \Voxel\svg( 'marker.svg' ) ?>
											<label>{{ zone.label || <?= wp_json_encode( _x( '(untitled)', 'stripe vendor shipping', 'voxel' ) ) ?> }}</label>
											<div class="ts-repeater-controller">
												<a href="#" @click.stop.prevent="config.zones.splice(zoneIndex,1)" class="ts-icon-btn ts-smaller">
													<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
												</a>
												<a href="#" class="ts-icon-btn ts-smaller" @click.prevent>
													<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
												</a>
											</div>
										</div>
										<div v-if="activeZone === zone" class="medium form-field-grid">
											<div class="ts-form-group vx-1-2">
												<label><?= _x( 'Label', 'stripe vendor shipping', 'voxel' ) ?></label>
												<input v-model="zone.label" type="text" class="ts-filter" maxlength="32">
											</div>

											<form-group :popup-key="'countries'" class="vx-1-2" :ref="'shipping:'+zone.key" @clear="zone.countries = []" @save="$refs['shipping:'+zone.key]?.[0]?.blur()">
												<template #trigger>
													<label>
														<?= _x( 'Countries', 'stripe vendor shipping', 'voxel' ) ?>
													</label>
													<div class="ts-filter ts-popup-target" :class="{'ts-filled': zone.countries.length}" @mousedown="$root.activePopup = 'countries'">
														<div class="ts-filter-text">
															<span>{{ getShippingCountriesLabel(zone) || <?= wp_json_encode( _x( 'Select one or more countries', 'stripe vendor shipping', 'voxel' ) ) ?> }}</span>
														</div>
													</div>
												</template>
												<template #popup>
													<div class="ts-sticky-top uib b-bottom">
														<div class="ts-input-icon flexify">
															<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
															<input v-model="zone._country_search" ref="searchInput" type="text" placeholder="<?= esc_attr( _x( 'Search countries', 'stripe vendor shipping', 'voxel' ) ) ?>" class="autofocus">
														</div>
													</div>
													<div class="ts-term-dropdown ts-md-group">
														<ul class="simplify-ul ts-term-dropdown-list min-scroll">
															<li v-if="!zone._country_search?.trim().length" @click.prevent="selectAllShippingCountries(zone)">
																<a href="#" class="flexify">
																	<div class="ts-checkbox-container">
																		<label class="container-checkbox">
																			<input type="checkbox" :checked="zone.countries.length === Object.keys( config.shipping_countries ).length" disabled hidden>
																			<span class="checkmark"></span>
																		</label>
																	</div>
																	<span>
																		<?= _x( 'All countries', 'stripe vendor shipping', 'voxel' ) ?>
																	</span>
																</a>
															</li>
															<li v-for="country_label, country_code in getShippingCountries(zone)">
																<a href="#" class="flexify" @click.prevent="toggleShippingCountry(zone, country_code)">
																	<div class="ts-checkbox-container">
																		<label class="container-checkbox">
																			<input type="checkbox" :value="country_code" :checked="isShippingCountrySelected(zone, country_code)" disabled hidden>
																			<span class="checkmark"></span>
																		</label>
																	</div>
																	<span>{{ country_label }}</span>
																</a>
															</li>
														</ul>
													</div>
												</template>

											</form-group>

											<div class="ts-form-group vx-1-1">
												<label><?= _x( 'Shipping rates', 'stripe vendor shipping', 'voxel' ) ?></label>

												<div class="ts-repeater-container">
													<template v-for="rate, rateIndex in zone.rates">
														<div class="ts-field-repeater" :class="{collapsed: zone._active_rate !== rate}">
															<div class="ts-repeater-head" @click.prevent="zone._active_rate = zone._active_rate === rate ? null : rate">
																<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_shipping_ico') ) ?: \Voxel\svg( 'fast-delivery.svg' ) ?>
																<label>{{ rate.label || <?= wp_json_encode( _x( '(untitled)', 'stripe vendor shipping', 'voxel' ) ) ?> }}</label>
																<div class="ts-repeater-controller">
																	<a href="#" @click.stop.prevent="zone.rates.splice(rateIndex,1)" class="ts-icon-btn ts-smaller">
																		<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
																	</a>
																	<a href="#" class="ts-icon-btn ts-smaller" @click.prevent>
																		<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
																	</a>
																</div>
															</div>
															<div v-if="zone._active_rate === rate" class="medium form-field-grid">
																<div class="ts-form-group vx-1-2">
																	<label><?= _x( 'Label', 'stripe vendor shipping', 'voxel' ) ?></label>
																	<input v-model="rate.label" type="text" class="ts-filter" maxlength="32">
																</div>

																<div class="ts-form-group vx-1-2">
																	<label><?= _x( 'Type', 'stripe vendor shipping', 'voxel' ) ?></label>
																	<div class="ts-filter">
																		<select v-model="rate.type">
																			<option value="free_shipping"><?= _x( 'Free shipping', 'stripe vendor shipping', 'voxel' ) ?></option>
																			<option value="fixed_rate"><?= _x( 'Fixed rate', 'stripe vendor shipping', 'voxel' ) ?></option>
																		</select>
																	</div>
																</div>

																<template v-if="rate.type === 'free_shipping'">
																	<div class="ts-form-group vx-1-1">
																		<label><?= _x( 'Free shipping requires', 'stripe vendor shipping', 'voxel' ) ?></label>
																		<div class="ts-filter">
																			<select v-model="rate.free_shipping.requirements">
																				<option value="none"><?= _x( 'No requirement', 'stripe vendor shipping', 'voxel' ) ?></option>
																				<option value="minimum_order_amount"><?= _x( 'Minimum order amount', 'stripe vendor shipping', 'voxel' ) ?></option>
																			</select>
																		</div>
																	</div>

																	<template v-if="rate.free_shipping.requirements === 'minimum_order_amount'">
																		<div class="ts-form-group vx-1-1">
																			<label><?= _x( 'Min. order amount', 'stripe vendor shipping', 'voxel' ) ?></label>
																			<div class="input-container">
																				<input v-model="rate.free_shipping.minimum_order_amount" type="number" step="any" class="ts-filter">
																				<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
																			</div>
																		</div>
																	</template>

																	<div class="ts-form-group vx-1-1">
																		<label>
																			<div class="switch-slider">
																				<div class="onoffswitch">
																					<input type="checkbox" class="onoffswitch-checkbox" v-model="rate.free_shipping.delivery_estimate.enabled">
																					<label class="onoffswitch-label" @click.prevent="rate.free_shipping.delivery_estimate.enabled = !rate.free_shipping.delivery_estimate.enabled"></label>
																				</div>
																			</div>
																			<?= _x( 'Add delivery estimate?', 'stripe vendor shipping', 'voxel' ) ?>
																		</label>

																		<template v-if="rate.free_shipping.delivery_estimate.enabled">
																			<div class="medium form-field-grid">
																				<div class="ts-form-group vx-1-2">
																					<label><?= _x( 'Between', 'stripe vendor shipping', 'voxel' ) ?></label>
																					<input v-model="rate.free_shipping.delivery_estimate.minimum.value" type="number" class="ts-filter">
																				</div>
																				<div class="ts-form-group vx-1-2">
																					<label><?= _x( 'Period', 'stripe vendor shipping', 'voxel' ) ?></label>
																					<div class="ts-filter">
																						<select v-model="rate.free_shipping.delivery_estimate.minimum.unit">
																							<option value="hour"><?= _x( 'Hour(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="day"><?= _x( 'Day(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="business_day"><?= _x( 'Business day(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="week"><?= _x( 'Week(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="month"><?= _x( 'Month(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																						</select>
																					</div>
																				</div>
																				<div class="ts-form-group vx-1-2">
																					<label><?= _x( 'And', 'stripe vendor shipping', 'voxel' ) ?></label>
																					<input v-model="rate.free_shipping.delivery_estimate.maximum.value" type="number" class="ts-filter">
																				</div>
																				<div class="ts-form-group vx-1-2">
																					<label><?= _x( 'Period', 'stripe vendor shipping', 'voxel' ) ?></label>
																					<div class="ts-filter">
																						<select v-model="rate.free_shipping.delivery_estimate.maximum.unit">
																							<option value="hour"><?= _x( 'Hour(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="day"><?= _x( 'Day(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="business_day"><?= _x( 'Business day(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="week"><?= _x( 'Week(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="month"><?= _x( 'Month(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																						</select>
																					</div>
																				</div>
																			</div>
																		</template>
																	</div>
																</template>
																<template v-else-if="rate.type === 'fixed_rate'">
																	<div class="ts-form-group vx-1-1">
																		<label><?= _x( 'Price per unit', 'stripe vendor shipping', 'voxel' ) ?></label>
																		<div class="input-container">
																			<input v-model="rate.fixed_rate.amount_per_unit" type="number" step="any" class="ts-filter">
																			<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
																		</div>
																	</div>

																	<div v-if="Object.keys(config.shipping_classes).length" class="ts-form-group vx-1-1">


																		<div class="medium form-field-grid">
																			<div class="ts-form-group vx-1-1 ui-heading-field">
																				<label><?= _x( 'Price by class', 'stripe vendor shipping', 'voxel' ) ?>

																				</label>
																			</div>
																			<template v-for="shipping_class, shipping_class_index in rate.fixed_rate.shipping_classes">
																				<div v-if="config.shipping_classes[ shipping_class.shipping_class ]" class="ts-form-group vx-1-2">
																					<label>
																						{{ config.shipping_classes[ shipping_class.shipping_class ].label }}
																						<!-- <a href="#" @click.prevent="rate.fixed_rate.shipping_classes.splice(shipping_class_index, 1)">Remove</a> -->
																					</label>
																					<div class="input-container">
																						<input v-model="shipping_class.amount_per_unit" type="number" step="any">
																						<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
																					</div>
																				</div>
																				<div class="ts-form-group vx-1-2">
																					<label><?= _x( 'Period', 'stripe vendor shipping', 'voxel' ) ?></label>
																					<a href="#" @click.prevent="rate.fixed_rate.shipping_classes.splice(shipping_class_index, 1)" class="ts-btn ts-btn-1 form-btn">
																						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?><?= _x( 'Remove', 'product field timeslots', 'voxel' ) ?>

																					</a>
																				</div>
																			</template>
																			<div class="ts-form-group">
																				<div class="flexify simplify-ul attribute-select" >
																					<template v-for="shipping_class in config.shipping_classes">
																						<a
																							href="#"
																							v-if="!rate.fixed_rate.shipping_classes.find( cls => cls.shipping_class === shipping_class.key )"
																							@click.prevent="rate.fixed_rate.shipping_classes.push( {
																								shipping_class: shipping_class.key,
																								amount_per_unit: null,
																							} )"
																						>{{ shipping_class.label }}</a>
																					</template>
																				</div>
																			</div>
																		</div>


																	</div>

																	<div class="ts-form-group vx-1-1">
																		<label>
																			<div class="switch-slider">
																				<div class="onoffswitch">
																					<input type="checkbox" class="onoffswitch-checkbox" v-model="rate.fixed_rate.delivery_estimate.enabled">
																					<label class="onoffswitch-label" @click.prevent="rate.fixed_rate.delivery_estimate.enabled = !rate.fixed_rate.delivery_estimate.enabled"></label>
																				</div>
																			</div>
																			<?= _x( 'Add delivery estimate?', 'stripe vendor shipping', 'voxel' ) ?>
																		</label>

																		<template v-if="rate.fixed_rate.delivery_estimate.enabled">
																			<div class="medium form-field-grid">
																				<div class="ts-form-group vx-1-2">
																					<label><?= _x( 'Between', 'stripe vendor shipping', 'voxel' ) ?></label>
																					<input v-model="rate.fixed_rate.delivery_estimate.minimum.value" type="number" class="ts-filter">
																				</div>
																				<div class="ts-form-group vx-1-2">
																					<label><?= _x( 'Period', 'stripe vendor shipping', 'voxel' ) ?></label>
																					<div class="ts-filter">
																						<select v-model="rate.fixed_rate.delivery_estimate.minimum.unit">
																							<option value="hour"><?= _x( 'Hour(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="day"><?= _x( 'Day(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="business_day"><?= _x( 'Business day(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="week"><?= _x( 'Week(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="month"><?= _x( 'Month(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																						</select>
																					</div>
																				</div>
																				<div class="ts-form-group vx-1-2">
																					<label><?= _x( 'And', 'stripe vendor shipping', 'voxel' ) ?></label>
																					<input v-model="rate.fixed_rate.delivery_estimate.maximum.value" type="number" class="ts-filter">
																				</div>
																				<div class="ts-form-group vx-1-2">
																					<label><?= _x( 'Period', 'stripe vendor shipping', 'voxel' ) ?></label>
																					<div class="ts-filter">
																						<select v-model="rate.fixed_rate.delivery_estimate.maximum.unit">
																							<option value="hour"><?= _x( 'Hour(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="day"><?= _x( 'Day(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="business_day"><?= _x( 'Business day(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="week"><?= _x( 'Week(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																							<option value="month"><?= _x( 'Month(s)', 'stripe vendor shipping', 'voxel' ) ?></option>
																						</select>
																					</div>
																				</div>
																			</div>
																		</template>
																	</div>
																</template>
															</div>
														</div>
													</template>
												</div>

												<a href="#" @click.prevent="addShippingRate(zone)" class="ts-repeater-add ts-btn ts-btn-4 form-btn">
													<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
													<?= _x( 'Add shipping rate', 'stripe vendor shipping', 'voxel' ) ?>
												</a>
											</div>
										</div>
									</div>
								</template>
							</div>
							<a href="#" class="ts-repeater-add ts-btn ts-btn-4 form-btn" @click.prevent="config.zones.push( activeZone = {
								key: $w.Voxel.helpers.randomId(8),
								label: null,
								countries: [],
								rates: [],
							} )">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
								<?= _x( 'Add shipping zone', 'stripe vendor shipping', 'voxel' ) ?>
							</a>
						</div>
						<!-- <div class="vx-1-1">
							<pre debug>{{ config.zones }}</pre>
						</div> -->
					</div>
				</div>
			</div>
		</div>
	<?php endif ?>
</div>
