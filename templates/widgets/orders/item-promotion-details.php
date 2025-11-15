<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="order-item-promotion-details">
	<div class="order-event">
		<div class="order-event-icon vx-blue">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_info') ) ?: \Voxel\get_svg( 'info.svg' ) ?>
		</div>

		<template v-if="details.status === 'canceled'">
			<b><?= _x( 'Promotion canceled', 'single order', 'voxel' ) ?></b>
			<span v-if="hasDates">{{ getDates }}</span>
		</template>
		<template v-else-if="details.status === 'ended'">
			<b><?= _x( 'Promotion has ended', 'single order', 'voxel' ) ?></b>
			<span v-if="hasDates">{{ getDates }}</span>
		</template>
		<template v-else-if="details.status === 'active'">
			<template v-if="details.assigned_to_post">
				<b><?= _x( 'Promotion is active', 'single order', 'voxel' ) ?></b>
				<span v-if="hasDates">{{ getDates }}</span>
			</template>
			<template v-else>
				<b><?= _x( 'Promotion details', 'single order', 'voxel' ) ?></b>
				<span v-if="hasDates">{{ getDates }}</span>
			</template>
		</template>

		<div class="further-actions">
			<a v-if="details.post_link" :href="details.post_link" target="_blank" class="ts-btn ts-btn-1">
				<?= _x( 'View listing', 'single order', 'voxel' ) ?>
			</a>
			<a v-if="details.stats_link" :href="details.stats_link" target="_blank" class="ts-btn ts-btn-1">
				<?= _x( 'View stats', 'single order', 'voxel' ) ?>
			</a>
			<a v-if="details.status === 'active' && details.assigned_to_post" href="#" @click.prevent="cancelPromotion" class="ts-btn ts-btn-1">
				<?= _x( 'Cancel promotion', 'single order', 'voxel' ) ?>
			</a>
		</div>
	</div>
</script>
