<?php $value = $this->parse_value( $this->get_value() ) ?>

<?php if ( ( $this->elementor_config['display_as'] ?? 'popup' ) === 'inline' ): ?>
	<div v-if="false" class="<?= $args['wrapper_class'] ?> ts-inline-filter">
		<?php if ( ! empty( $args['show_labels'] ) ): ?>
			<label><?= $this->get_label() ?></label>
		<?php endif ?>
		<div class="flexify ts-input-icon">
			<span><?= \Voxel\get_icon_markup( $this->get_icon() ) ?></span>
			<input type="text" placeholder="<?= esc_attr( $this->props['placeholder'] ?: $this->props['label'] ) ?>" value="<?= esc_attr( $value ) ?>" class="inline-input">
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
		</div>
	</div>
<?php endif ?>
