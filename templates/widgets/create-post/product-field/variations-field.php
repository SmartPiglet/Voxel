<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-variations">
	<attributes :product="product" :field="this" ref="attributes" @change:attributes="onAttributesUpdate"></attributes>

	<div v-if="variationList.length" class="ts-form-group" ref="variationContainer">
		<label>
			<?= _x( 'Variations', 'product field variations', 'voxel' ) ?> ({{ variationList.length }})
			<div class="vx-dialog">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
				<div class="vx-dialog-content min-scroll">
					<p><?= _x( 'Product variations are automatically generated based on your attributes.<br><br>An individual variation requires a price to be available for purchase.<br><br>Variations without a price, are automatically disabled upon saving changes', 'product field variations', 'voxel' ) ?></p>
				</div>
			</div>
		</label>

		<div class="ts-repeater-container">
			<template v-for="variation in variationList" :key="variation.id">
				<div class="ts-field-repeater" :class="{collapsed: activeVariation !== variation, disabled: !variation.enabled, 'v-checked': variation.enabled && hasValidPrice(variation) && hasValidStock(variation), 'v-error': variation.enabled && ( ! hasValidPrice(variation) || ! hasValidStock(variation) )}" style="pointer-events: all;">
					<div class="ts-repeater-head" @click.prevent="activeVariation = variation === activeVariation ? null : variation">
						<template v-if="variation.enabled && hasValidPrice(variation) && hasValidStock(variation)">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('variation_icon_enabled') ) ?: \Voxel\svg( 'checkmark-circle.svg' ) ?>
						</template>
						<template v-else>
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('variation_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
						</template>
						<label>
							{{ getVariationLabel(variation) }}
						</label>
						<template v-if="variation.enabled && field.props.stock.enabled && variation.config.stock.enabled && ! hasValidStock(variation)">
							<em><?= _x( 'Out of stock', 'product field variations', 'voxel' ) ?></em>
						</template>
						<template v-else-if="typeof variation.config.base_price.amount === 'number'">
							<template v-if="typeof variation.config.base_price.discount_amount === 'number'">
								<em>
									{{ $root.currencyFormat( variation.config.base_price.discount_amount ) }}
									<s>{{ $root.currencyFormat( variation.config.base_price.amount ) }}</s>
								</em>
							</template>
							<template v-else>
								<em>{{ $root.currencyFormat( variation.config.base_price.amount ) }}</em>
							</template>
						</template>
						<template v-else>
							<em><?= _x( 'No price added', 'product field variations', 'voxel' ) ?></em>
						</template>
						<div class="ts-repeater-controller">
							<a href="#" v-if="!variation.enabled" @click.stop.prevent="variation.enabled = true" class="ts-icon-btn ts-smaller">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
							</a>
							<a href="#" v-if="variation.enabled" @click.stop.prevent="variation.enabled = false" class="ts-icon-btn ts-smaller">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
							</a>
							<a href="#" class="ts-icon-btn ts-smaller" @click.prevent>
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
							</a>
						</div>
					</div>

					<div class="medium form-field-grid">
						<variation-base-price
							:product="product"
							:product-type="productType"
							:variations="this"
							:variation="variation"
							:field="field.props.fields['base-price']"
						></variation-base-price>

						<div class="ts-form-group">
							<label><?= _x( 'Image', 'product field variations', 'voxel' ) ?></label>
							<file-upload v-model="variation.image" allowed-file-types="image/jpeg,image/png,image/webp"></file-upload>
						</div>

						<template v-if="field.props.stock.enabled">
							<variation-stock
								:product="product"
								:product-type="productType"
								:variations="this"
								:variation="variation"
								:field="field.props.fields['stock']"
							></variation-stock>
						</template>

						<variation-bulk-settings
							:product="product"
							:product-type="productType"
							:variations="this"
							:variation="variation"
						></variation-bulk-settings>
					</div>
				</div>
			</template>
		</div>
	</div>
</script>
