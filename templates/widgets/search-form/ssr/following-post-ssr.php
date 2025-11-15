<?php
$value = $this->parse_value( $this->get_value() );
if ( ! $value ) {
	return;
}
?>
<div v-if="false" class="<?= $args['wrapper_class'] ?>">
	<?php if ( ! empty( $args['show_labels'] ) ): ?>
		<label><?= $this->get_label() ?></label>
	<?php endif ?>
	<div class="ts-filter ts-popup-target <?= $value ? 'ts-filled' : '' ?>">
		<span><?= \Voxel\get_icon_markup( $this->get_icon() ) ?></span>
		<div class="ts-filter-text">#<?= esc_html( $value ) ?></div>
	</div>
</div>