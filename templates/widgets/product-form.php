<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<?php if ( ! $is_purchasable ): ?>
	<div class="ts-form ts-product-form vx-loading" style="pointer-events: all;">
		<div class="ts-product-main vx-loading-screen">
			<div class="ts-form-group ts-no-posts">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('nostock_ico') ) ?: \Voxel\svg( 'box-remove.svg' ) ?>
				<p><?= $error_message ?></p>
			</div>
		</div>
	</div>
<?php else: ?>
	<script type="text/json" class="vxconfig"><?= wp_specialchars_decode( wp_json_encode( $config ) ) ?></script>
	<div class="ts-form ts-product-form vx-loading">
		<div class="ts-product-main vx-loading-screen">

			<div class="ts-no-posts">
				<span class="ts-loader"></span>
			</div>
		</div>
		<div class="ts-product-main">

			<template v-for="field in config.props.fields">
				<component
					:is="field.component_key"
					:field="field"
					:ref="'field:'+field.key"
				></component>
			</template>

			<div v-if="config.props.cart.enabled" class="ts-form-group product-actions">
				<a href="#" class="ts-btn form-btn ts-btn-2" @click.prevent="!processing ? ( $event.shiftKey ? directCart() : addToCart() ) : null" :class="{'ts-loading-btn': processing}">
					<div v-if="processing" class="ts-loader-wrapper">
						<span class="ts-loader"></span>
					</div>
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_cart_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
					<?= _x( 'Add to cart', 'product form', 'voxel' ) ?>
				</a>

			</div>
			<div v-else class="ts-form-group product-actions">
				<a href="#" class="ts-btn form-btn ts-btn-2" @click.prevent="!processing ? directCart() : null" :class="{'ts-loading-btn': processing}">
					<div v-if="processing" class="ts-loader-wrapper">
						<span class="ts-loader"></span>
					</div>
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_checkout_icon') ) ?: \Voxel\svg( 'bag-2.svg' ) ?>
					<?= _x( 'Continue', 'product form', 'voxel' ) ?>
				</a>
			</div>

			<div v-if="pricing_summary.visible_items.length" class="ts-form-group tcc-container">
				<ul class="ts-cost-calculator simplify-ul flexify">
					<template v-for="item in pricing_summary.visible_items">
						<li v-if="!item.hidden">
							<div class="ts-item-name">
								<p>
									{{ item.label }}
								</p>
							</div>
							<div class="ts-item-price">
								<p>{{ item.value ? item.value : currencyFormat( item.amount ) }}</p>
							</div>
						</li>
					</template>

					<li class="ts-total">
						<div class="ts-item-name">
							<p><?= _x( 'Subtotal', 'product form', 'voxel' ) ?></p>
						</div>
						<div class="ts-item-price">
							<p>{{ currencyFormat( pricing_summary.total_amount ) }}</p>
						</div>
					</li>
				</ul>
			</div>
			<!-- <teleport to="#pf-dbg">
				<pre debug>{{ config }}</pre>
			</teleport> -->
		</div>
	</div>
	<!-- <div id="pf-dbg" style="margin-top: 50px;"></div> -->
<?php endif ?>
