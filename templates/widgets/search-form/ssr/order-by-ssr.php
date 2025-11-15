<?php
$value = $this->parse_value( $this->get_value() );
$key = $value['key'] ?? null;
$choices = $this->_get_selected_choices();
$label = isset( $choices[ $key ] ) ? $choices[ $key ]['label'] : null;
?>

<?php if ( ( $this->elementor_config['display_as'] ?? null ) === 'buttons' ): ?>
	<div class="ts-form-group <?= $args['wrapper_class'] ?>">
		<?php if ( ! empty( $args['show_labels'] ) ): ?>
			<label><?= $this->get_label() ?></label>
		<?php endif ?>
		<ul class="simplify-ul addon-buttons flexify">
			<?php foreach ( $choices as $choice ): ?>
				<li class="flexify <?= $key === $choice['key'] ? 'adb-selected' : '' ?>">
					<?= esc_attr( $choice['placeholder'] ?: $choice['label'] ) ?>
				</li>
			<?php endforeach ?>
		</ul>
	</div>
<?php elseif ( ( $this->elementor_config['display_as'] ?? null ) === 'alt-btn' ): ?>
	<?php foreach ( $choices as $choice ): ?>
		<div class="ts-form-group <?= $args['wrapper_class'] ?>">
			<?php if ( ! empty( $args['show_labels'] ) ): ?>
				<label><?= esc_attr( $choice['label'] ) ?></label>
			<?php endif ?>
			<div class="ts-filter <?= $key === $choice['key'] ? 'ts-filled' : '' ?>">
				<span><?= \Voxel\get_icon_markup( $choice['icon'] ) ?></span>
				<div class="ts-filter-text">
					<span><?= esc_attr( $choice['placeholder'] ?: $choice['label'] ) ?></span>
				</div>
			</div>
		</div>
	<?php endforeach ?>
<?php elseif ( ( $this->elementor_config['display_as'] ?? null ) === 'post-feed' ): ?>
<?php else: ?>
	<div v-if="false" class="<?= $args['wrapper_class'] ?>">
		<?php if ( ! empty( $args['show_labels'] ) ): ?>
			<label><?= $this->get_label() ?></label>
		<?php endif ?>
		<div class="ts-filter ts-popup-target <?= $key ? 'ts-filled' : '' ?>">
			<span><?= \Voxel\get_icon_markup( $this->get_icon() ) ?></span>
			<div class="ts-filter-text"><?= $key ? $label : ( $this->props['placeholder'] ?: $this->props['label'] ) ?></div>
			<div class="ts-down-icon"></div>
		</div>
	</div>
<?php endif;