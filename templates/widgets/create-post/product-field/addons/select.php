<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-addon-select">
	<div class="ts-form-group switcher-label">
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
			<div class="ts-repeater-container">
				<template v-for="choice in addon.props.choices">
					<div class="ts-field-repeater" :class="{collapsed: active !== choice}">
						<div class="ts-repeater-head" @click.prevent="active = choice === active ? null : choice">
							<?= \Voxel\get_icon_markup( $this->get_settings_for_display('handle_icon') ) ?: \Voxel\svg( 'handle.svg' ) ?>
							<label>
								{{ choice.label }}
							</label>
							<template v-if="typeof value.choices[ choice.value ].price === 'number'">
								<em>{{ $root.currencyFormat( value.choices[ choice.value ].price ) }}</em>
							</template>
							<template v-else>
								<em><?= _x( 'No price added', 'product field addons', 'voxel' ) ?></em>
							</template>
							<div class="ts-repeater-controller">
								<a href="#" class="ts-icon-btn ts-smaller" @click.prevent>
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('down_icon') ) ?: \Voxel\svg( 'chevron-down.svg' ) ?>
								</a>
							</div>
						</div>

						<div class="medium form-field-grid">
							<div class="ts-form-group">
								<label><?= _x( 'Price', 'product field', 'voxel' ) ?></label>
								<div class="input-container">
									<input type="number" v-model="value.choices[ choice.value ].price" class="ts-filter" min="0" placeholder="<?= esc_attr( _x( 'Add price', 'product field', 'voxel' ) ) ?>">
									<span class="input-suffix"><?= \Voxel\get_primary_currency() ?></span>
								</div>
							</div>
						</div>
					</div>
				</template>
			</div>
		</template>
	</div>
</script>
