<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-variation-bulk-settings">
	<div class="ts-form-group">
		<a href="#" class="ts-btn ts-btn-4 form-btn" @click.prevent="open = !open">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('cog_icon') ) ?: \Voxel\svg( 'cog.svg' ) ?>
			<span><?= _x( 'Bulk apply settings', 'product field variations', 'voxel' ) ?></span>
		</a>
	</div>
	<template v-if="open">
		<div class="ts-form-group vx-1-2">
			<label><?= _x( 'Copy settings', 'product field variation', 'voxel' ) ?></label>
			<div class="ts-filter">
				<select v-model="copy.what">
					<option value="price"><?= _x( 'Price', 'product field variations', 'voxel' ) ?></option>
					<option value="image"><?= _x( 'Image', 'product field variations', 'voxel' ) ?></option>
					<option v-if="variations.field.props.stock.enabled" value="stock"><?= _x( 'Stock', 'product field variations', 'voxel' ) ?></option>
					<option value="all"><?= _x( 'All', 'product field variations', 'voxel' ) ?></option>
				</select>
				<div class="ts-down-icon"></div>
			</div>
		</div>
		<div class="ts-form-group vx-1-2">
			<label><?= _x( 'To variations', 'product field variation', 'voxel' ) ?></label>
			<div class="ts-filter">
				<select v-model="copy.where">
					<option v-for="choice in getWhereChoices()" :value="choice.key">
						<?= \Voxel\replace_vars( _x( 'All in @category', 'product field variations', 'voxel' ), [
							'@category' => '{{ choice.label }}',
						] ) ?>
					</option>
					<option value="all"><?= _x( 'All', 'product field variations', 'voxel' ) ?></option>
				</select>
				<div class="ts-down-icon"></div>
			</div>
		</div>
		<div class="ts-form-group vx-1-1">
			<a href="#" @click.prevent="apply" class="ts-btn ts-btn-2 form-btn">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('apply_icon') ) ?: \Voxel\svg( 'checkmark-circle.svg' ) ?>
				<?= _x( 'Apply', 'product field variations', 'voxel' ) ?>
			</a>
		</div>
	</template>
</script>
