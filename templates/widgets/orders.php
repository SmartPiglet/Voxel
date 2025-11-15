<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

require_once locate_template( 'templates/widgets/orders/single-order.php' );

?>
<script type="text/json" class="vxconfig__icons"><?= wp_json_encode( $icons ) ?></script>
<div class="vx-orders-widget vx-order-ease" data-config="<?= esc_attr( wp_json_encode( $config ) ) ?>">
	<template v-if="order.id">
		<template v-if="order.loading && !order.item">
			<div class="ts-no-posts">
				<span class="ts-loader"></span>
			</div>
		</template>
		<template v-else>
			<single-order :order="order.item" :orders="this"></single-order>
		</template>
	</template>
	<template v-else>
		<div class="widget-head">
			<h1><?php echo $this->get_settings_for_display( 'orders_title' ); ?></h1>
			<p><?php echo $this->get_settings_for_display( 'orders_subtitle' ); ?></p>
		</div>

		<template v-if="config.available_statuses.length">
			<div class="vx-order-filters ts-form">
				<div class="ts-form-group ts-inline-filter order-keyword-search">
				  <div class="ts-input-icon flexify">
				    <?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_search_icon') ) ?: \Voxel\svg( 'search.svg' ) ?>
				    <input
				    	type="text"
				    	placeholder="<?= esc_attr( _x( 'Search orders', 'orders', 'voxel' ) ) ?>"
				    	class="inline-input"
				    	:value="query.search"
						@keyup.enter="setSearch($event.target.value)"
						@blur="setSearch($event.target.value)"
				    >
				  </div>
				</div>

				<form-group popup-key="status" class="order-status-search" ref="status" @clear="setStatus('all'); $refs.status.blur();" @save="$refs.status.blur()">
					<template #trigger>
						<div
							class="ts-filter ts-popup-target"
							:class="{'ts-filled': query.status !== 'all'}"
							@mousedown="$root.activePopup = 'status'"
						>
							<div v-if="query.status === 'all'" class="ts-filter-text">
								<?= _x( 'Status', 'orders', 'voxel' ) ?>
							</div>
							<div v-else class="ts-filter-text">
								{{ config.statuses[ query.status ]?.label || query.status }}
							</div>
							<div class="ts-down-icon"></div>
						</div>
					</template>
					<template #popup>
						<div class="ts-term-dropdown ts-md-group">
							<ul class="simplify-ul ts-term-dropdown-list min-scroll">
								<template v-for="status, status_key in config.statuses">
									<li v-if="status_key !== 'pending_payment' && config.available_statuses.includes(status_key)">
										<a href="#" class="flexify" @click.prevent="setStatus(status_key); $refs.status.blur();">
											<div class="ts-checkbox-container">
												<label class="container-radio">
													<input type="radio" :value="status_key"
														:checked="query.status === status_key" disabled hidden
													>
													<span class="checkmark"></span>
												</label>
											</div>
											<span>{{ status.label }}</span>
										</a>
									</li>
								</template>
							</ul>
						</div>
					</template>
				</form-group>

				<template v-if="config.available_shipping_statuses.length">
					<form-group popup-key="shipping_status" class="order-shipping-status-search" ref="shipping_status" @clear="setShippingStatus('all'); $refs.shipping_status.blur();" @save="$refs.shipping_status.blur()">
						<template #trigger>
							<div
								class="ts-filter ts-popup-target"
								:class="{'ts-filled': query.shipping_status !== 'all'}"
								@mousedown="$root.activePopup = 'shipping_status'"
							>
								<div v-if="query.shipping_status === 'all'" class="ts-filter-text">
									<?= _x( 'Shipping', 'orders', 'voxel' ) ?>
								</div>
								<div v-else class="ts-filter-text">
									{{ config.shipping_statuses[ query.shipping_status ]?.label || query.shipping_status }}
								</div>
								<div class="ts-down-icon"></div>
							</div>
						</template>
						<template #popup>
							<div class="ts-term-dropdown ts-md-group">
								<ul class="simplify-ul ts-term-dropdown-list min-scroll">
									<template v-for="status, status_key in config.shipping_statuses">
										<li v-if="config.available_shipping_statuses.includes(status_key)">
											<a href="#" class="flexify" @click.prevent="setShippingStatus(status_key); $refs.shipping_status.blur();">
												<div class="ts-checkbox-container">
													<label class="container-radio">
														<input type="radio" :value="status_key"
															:checked="query.shipping_status === status_key" disabled hidden
														>
														<span class="checkmark"></span>
													</label>
												</div>
												<span>{{ status.label }}</span>
											</a>
										</li>
									</template>
								</ul>
							</div>
						</template>
					</form-group>
				</template>

				<template v-if="Object.keys(config.product_types).length">
					<form-group popup-key="product_type" class="order-product-type-search" ref="product_type" @clear="setProductType('all'); $refs.product_type.blur();" @save="$refs.product_type.blur()">
						<template #trigger>
							<div
								class="ts-filter ts-popup-target"
								:class="{'ts-filled': query.product_type !== 'all'}"
								@mousedown="$root.activePopup = 'product_type'"
							>
								<div v-if="query.product_type === 'all'" class="ts-filter-text">
									<?= _x( 'Product type', 'orders', 'voxel' ) ?>
								</div>
								<div v-else class="ts-filter-text">
									{{ config.product_types[ query.product_type ]?.label || query.product_type }}
								</div>
								<div class="ts-down-icon"></div>
							</div>
						</template>
						<template #popup>
							<div class="ts-term-dropdown ts-md-group">
								<ul class="simplify-ul ts-term-dropdown-list min-scroll">
									<template v-for="product_type in config.product_types">
										<li>
											<a href="#" class="flexify" @click.prevent="setProductType(product_type.key); $refs.product_type.blur();">
												<div class="ts-checkbox-container">
													<label class="container-radio">
														<input type="radio" :value="product_type.key"
															:checked="query.product_type === product_type.key" disabled hidden
														>
														<span class="checkmark"></span>
													</label>
												</div>
												<span>{{ product_type.label }}</span>
											</a>
										</li>
									</template>
								</ul>
							</div>
						</template>
					</form-group>
				</template>

				<div class="ts-form-group order-reset-button">
					 <a href="#" class="ts-filter" @click.prevent="resetFilters">
					 	<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_reset_search') ) ?: \Voxel\svg( 'reload.svg' ) ?>
					 </a>
				</div>
			</div>
		</template>

		<div v-if="query.is_initial_load && query.loading" class="ts-no-posts">
			<span class="ts-loader"></span>
		</div>
		<template v-else-if="query.items.length">
			<div class="vx-order-ease">
				<div class="vx-order-list" :class="{'vx-disabled': query.loading}">
					<template v-for="order in query.items">
						<div class="vx-order-card" :class="'vx-status-'+order.status" @click.prevent="viewOrder(order.id)">
							<div class="vx-order-meta vx-order-title">
								<div v-if="order.customer.avatar" class="vx-avatar" v-html="order.customer.avatar"></div>
								<span class="order-badge vx-hide-mobile">
									#{{ order.id }}
								</span>
								<b><?= \Voxel\replace_vars( _x( '@customer_name submitted an order @date', 'orders', 'voxel' ), [
									'@customer_name' => '{{ order.customer.name }}',
									'@date' => '{{ order.created_at }}',
								] ) ?></b>
							</div>
							<div class="vx-order-meta">
								<span v-if="order.item_count > 1" class="vx-hide-mobile">
									<?= \Voxel\replace_vars( _x( '@count items', 'orders', 'voxel' ), [
										'@count' => '{{ order.item_count }}',
									] ) ?>
								</span>
								<span v-if="typeof order.total === 'number'" class="vx-hide-mobile">{{ currencyFormat( order.total, order.currency ) }}</span>
								<span v-else-if="typeof order.subtotal === 'number'" class="vx-hide-mobile">{{ currencyFormat( order.subtotal, order.currency ) }}</span>
							</div>
							<div v-if="order.shipping_status !== null" class="order-status" :class="config.shipping_statuses[ order.shipping_status ]?.class || 'vx-neutral'">
								{{ config.shipping_statuses[ order.shipping_status ]?.label || order.shipping_status }}
							</div>
							<div v-else class="order-status" :class="config.statuses_ui[ order.status ]?.class || 'vx-neutral'">
								{{ config.statuses[ order.status ]?.label || order.status }}
							</div>
						</div>
					</template>
				</div>
			</div>

			<div class="vx-order-more" :class="{hidden: query.pg < 2 && !query.has_more, 'vx-inert': query.loading}">
				<a href="#" @click.prevent="previousPage" class="ts-load-more ts-btn ts-btn-1" :class="{'vx-disabled': query.pg < 2}">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_back') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?>
					<?= __( 'Previous', 'voxel' ) ?>
				</a>
				<a href="#" @click.prevent="nextPage" class="ts-load-more ts-btn ts-btn-1" :class="{'vx-disabled': !query.has_more}">
					<?= __( 'Next', 'voxel' ) ?>
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_forward') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
				</a>
			</div>
		</template>
		<div v-else class="ts-no-posts">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_noresults_icon') ) ?: \Voxel\svg( 'keyword-research.svg' ) ?>
			<p><?= _x( 'No orders found', 'orders', 'voxel' ) ?></p>
		</div>
	</template>
</div>

<script type="text/html" id="vx-orders-file-upload">
	<a href="#" @click.prevent="$refs.input.click()" class="ts-btn ts-btn-1"><?= _x( 'Upload file', 'order downloads', 'voxel' ) ?></a>
	<input ref="input" type="file" class="hidden" :multiple="maxFileCount > 1" :accept="allowedFileTypes">
</script>
