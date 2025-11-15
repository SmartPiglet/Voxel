<script type="text/html" id="vx-cart-item">
	<li :class="{'vx-disabled': item._disabled, [item.custom_class]:true}">
		<div class="cart-image" v-html="item.logo"></div>
		<div class="cart-item-details">
			<a :href="item.link">{{ item.title }}</a>
			<span v-if="item.subtitle">{{ item.subtitle }}</span>

			<template v-if="item.pricing.total_amount === 0">
				<span><?= _x( 'Free', 'cart summary', 'voxel' ) ?></span>
			</template>
			<template v-else>
				<span>{{ checkout.currencyFormat( item.pricing.total_amount, item.currency ) }}</span>
			</template>
		</div>
		<div v-if="item.quantity.enabled" class="cart-stepper">
			<a @click.prevent="minusOne" href="#" class="ts-icon-btn ts-smaller">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
			</a>
			<span>{{ checkout.getItemQuantity(item) }}</span>
			<a @click.prevent="plusOne" href="#" class="ts-icon-btn ts-smaller" :class="{'vx-disabled': !hasStockLeft()}">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
			</a>
		</div>
		<div v-else class="cart-stepper">
			<a href="#" class="ts-icon-btn ts-smaller" @click.prevent="removeItem">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_delete_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
			</a>
		</div>
	</li>
</script>
