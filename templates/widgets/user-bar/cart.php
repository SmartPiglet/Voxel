<li
	class="ts-popup-cart elementor-repeater-item-<?= $component['_id'] ?>"
	data-config="<?= esc_attr( wp_json_encode( [
		'nonce' => wp_create_nonce('vx_cart'),
		'is_cart_empty' => ! is_user_logged_in() || ! metadata_exists( 'user', get_current_user_id(), 'voxel:cart' ),
	] ) ) ?>"
>
	<a ref="target" @mousedown="open" @click.prevent @vx:open="open" href="#" role="button" aria-label="<?= $component['cart_title'] ?>">
		<div class="ts-comp-icon flexify" ref="icon">
			<?php \Voxel\render_icon( $component['choose_component_icon'] ) ?>
			<template v-if="showIndicator">
				<span ref="indicator" class="unread-indicator"></span>
			</template>
			<?php if ( is_user_logged_in() && metadata_exists( 'user', get_current_user_id(), 'voxel:cart' ) ): ?>
				<span v-if="false" class="unread-indicator"></span>
			<?php endif ?>
		</div>
		<span class="ts_comp_label" ><?= $component['cart_title'] ?></span>
	</a>
	<teleport to="body" class="hidden">
		<transition name="form-popup">
			<form-popup
				ref="popup"
				v-if="$root.active"
				:target="$refs.target"
				class="ts-cart-popup lg-width"
				@blur="active = false"
			>
				<div class="ts-popup-head flexify ts-sticky-top" ref="top">
					<div class="ts-popup-name flexify">
						<?php \Voxel\render_icon( $component['choose_component_icon'] ) ?>
						<span><?= $component['cart_title'] ?></span>
					</div>
					<ul class="flexify simplify-ul">
						<li class="flexify" v-if="hasItems()">
							<a href="#" class="ts-icon-btn" role="button" @click.prevent="emptyCart">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_trash_ico') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
							</a>
						</li>
						<li class="flexify ts-popup-close">
							<a @click.prevent="$root.active = false" href="#" class="ts-icon-btn" role="button">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_close_ico') ) ?: \Voxel\svg( 'close.svg' ) ?>
							</a>
						</li>
					</ul>
				</div>
				<div v-if="loading" class="ts-empty-user-tab">
					<span class="ts-loader"></span>
				</div>
				<div v-else-if="!hasItems()" class="ts-empty-user-tab">
					<?php \Voxel\render_icon( $component['choose_component_icon'] ) ?>
					<p><?= _x( 'No items added to cart', 'cart', 'voxel' ) ?></p>
				</div>
				<div v-else class="ts-form-group" :class="{'vx-disabled': disabled}">
					<ul class="ts-cart-list simplify-ul">
						<template v-for="item in items">
							<li :class="{'vx-disabled': item._disabled}">
								<div class="cart-image" v-html="item.logo"></div>
								<div class="cart-item-details">
									<a :href="item.link">{{ item.title }}</a>
									<span v-if="item.subtitle">{{ item.subtitle }}</span>

									<template v-if="item.pricing.total_amount === 0">
										<span><?= _x( 'Free', 'cart summary', 'voxel' ) ?></span>
									</template>
									<template v-else>
										<span>{{ currencyFormat( item.pricing.total_amount ) }}</span>
									</template>
								</div>
								<div v-if="item.quantity.enabled" class="cart-stepper">
									<a @click.prevent="minusOne(item)" href="#" class="ts-icon-btn ts-smaller">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
									</a>
									<span>{{ getItemQuantity(item) }}</span>
									<a @click.prevent="plusOne(item)" href="#" class="ts-icon-btn ts-smaller" :class="{'vx-disabled': !hasStockLeft(item)}">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
									</a>
								</div>
								<div v-else class="cart-stepper">
									<a href="#" class="ts-icon-btn ts-smaller" @click.prevent="removeItem(item)">
										<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_trash_ico') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
									</a>
								</div>
							</li>
						</template>
					</ul>
				</div>
				<template #controller>
					<template v-if="loaded && hasItems()">
						<div class="ts-cart-controller" :class="{'vx-disabled': disabled}">
							<div v-if="getSubtotal() !== 0" class="cart-subtotal">
								<span><?= _x( 'Subtotal', 'cart summary', 'voxel' ) ?></span>
								<span>{{ currencyFormat( getSubtotal() ) }}</span>
							</div>
							<a :href="checkout_link"  class="ts-btn ts-btn-2" >
								<?= __( 'Continue', 'voxel' ) ?>
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
							</a>
						</div>
					</template>
					<div v-else></div>
				</template>
			</form-popup>
		</transition>
	</teleport>
</li>
