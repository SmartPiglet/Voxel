<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="order-shipping-details">
	<div class="order-event" :class="status.class">
		<div class="order-event-icon">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_box') ) ?: \Voxel\get_svg( 'box.svg' ) ?>
		</div>
		<b>{{ status.label }}</b>
		<span>{{ status.long_label }}</span>

		<div class="further-actions">
			<a v-if="parent.isVendor() && status.key === 'processing'" href="#" @click.prevent="markShipped" class="ts-btn ts-btn-1">
				<?= _x( 'Mark as shipped', 'single order', 'voxel' ) ?>
			</a>
			<template v-if="parent.isVendor() && status.key === 'shipped'">
				<form-group
					:popup-key="'share-tracking-link-'+order.id"
					wrapper-class="md-width"
					ref="trackingLink"
					@save="shareTrackingLink"
					@clear="$refs.trackingLink.blur()"
					clear-label="<?= esc_attr( _x( 'Cancel', 'share tracking details', 'voxel' ) ) ?>"
					save-label="<?= esc_attr( _x( 'Share', 'share tracking details', 'voxel' ) ) ?>"
				>
					<template #trigger>
						<a href="#" @click.prevent="$root.activePopup = 'share-tracking-link-'+order.id" class="ts-popup-target ts-btn ts-btn-1">
							<template v-if="order.shipping.tracking_details.link">
								<?= _x( 'Update tracking link', 'single order', 'voxel' ) ?>
							</template>
							<template v-else>
								<?= _x( 'Share tracking link', 'single order', 'voxel' ) ?>
							</template>
						</a>
					</template>
					<template #popup>
						<div class="ts-sticky-top uib b-bottom">

							<input type="url" v-model="share_details.tracking_link" placeholder="<?= esc_attr( _x( 'Tracking URL', 'share tracking details', 'voxel' ) ) ?>">
						</div>
					</template>
				</form-group>
			</template>
			<a v-if="parent.isCustomer() && status.key === 'shipped' && order.shipping.tracking_details.link" :href="order.shipping.tracking_details.link" target="_blank" class="ts-btn ts-btn-1">
				<?= _x( 'Track order', 'single order', 'voxel' ) ?>
			</a>
			<a v-if="status.key === 'shipped'" @click.prevent="markDelivered" href="#" class="ts-btn ts-btn-1">
				<?= _x( 'Mark as delivered', 'single order', 'voxel' ) ?>
			</a>
		</div>
	</div>
</script>
