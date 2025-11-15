<?php
if ( ! defined('ABSPATH') ) {
	exit;
}
?>

<script type="text/json" class="vxconfig"><?= wp_specialchars_decode( wp_json_encode( $config ) ) ?></script>
<div class="ts-form ts-checkout ts-checkout-promotion" v-cloak>
	<div class="cart-head">
		<h1><?= \Voxel\replace_vars( _x( 'Promote @post_title', 'promote post', 'voxel' ), [
			'@post_title' => esc_html( $post->get_title() )
		] ) ?></h1>
	</div>
	<div class="checkout-section form-field-grid">
		<div class="ts-form-group">
			<label><?= _x( 'Select promotion package', 'promote post', 'voxel' ) ?></label>
			<ul class="simplify-ul addon-cards flexify">
				<template v-for="package in config.packages">
					<li class="flexify" :class="{'adc-selected': package === selected}" :style="{'--ts-accent-1': package.color}"
						@click.prevent="selected = package">
						<div v-if="package.icon" class="card-icn" v-html="package.icon"></div>
						<div v-else class="card-icn">
							<?= \Voxel\get_svg( 'bolt.svg' ) ?>
						</div>
						<div class="addon-details">
							<span class="adc-title">{{ package.label }}</span>
							<span class="adc-subtitle">{{ package.description }}</span>
							<div class="vx-addon-price">{{ currencyFormat( package.price_amount ) }}</div>
						</div>
					</li>
				</template>
			</ul>
		</div>
	</div>
	<div class="checkout-section">
		<a href="#" class="ts-btn ts-btn-2 form-btn" @click.prevent="!processing ? checkout() : null" :class="{'ts-loading-btn': processing}">
			<div v-if="processing" class="ts-loader-wrapper">
				<span class="ts-loader"></span>
			</div>
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_checkout_icon') ) ?: \Voxel\svg( 'bag-2.svg' ) ?>
			<?= _x( 'Pay now', 'promote post', 'voxel' ) ?>
		</a>
	</div>
</div>
