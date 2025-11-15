<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-stock">
	<div class="ts-form-group switcher-label">
		<label>
			<div class="switch-slider">
				<div class="onoffswitch">
					<input type="checkbox" class="onoffswitch-checkbox" v-model="value.enabled">
					<label class="onoffswitch-label" @click.prevent="value.enabled = !value.enabled"></label>
				</div>
			</div>
			<?= _x( 'Manage stock', 'product field stock', 'voxel' ) ?>
		</label>
		<div v-if="value.enabled" class="ts-field-repeater">
			<div class="medium form-field-grid">
				<div class="ts-form-group vx-1-2">
					<label><?= _x( 'Stock', 'product field stock', 'voxel' ) ?></label>
					<input type="number" v-model="value.quantity" class="ts-filter" min="0" placeholder="<?= esc_attr( _x( 'Set quantity', 'product field stock', 'voxel' ) ) ?>">
				</div>
				<div v-if="field.props.sku.enabled" class="ts-form-group vx-1-2">
					<label><?= _x( 'SKU', 'product field stock', 'voxel' ) ?></label>
					<input type="text" class="ts-filter" v-model="value.sku" placeholder="<?= esc_attr( _x( 'Stock-keeping unit', 'product field stock', 'voxel' ) ) ?>">
				</div>
				<div class="ts-form-group switcher-label">
					<label>
						<div class="switch-slider">
							<div class="onoffswitch">
								<input type="checkbox" class="onoffswitch-checkbox" v-model="value.sold_individually">
								<label class="onoffswitch-label" @click.prevent="value.sold_individually = !value.sold_individually"></label>
							</div>
						</div>
						<?= _x( 'Limit purchases to 1 item per order', 'product field stock', 'voxel' ) ?>
					</label>
				</div>

			</div>
		</div>
	</div>
</script>
