<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-addon-custom-select">
	<div class="ts-form-group switcher-label" ref="formGroup">
		<template v-if="!addon.required">
			<label>
				<div class="switch-slider">
					<div class="onoffswitch">
						<input type="checkbox" class="onoffswitch-checkbox" v-model="value.enabled">
						<label class="onoffswitch-label" @click.prevent="value.enabled = !value.enabled"></label>
					</div>
				</div>
				{{ addon.label }}
				<div class="vx-dialog" v-if="addon.description">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
					<div class="vx-dialog-content min-scroll">
						<p>{{ addon.description }}</p>
					</div>
				</div>
			</label>
		</template>
		<template v-else>
			<label>
				{{ addon.label }}
				<div class="vx-dialog" v-if="addon.description">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
					<div class="vx-dialog-content min-scroll">
						<p>{{ addon.description }}</p>
					</div>
				</div>
			</label>
		</template>
		<template v-if="addon.required || value.enabled">
			<draggable
				v-model="list"
				:group="'choices:'+addon_id"
				handle=".ts-repeater-head"
				filter=".no-drag"
				item-key="id"
				class="ts-repeater-container"
			>
				<template #item="{element: choice, index: index}">
					<div class="ts-field-repeater" :class="{collapsed: active !== choice}">
						<div class="ts-repeater-head" @click.prevent="active = choice === active ? null : choice">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('handle_icon') ) ?: \Voxel\svg( 'handle.svg' ) ?>
							<label>
								{{ choice.value || <?= wp_json_encode( _x( 'Untitled', 'product field', 'voxel' ) ) ?> }}
							</label>
							<template v-if="typeof choice.price === 'number'">
								<em>{{ $root.currencyFormat( choice.price ) }}</em>
							</template>
							<template v-else>
								<em><?= _x( 'No price added', 'product field addons', 'voxel' ) ?></em>
							</template>
							<div class="ts-repeater-controller">
								<a href="#" @click.stop.prevent="deleteChoice(choice)" class="ts-icon-btn ts-smaller no-drag">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('trash_icon') ) ?: \Voxel\svg( 'trash-can.svg' ) ?>
								</a>
								<a href="#" class="ts-icon-btn ts-smaller no-drag" @click.prevent>
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
								</a>
							</div>
						</div>

						<div class="medium form-field-grid">
							<div class="ts-form-group ts-choice-label vx-1-2">
								<label><?= _x( 'Label', 'product field', 'voxel' ) ?></label>
								<div class="input-container">
									<input type="text" v-model="choice.value" class="ts-filter">
								</div>
							</div>
							<div class="ts-form-group vx-1-2">
								<label><?= _x( 'Price', 'product field', 'voxel' ) ?></label>
								<div class="input-container">
									<input type="number" v-model="choice.price" class="ts-filter" min="0" placeholder="<?= esc_attr( _x( 'Add price', 'product field', 'voxel' ) ) ?>">
									<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
								</div>
							</div>
							<div v-if="addon.props.display_mode === 'cards'" class="ts-form-group">
								<label><?= _x( 'Subheading', 'product field', 'voxel' ) ?></label>
								<div class="input-container">
									<input type="text" v-model="choice.subheading" class="ts-filter">
								</div>
							</div>
							<template v-if="['cards','images'].includes( addon.props.display_mode )">
								<div class="ts-form-group">
									<label><?= _x( 'Image', 'product field addons', 'voxel' ) ?></label>
									<file-upload :key="choice" v-model="choice.image" allowed-file-types="image/jpeg,image/png,image/webp"></file-upload>
								</div>
							</template>
							<template v-if="['cards','radio','checkboxes'].includes( addon.props.display_mode )">
								<div class="ts-form-group vx-1-1 switcher-label">
									<label>
										<div class="switch-slider">
											<div class="onoffswitch">
												<input type="checkbox" class="onoffswitch-checkbox" v-model="choice.quantity.enabled">
												<label class="onoffswitch-label" @click.prevent="choice.quantity.enabled = !choice.quantity.enabled"></label>
											</div>
										</div>
										<?= _x( 'Sold in bulk', 'product field addons', 'voxel' ) ?>
										<div class="vx-dialog">
											<?= \Voxel\get_icon_markup( $this->get_settings_for_display('info_icon') ) ?: \Voxel\svg( 'info.svg' ) ?>
											<div class="vx-dialog-content min-scroll">
												<p><?= _x( 'Multiple units of this item can be purchasd in the same order', 'product field addons', 'voxel' ) ?></p>
											</div>
										</div>
									</label>
								</div>
								<template v-if="choice.quantity.enabled">
									<div class="ts-form-group vx-1-2">
										<label><?= _x( 'Minimum', 'product field addons', 'voxel' ) ?></label>
										<input type="number" v-model="choice.quantity.min" class="ts-filter" min="0" placeholder="<?= _x( 'Min', 'product field', 'voxel' ) ?>">
									</div>
									<div class="ts-form-group vx-1-2">
										<label><?= _x( 'Maximum', 'product field addons', 'voxel' ) ?></label>
										<input type="number" v-model="choice.quantity.max" class="ts-filter" min="0" placeholder="<?= _x( 'Max', 'product field', 'voxel' ) ?>">
									</div>
								</template>
							</template>
						</div>
					</div>
				</template>
			</draggable>

			<a href="#" @click.prevent="insertChoice" class="ts-repeater-add ts-btn ts-btn-4 form-btn">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_add_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
				<?= _x( 'Add option', 'product field', 'voxel' ) ?>
			</a>
		</template>
	</div>
</script>
