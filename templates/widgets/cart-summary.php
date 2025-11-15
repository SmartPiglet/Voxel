<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

require_once locate_template( 'templates/widgets/cart-summary/file-upload.php' );
?>

<script type="text/json" class="vxconfig__icons"><?= wp_json_encode( $icons ) ?></script>
<script type="text/json" class="vxconfig"><?= wp_specialchars_decode( wp_json_encode( $config ) ) ?></script>
<div class="vx-loading-screen ts-checkout-loading">
	<div class="ts-no-posts">
		<span class="ts-loader"></span>
	</div>
</div>
<div class="ts-form ts-checkout ts-checkout-regular">
	<template v-if="loading"></template>
	<template v-else-if="!hasItems()">
		<div class="vx-loading-screen">
			<div class="ts-form-group ts-no-posts">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('nostock_ico') ) ?: \Voxel\svg( 'box-remove.svg' ) ?>
				<p><?= _x( 'No products selected for checkout', 'cart summary', 'voxel' ) ?></p>
			</div>
		</div>
	</template>
	<template v-else>
		<?php if ( ! is_user_logged_in() && $config['guest_customers']['behavior'] === 'proceed_with_email' ): ?>
			<a href="<?= esc_url( $auth_link ) ?>" class="ts-btn ts-btn-1 form-btn">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_enter') ) ?: \Voxel\svg( 'user.svg' ) ?>
				<?= _x( 'Existing customer? Sign in', 'cart summary', 'voxel' ) ?>
			</a>
		<?php endif ?>

		<?php if ( ! is_user_logged_in() && $config['guest_customers']['behavior'] === 'proceed_with_email' ): ?>
			<div class="checkout-section form-field-grid">
				<div class="ts-form-group">
					<div class="or-group">
						<span class="or-text"><?= _x( 'Or continue as Guest', 'cart summary', 'voxel' ) ?></span>
						<div class="or-line"></div>
					</div>
				</div>
				<div class="ts-form-group vx-1-1">
					<label><?= esc_attr( _x( 'Email address', 'cart summary', 'voxel' ) ) ?></label>
					<div class="ts-input-icon flexify">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
						<input
							v-model="quick_register.email"
							type="email"
							placeholder="<?= esc_attr( _x( 'Your email address', 'cart summary', 'voxel' ) ) ?>"
							@input="quick_register.sent_code ? quick_register.sent_code = false : ''"
							:readonly="quick_register.sending_code || quick_register.registered"
							@keydown.enter="$refs.sendCode?.click()"
							class="ts-filter"
						>
					</div>
				</div>
				<?php if ( $config['guest_customers']['proceed_with_email']['require_verification'] ): ?>
					<div v-if="!quick_register.sent_code && /^\S+@\S+\.\S+$/.test(quick_register.email)" class="ts-form-group vx-1-1">
						<div :class="{'vx-disabled': quick_register.sending_code}">
							<a href="#" class="ts-btn ts-btn-1 form-btn" ref="sendCode" @click.prevent="sendEmailVerificationCode">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_email_ico') ) ?: \Voxel\svg( 'envelope.svg' ) ?>
								<?= _x( 'Send confirmation code', 'cart summary', 'voxel' ) ?>
							</a>
						</div>
					</div>
					<div v-if="quick_register.sent_code" class="ts-form-group vx-1-1">
						<label><?= esc_attr( _x( 'Confirmation code', 'cart summary', 'voxel' ) ) ?></label>
						<input
							ref="emailConfirmCode"
							type="text"
							maxlength="6"
							placeholder="<?= esc_attr( _x( 'Type your 6 digit code', 'cart summary', 'voxel' ) ) ?>"
							v-model="quick_register.code"
							:readonly="quick_register.registered"
							class="ts-filter"
						>
					</div>
				<?php endif ?>
			</div>
		<?php endif ?>
		<?php if ( is_user_logged_in() || $config['guest_customers']['behavior'] !== 'proceed_with_email' ): ?>
			<div class="ts-cart-head">
				<h1 v-if="metadata.cart_label">
					{{ metadata.cart_label }}
				</h1>
				<h1 v-else>
					<?= _x( 'Order summary', 'cart summary', 'voxel' ) ?>
				</h1>
				<div v-if="hasShippableProducts()" id="ts-cart-shipping-country">
					<span class="cn-picker">
						<?= _x( 'Ship to', 'cart summary', 'voxel' ) ?>
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_location') ) ?: \Voxel\svg( 'marker.svg' ) ?>
						<span>{{ shippingCountries[ shipping.country ] || '-' }}</span>
						<select class="plain-select" ref="shippingCountry" :value="shipping.country" @change="shippingCountryUpdated($event)">
							<template v-for="country, country_code in shippingCountries">
								<option :value="country_code">{{ country }}</option>
							</template>
						</select>
					</span>
				</div>
			</div>
		<?php endif ?>
		<div class="checkout-section form-field-grid">
			<template v-if="shouldGroupItemsByVendor()">
				<template v-for="vendor in vendors">
					<div class="ts-form-group">
						<div class="or-group">
							<span class="or-text">
								<?= \Voxel\replace_vars( _x( 'Sold by @vendor_name', 'cart summary', 'voxel' ), [
									'@vendor_name' => '{{ vendor.display_name }}',
								] ) ?>
							</span>
							<div class="or-line"></div>
						</div>
					</div>
					<div class="ts-form-group">
						<ul class="ts-cart-list simplify-ul">
							<template v-for="item in vendor.items">
								<cart-item :checkout="this" :item="item"></cart-item>
							</template>
						</ul>
					</div>
					<div v-if="vendor.has_shippable_products && config.shipping.responsibility === 'vendor'" class="ts-form-group ts-cart-vendor-shipping">
						<template v-if="shipping.country && vendor.shipping_countries[ shipping.country ]">
							<span class="cn-picker">
								<?= _x( 'Ship via', 'cart summary', 'voxel' ) ?>
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_shipping_ico') ) ?: \Voxel\svg( 'box-4.svg' ) ?>
								<template v-if="isVendorShippingSelected(vendor)">
									<span>{{ vendor.shipping_zones[ shipping.vendors[vendor.key].zone ].rates[ shipping.vendors[vendor.key].rate ].label }}</span>
								</template>
								<select class="plain-select" v-model="shipping.vendors[vendor.key]">
									<template v-for="shipping_zone in vendor.shipping_zones">
										<template v-for="shipping_rate in shipping_zone.rates">
											<template v-if="shouldListVendorShippingRate(vendor, shipping_rate, shipping_zone)">
												<option :value="{ zone: shipping_zone.key, rate: shipping_rate.key }" :disabled="!vendorRateMeetsCriteria(vendor, shipping_rate, shipping_zone)">
													{{ shipping_rate.label }}
													<template v-if="getVendorShippingTotalForRate( vendor, shipping_rate ) !== 0">
														- {{ currencyFormat( getVendorShippingTotalForRate( vendor, shipping_rate ), cart_currency ) }}
													</template>
													<template v-if="shipping_rate.delivery_estimate && vendorRateMeetsCriteria(vendor, shipping_rate, shipping_zone)">
														({{ shipping_rate.delivery_estimate }})
													</template>
													<template v-if="!vendorRateMeetsCriteria(vendor, shipping_rate, shipping_zone)">
														<template v-if="shipping_rate.type === 'free_shipping' && shipping_rate.requirements === 'minimum_order_amount'">
															(<?= _x( 'Minimum order amount:', 'cart summary', 'voxel' ) ?> {{ currencyFormat( shipping_rate.minimum_order_amount, cart_currency ) }})
														</template>
													</template>
												</option>
											</template>
										</template>
									</template>
								</select>
							</span>
						</template>
						<template v-else-if="shipping.country">
							<span class="cn-picker">
								<?= \Voxel\replace_vars( _x( 'This vendor does not ship to @country_name', 'cart summary', 'voxel' ), [
									'@country_name' => '{{ shippingCountries[ shipping.country ] }}',
								] ) ?>
							</span>
						</template>
					</div>
				</template>
			</template>
			<template v-else>
				<div class="ts-form-group">
					<div class="or-group">
						<span class="or-text"><?= _x( 'Items', 'cart summary', 'voxel' ) ?></span>
						<div class="or-line"></div>
					</div>
				</div>
				<div class="ts-form-group">
					<ul class="ts-cart-list simplify-ul">
						<template v-for="item in items">
							<cart-item :checkout="this" :item="item"></cart-item>
						</template>
					</ul>
				</div>
			</template>
		</div>

		<div v-if="hasShippableProducts() && getShippingMethod() === 'platform_rates'" class="checkout-section form-field-grid">
			<div class="ts-form-group">
				<div class="or-group">
					<span class="or-text"><?= _x( 'Shipping', 'cart summary', 'voxel' ) ?></span>
					<div class="or-line"></div>
				</div>
			</div>
			<template v-if="shipping.status !== 'completed'">
				<div class="ts-form-group vx-1-1">
					<div class="vx-loading-screen">
						<div class="ts-no-posts">
							<span class="ts-loader"></span>
						</div>
					</div>
				</div>
			</template>
			<template v-else>
				<div class="ts-form-group vx-1-1">
					<ul class="simplify-ul addon-cards flexify">
						<template v-for="shipping_zone in config.shipping.zones">
							<template v-for="shipping_rate in shipping_zone.rates">
								<li v-if="shouldListShippingRate(shipping_rate, shipping_zone)" class="flexify" :class="{'adc-selected': shipping.zone === shipping_zone.key && shipping.rate === shipping_rate.key, 'vx-disabled': !rateMeetsCriteria(shipping_rate, shipping_zone)}" @click.prevent="shipping.zone = shipping_zone.key; shipping.rate = shipping_rate.key">
									<div class="card-icn">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_shipping_ico') ) ?: \Voxel\svg( 'box-4.svg' ) ?>
									</div>
									<div class="addon-details">
										<span class="adc-title">
											{{ shipping_rate.label }}
										</span>
										<span v-if="shipping_rate.delivery_estimate && rateMeetsCriteria(shipping_rate, shipping_zone)" class="adc-subtitle">{{ shipping_rate.delivery_estimate }}</span>
										<template v-if="!rateMeetsCriteria(shipping_rate, shipping_zone)">
											<span v-if="shipping_rate.type === 'free_shipping' && shipping_rate.requirements === 'minimum_order_amount'" class="adc-subtitle">
												<?= _x( 'Minimum order amount:', 'cart summary', 'voxel' ) ?> {{ currencyFormat( shipping_rate.minimum_order_amount, cart_currency ) }}
											</span>
										</template>
										<span class="vx-addon-price">
											<template v-if="getShippingTotalForRate( shipping_rate ) === 0">
												{{ config.l10n.free }}
											</template>
											<template v-else>
												{{ currencyFormat( getShippingTotalForRate( shipping_rate ), cart_currency ) }}
											</template>
										</span>
									</div>
								</li>
							</template>
						</template>
					</ul>
					<!-- <pre debug>{{ config.shipping }}</pre> -->
				</div>
			</template>

		</div>

		<?php if ( is_user_logged_in() || $config['guest_customers']['behavior'] === 'proceed_with_email' ): ?>
			<div class="checkout-section form-field-grid">
				<div class="ts-form-group">
					<div class="or-group">
						<span class="or-text"><?= _x( 'Details', 'cart summary', 'voxel' ) ?></span>
						<div class="or-line"></div>
					</div>
				</div>

				<suspense>
					<template #fallback>
						<div class="ts-no-posts">
							<span class="ts-loader"></span>
						</div>
					</template>
					<template #default>
						<template v-for="item in items">
							<template v-for="component in item.components">
								<component
									:is="'cart-item:'+component.type"
									:data="component.data"
									:parent="this"
									:cart-item="item"
								></component>
							</template>
						</template>
					</template>
				</suspense>

				<div class="tos-checkbox ts-form-group vx-1-1 switcher-label">
					<label @click.prevent="toggleComposer">
						<div class="ts-checkbox-container">
							<label class="container-checkbox">
								<input :checked="order_notes.enabled" type="checkbox" tabindex="0" class="hidden">
								<span class="checkmark"></span>
							</label>
						</div>
						<?= _x( 'Add order notes?', 'cart summary', 'voxel' ) ?>
					</label>
				</div>
				<div v-if="order_notes.enabled" class="ts-form-group vx-1-1">
					<textarea
						ref="orderNotes"
						:value="order_notes.content"
						@input="order_notes.content = $event.target.value; resizeComposer();"
						placeholder="<?= esc_attr( _x( 'Add notes about your order', 'cart summary', 'voxel' ) ) ?>"
						class="autofocus ts-filter"
					></textarea>
					<textarea ref="_orderNotes" disabled style="height:5px;position:fixed;top:-9999px;left:-9999px;visibility:hidden;"></textarea>
				</div>

				<?php if ( ! is_user_logged_in() && $config['guest_customers']['behavior'] === 'proceed_with_email' && $config['guest_customers']['proceed_with_email']['require_tos'] ): ?>
					<div class="tos-checkbox ts-form-group vx-1-1 switcher-label">
						<label @click.prevent="quick_register.terms_agreed = !quick_register.terms_agreed">
							<div class="ts-checkbox-container">
								<label class="container-checkbox">
									<input :checked="quick_register.terms_agreed" type="checkbox" tabindex="0" class="hidden">
									<span class="checkmark"></span>
								</label>
							</div>
							<p class="field-info">
								<?= \Voxel\replace_vars( _x( 'I agree to the <a:terms>Terms and Conditions</a> and <a:privacy>Privacy Policy</a>', 'cart summary', 'voxel' ), [
									'<a:terms>' => '<a target="_blank" @click.stop href="'.esc_url( get_permalink( \Voxel\get( 'templates.terms' ) ) ?: home_url('/') ).'">',
									'<a:privacy>' => '<a target="_blank" @click.stop href="'.esc_url( get_permalink( \Voxel\get( 'templates.privacy_policy' ) ) ?: home_url('/') ).'">'
								] ) ?>
							</p>

						</label>
					</div>
				<?php endif ?>
			</div>
			<div class="checkout-section">
				<ul v-if="getSubtotal() !== 0" class="ts-cost-calculator simplify-ul flexify">
					<li v-if="getShippingTotal() !== null">
						<div class="ts-item-name">
							<p><?= _x( 'Shipping', 'cart summary shipping cost', 'voxel' ) ?></p>
						</div>
						<div class="ts-item-price">
							<p>{{ currencyFormat( getShippingTotal(), cart_currency ) }}</p>
						</div>
					</li>
					<li class="ts-total">
						<div class="ts-item-name">
							<p><?= _x( 'Subtotal', 'cart summary', 'voxel' ) ?></p>
						</div>
						<div class="ts-item-price">
							<p>{{ currencyFormat( getSubtotal(), cart_currency ) }}</p>
						</div>
					</li>
				</ul>
				<a href="#" class="ts-btn ts-btn-2 form-btn" @click.prevent="!processing ? submit() : null" :class="{'ts-loading-btn': processing, 'vx-disabled': !canProceedWithPayment()}">
					<div v-if="processing" class="ts-loader-wrapper">
						<span class="ts-loader"></span>
					</div>
					
					<template v-if="getSubtotal() === 0">
						<?= _x( 'Continue', 'cart summary', 'voxel' ) ?>
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('continue_ico') ) ?: \Voxel\svg( 'arrow-right.svg' ) ?>

					</template>
					<template v-else>
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_checkout_icon') ) ?: \Voxel\svg( 'bag-2.svg' ) ?>
						<?= _x( 'Pay now', 'cart summary', 'voxel' ) ?>
					</template>
				</a>
			</div>
		<?php else: ?>
			<div class="checkout-section">
				<ul v-if="getSubtotal() !== 0" class="ts-cost-calculator simplify-ul flexify">
					<li v-if="getShippingTotal() !== null">
						<div class="ts-item-name">
							<p><?= _x( 'Shipping', 'cart summary shipping cost', 'voxel' ) ?></p>
						</div>
						<div class="ts-item-price">
							<p>{{ currencyFormat( getShippingTotal(), cart_currency ) }}</p>
						</div>
					</li>
					<li class="ts-total">
						<div class="ts-item-name">
							<p><?= _x( 'Subtotal', 'cart summary', 'voxel' ) ?></p>
						</div>
						<div class="ts-item-price">
							<p>{{ currencyFormat( getSubtotal(), cart_currency ) }}</p>
						</div>
					</li>
				</ul>
				<a href="<?= esc_url( $auth_link ) ?>" class="ts-btn ts-btn-2 form-btn">
					<div v-if="processing" class="ts-loader-wrapper">
						<span class="ts-loader"></span>
					</div>
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('auth_user_ico') ) ?: \Voxel\svg( 'user.svg' ) ?>
					<?= _x( 'Log in to continue', 'cart summary', 'voxel' ) ?>
				</a>
			</div>
		<?php endif ?>
	</template>
	<!-- <pre debug>{{ itemsByVendor }}</pre> -->
</div>
