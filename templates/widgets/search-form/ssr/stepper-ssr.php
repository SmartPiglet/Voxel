<?php $value = $this->parse_value( $this->get_value() ) ?>

<?php if ( ( $this->elementor_config['display_as'] ?? 'popup' ) === 'inline' ): ?>
	<div v-if="false" class="<?= $args['wrapper_class'] ?> ts-inline-filter">
		<?php if ( ! empty( $args['show_labels'] ) ): ?>
			<label><?= $this->get_label() ?></label>
		<?php endif ?>
		<div class="ts-stepper-input flexify">
			<button class="ts-stepper-left ts-icon-btn inline-btn-ts">
				<?= \Voxel\get_icon_markup( $this->search_widget->get_settings_for_display('ts_minus_icon') ) ?: \Voxel\svg( 'minus.svg' ) ?>
			</button>
			<input
				type="number"
				class="ts-input-box"
				placeholder="<?= esc_attr( _x( 'Any', 'stepper filter', 'voxel' ) ) ?>"
				value="<?= $value !== null ? $value : '' ?>"
			>
			<button class="ts-stepper-right ts-icon-btn inline-btn-ts">
				<?= \Voxel\get_icon_markup( $this->search_widget->get_settings_for_display('ts_plus_icon') ) ?: \Voxel\svg( 'plus.svg' ) ?>
			</button>
		</div>
	</div>
<?php else: ?>
	<div v-if="false" class="<?= $args['wrapper_class'] ?>">
		<?php if ( ! empty( $args['show_labels'] ) ): ?>
			<label><?= $this->get_label() ?></label>
		<?php endif ?>
		<div class="ts-filter ts-popup-target <?= $value ? 'ts-filled' : '' ?>">
			<span><?= \Voxel\get_icon_markup( $this->get_icon() ) ?></span>
			<div class="ts-filter-text"><?= $value ?? ( ! empty( $this->props['placeholder'] ) ? $this->props['placeholder'] : $this->get_label() ) ?></div>
			<div class="ts-down-icon"></div>
		</div>
	</div>
<?php endif ?>
