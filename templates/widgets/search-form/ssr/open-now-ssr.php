<?php $value = $this->parse_value( $this->get_value() ) ?>

<?php if ( ( $this->elementor_config['open_in_popup'] ?? null ) === 'yes' ): ?>
	<div v-if="false" class="<?= $args['wrapper_class'] ?>">
		<?php if ( ! empty( $args['show_labels'] ) ): ?>
			<label><?= $this->get_label() ?></label>
		<?php endif ?>
		<div class="ts-filter ts-popup-target <?= $value ? 'ts-filled' : '' ?>">
			<span><?= \Voxel\get_icon_markup( $this->get_icon() ) ?></span>
			<div class="ts-filter-text"><?= $this->props['placeholder'] ?: $this->props['label'] ?></div>
		</div>
	</div>
<?php else: ?>
	<div v-if="false" class="<?= $args['wrapper_class'] ?> switcher-label ts-inline-filter">
		<label>
			<div class="switch-slider">
				<div class="onoffswitch">
					<input type="checkbox" class="onoffswitch-checkbox" <?= !! checked( $value ) ?>>
					<label class="onoffswitch-label"></label>
				</div>
			</div>
			<?= $this->props['placeholder'] ?: $this->props['label'] ?>
		</label>
	</div>
<?php endif ?>
