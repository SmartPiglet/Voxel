<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

require_once locate_template( 'templates/widgets/orders/item-promotion-details.php' );

?>
<script type="text/html" id="orders-single">
	<div class="vx-order-ease">
		<div v-if="order" class="single-order" :class="{'vx-pending': running_action || orders.order.loading}">
			<div class="vx-order-head">
				<a href="#" @click.prevent="goBack" class="ts-btn ts-btn-1 ts-go-back">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_back') ) ?: \Voxel\get_svg( 'chevron-left.svg' ) ?>
					<?= _x( 'Go back', 'single order', 'voxel' ) ?>
				</a>

				<template v-if="order.actions.dms.enabled && order.customer.id && order.actions.dms.vendor_target">
					<a @click.prevent="openConversation" href="#" class="ts-btn ts-btn-1 has-tooltip"
						:data-tooltip="isVendor() ? <?= esc_attr( wp_json_encode( _x( 'Message customer', 'single order', 'voxel' ) ) ) ?> : <?= esc_attr( wp_json_encode( _x( 'Message seller', 'single order', 'voxel' ) ) ) ?>">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_inbox') ) ?: \Voxel\get_svg( 'inbox.svg' ) ?>
					</a>
				</template>

				<template v-if="order.actions.primary.length">
					<template v-for="action in order.actions.primary">
						<a href="#" @click.prevent="runAction(action)" class="ts-btn ts-btn-2">
							<template v-if="action.action.endsWith('vendor.approve')">
								<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_checkmark') ) ?: \Voxel\get_svg( 'checkmark-circle.svg' ) ?>
							</template>
							{{ action.label }}
						</a>
					</template>
				</template>

				<template v-if="order.actions.secondary.length">
					<form-group
						popup-key="actions"
						ref="actions"
						:show-clear="false"
						:show-save="false"
						:default-class="false"
						class="ts-btn ts-btn-1 has-tooltip ts-popup-target"
						tag="a"
						href="#"
						@click.prevent
						@mousedown="$root.activePopup = 'actions'"
						data-tooltip="<?= esc_attr( _x( 'More actions', 'single order', 'voxel' ) ) ?>"
					>
						<template #trigger>
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_menu') ) ?: \Voxel\get_svg( 'menu.svg' ) ?>
						</template>
						<template #popup>
							<div class="ts-term-dropdown ts-md-group">
								<ul class="simplify-ul ts-term-dropdown-list min-scroll">
									<template v-for="action in order.actions.secondary">
										<li>
											<a href="#" class="flexify" @click.prevent="runAction( action )">
												<span>{{ action.label }}</span>
											</a>
										</li>
									</template>
								</ul>
							</div>
						</template>
					</form-group>
				</template>
			</div>

			<div class="order-timeline">
				<div class="order-event">
					<template v-if="order.customer.avatar">
						<a :href="order.customer.link">
							<div class="vx-avatar big-avatar" v-html="order.customer.avatar"></div>
						</a>
					</template>
					<div v-if="order.shipping.enabled" class="order-status" :class="order.shipping.status.class || 'vx-neutral'">
						{{ order.shipping.status.label || order.shipping.status.key }}
					</div>
					<div v-else class="order-status" :class="orders.config.statuses_ui[ order.status.key ]?.class || 'vx-neutral'">
						{{ orders.config.statuses[ order.status.key ]?.label || order.status.key }}
					</div>
					<h3>
						<?= \Voxel\replace_vars( _x( '@customer_name submitted order #@order_id', 'orders', 'voxel' ), [
							'@customer_name' => '{{ order.customer.name }}',
							'@order_id' => '{{ order.id }}',
						] ) ?>
					</h3>
					<span>{{ order.created_at }}</span>
				</div>

				<div v-if="order.child_orders.length" class="order-event ts-child-orders">
					<div class="vx-order-list">
						<template v-for="child_order in order.child_orders">
							<div class="vx-order-card" :class="'vx-status-'+child_order.status" @click.prevent="$root.viewOrder(child_order.id, order.id)">
								<div class="vx-order-meta vx-order-title">
									<div v-if="child_order.vendor.avatar" class="vx-avatar" v-html="child_order.vendor.avatar"></div>
									<div v-else class="vx-avatar">
										<img src="<?= esc_url( \Voxel\get_image( 'platform.jpg' ) ) ?>">
									</div>
									<!-- <span class="order-badge vx-hide-mobile">
										#{{ child_order.id }}
									</span> -->
									<b v-if="child_order.item_count <= 1">
										<?= \Voxel\replace_vars( _x( 'One item sold by @vendor_name', 'orders', 'voxel' ), [
											'@vendor_name' => '{{ child_order.vendor.name }}',
										] ) ?>
									</b>
									<b v-else>
										<?= \Voxel\replace_vars( _x( '@count items sold by @vendor_name', 'orders', 'voxel' ), [
											'@count' => '{{ child_order.item_count }}',
											'@vendor_name' => '{{ child_order.vendor.name }}',
										] ) ?>
									</b>
								</div>
								<div class="vx-order-meta">
									<!-- <span v-if="child_order.item_count > 1" class="vx-hide-mobile">
										<?= \Voxel\replace_vars( _x( '@count items', 'orders', 'voxel' ), [
											'@count' => '{{ child_order.item_count }}',
										] ) ?>
									</span> -->
									<span v-if="child_order.total" class="vx-hide-mobile">{{ $root.currencyFormat( child_order.total, child_order.currency ) }}</span>
									<span v-else-if="child_order.subtotal" class="vx-hide-mobile">{{ $root.currencyFormat( child_order.subtotal, child_order.currency ) }}</span>
								</div>
								<div v-if="child_order.shipping_status !== null" class="order-status" :class="$root.config.shipping_statuses[ child_order.shipping_status ]?.class || 'vx-neutral'">
									{{ $root.config.shipping_statuses[ child_order.shipping_status ]?.label || child_order.shipping_status }}
								</div>
								<div v-else class="order-status" :class="$root.config.statuses_ui[ child_order.status ]?.class || 'vx-neutral'">
									{{ $root.config.statuses[ child_order.status ]?.label || child_order.status }}
								</div>
							</div>
						</template>
					</div>
					<!-- <pre debug>{{ order.child_orders }}</pre> -->
				</div>

				

				<template v-for="component in order.components">
					<component :is="'order:'+component.type" :data="component.data" :parent="this" :order="order"></component>
				</template>

				<template v-for="item in order.items">
					<template v-for="component in item.components">
						<component :is="'order-item:'+component.type" :data="component.data" :parent="this" :order="order" :order-item="item"></component>
					</template>
					<template v-if="item.type === 'regular' && item.details.promotion_package && ['active', 'ended', 'canceled'].includes(item.details.promotion_package.status)">
						<item-promotion-details :item="item" :order="order" :parent="this"></item-promotion-details>
					</template>
				</template>
				<div class="order-event">
					<div class="order-event-box">
						<ul v-if="order.items.length" class="ts-cart-list simplify-ul">
							<template v-for="item in order.items">
								<li>
									<div class="cart-image">
						      			<img width="150" height="150" :src="item.product.thumbnail_url || '<?= esc_url( \Voxel\get_image( 'platform.jpg' ) ) ?>'" class="ts-status-avatar" decoding="async">
									</div>
									<div class="cart-item-details">

										<div class="order-item-title">
											<a :href="item.product.link">{{ item.product.label }}</a>
											<span>{{ orders.currencyFormat( item.subtotal, item.currency ) }}</span>
										</div>
										<span>{{ item.product.description }}</span>

										<span class="cart-data-inputs" v-html="!dataInputs[item.id].truncated.exists || item._expanded ? item.data_inputs_markup : dataInputs[item.id].truncated.content"></span>
										<span v-if="dataInputs[item.id].truncated.exists" class="order-expand-details">
											<span @click.prevent="item._expanded = !item._expanded">
												<template v-if="item._expanded">
													<?= _x( 'Collapse &#9652;', 'timeline', 'voxel' ) ?>
												</template>
												<template v-else>
													<?= _x( 'Expand &#9662;', 'timeline', 'voxel' ) ?>
												</template>
											</span>
										</span>
									</div>
								</li>
							</template>
						</ul>
						<ul class="ts-cost-calculator simplify-ul flexify">
							<li v-if="order.pricing.subtotal !== null" class="ts-cost--subtotal">
								<div class="ts-item-name"><p><?= _x( 'Subtotal', 'single order', 'voxel' ) ?></p></div>
								<div class="ts-item-price"><p>{{ orders.currencyFormat( order.pricing.subtotal, order.pricing.currency ) }}</p></div>
							</li>
							<li v-if="order.pricing.discount_amount !== null" class="ts-cost--discount-amount">
								<div class="ts-item-name"><p><?= _x( 'Discount', 'single order', 'voxel' ) ?></p></div>
								<div class="ts-item-price"><p>{{ orders.currencyFormat( order.pricing.discount_amount, order.pricing.currency ) }}</p></div>
							</li>
							<li v-if="order.pricing.tax_amount !== null" class="ts-cost--tax-amount">
								<div class="ts-item-name"><p><?= _x( 'Tax', 'single order', 'voxel' ) ?></p></div>
								<div class="ts-item-price"><p>{{ orders.currencyFormat( order.pricing.tax_amount, order.pricing.currency ) }}</p></div>
							</li>
							<li v-if="order.pricing.shipping_amount !== null" class="ts-cost--shipping-amount">
								<div class="ts-item-name"><p><?= _x( 'Shipping', 'single order', 'voxel' ) ?></p></div>
								<div class="ts-item-price"><p>{{ orders.currencyFormat( order.pricing.shipping_amount, order.pricing.currency ) }}</p></div>
							</li>
							<li v-if="order.pricing.total !== null" class="ts-total">
								<div class="ts-item-name"><p><?= _x( 'Total', 'single order', 'voxel' ) ?></p></div>
								<div class="ts-item-price"><p>{{ orders.currencyFormat( order.pricing.total, order.pricing.currency ) }}</p></div>
							</li>
							<li v-if="order.pricing.total !== null && order.pricing.subscription_interval !== null" class="ts-cost--schedule">
								<div class="ts-item-name"></div>
								<div class="ts-item-price"><p><?= _x( 'Renews', 'single order', 'voxel' ) ?> {{ order.pricing.subscription_interval }}</p></div>
							</li>
						</ul>
						<details class="order-accordion" v-if="order.vendor.fees">
							<summary><?= _x( 'Vendor fees', 'single order', 'voxel' ) ?><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_down') ) ?: \Voxel\get_svg( 'chevron-down.svg' ) ?></summary>
							<div class="details-body">
								 <ul  class="ts-cost-calculator simplify-ul flexify ts-customer-details">
									<li v-for="fee in order.vendor.fees.breakdown" >
										<div class="ts-item-name"><p>{{ fee.label }}</p></div>
										<div class="ts-item-price"><p>{{ fee.content }}</p></div>
									</li>
									<li class="ts-total">
										<div class="ts-item-name"><p><?= _x( 'Total', 'single order', 'voxel' ) ?></p></div>
										<div class="ts-item-price"><p>{{ orders.currencyFormat( order.vendor.fees.total, order.pricing.currency ) }}</p></div>
									</li>
								</ul>
							</div>
						</details>
						<details class="order-accordion" v-if="order.customer.customer_details?.length">
							<summary><?= _x( 'Customer details', 'single order', 'voxel' ) ?><icon-down/></summary>
							<div class="details-body">
								 <ul  class="ts-cost-calculator simplify-ul flexify ts-customer-details">
									<li v-for="detail in order.customer.customer_details" >
										<div class="ts-item-name"><p>{{ detail.label }}</p></div>
										<div class="ts-item-price"><p>{{ detail.content }}</p></div>
									</li>
								</ul>
							</div>
						</details>
						<details class="order-accordion" v-if="order.customer.shipping_details?.length">
							<summary><?= _x( 'Shipping details', 'single order', 'voxel' ) ?><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_down') ) ?: \Voxel\get_svg( 'chevron-down.svg' ) ?></summary>
							<div class="details-body">
								 <ul class="ts-cost-calculator simplify-ul flexify ts-customer-details">
									<li v-for="detail in order.customer.shipping_details" >
										<div class="ts-item-name"><p>{{ detail.label }}</p></div>
										<div class="ts-item-price"><p>{{ detail.content }}</p></div>
									</li>
									<template v-if="order.shipping.rate">
										<li v-if="order.shipping.rate.label">
											<div class="ts-item-name"><p><?= _x( 'Ships via', 'single order', 'voxel' ) ?></p></div>
											<div class="ts-item-price"><p>{{ order.shipping.rate.label }}</p></div>
										</li>
										<li v-if="order.shipping.rate.delivery_estimate">
											<div class="ts-item-name"><p><?= _x( 'Delivery estimate', 'single order', 'voxel' ) ?></p></div>
											<div class="ts-item-price"><p>{{ order.shipping.rate.delivery_estimate }}</p></div>
										</li>
									</template>
								</ul>
							</div>
						</details>
						<details class="order-accordion" v-if="order.customer.order_notes?.length">
							<summary><?= _x( 'Order notes', 'single order', 'voxel' ) ?><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_down') ) ?: \Voxel\get_svg( 'chevron-down.svg' ) ?></summary>
							<div class="details-body">
								<p v-html="order.customer.order_notes" style="white-space: pre-wrap; word-break: break-word;"></p>
							</div>
						</details>
						<details class="order-accordion" v-if="order.vendor.notes_to_customer?.length">
							<summary><?= _x( 'Notes to customer', 'single order', 'voxel' ) ?><?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_down') ) ?: \Voxel\get_svg( 'chevron-down.svg' ) ?></summary>
							<div class="details-body">
								<p v-html="order.vendor.notes_to_customer" style="white-space: pre-wrap; word-break: break-word;"></p>
							</div>
						</details>
					</div>
				</div>
			</div>
		</div>
	</div>
</script>
