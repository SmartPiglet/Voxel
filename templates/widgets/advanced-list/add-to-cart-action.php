<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

$post = \Voxel\get_current_post();
if ( ! $post ) {
	return;
}

$field = $post->get_field( 'product' );
if ( ! ( $field && $field->get_type() === 'product' ) ) {
	return;
}

try {
	$field->check_product_form_validity();
} catch ( \Exception $e ) {
	return;
} ?>

<?php if ( $field->supports_one_click_add_to_cart() ): ?>
	<li class="elementor-repeater-item-<?= $action['_id'] ?> flexify ts-action <?= $this->get_settings_for_display('ts_al_columns_no') ?>"
		<?php if ($action['ts_enable_tooltip'] === 'yes'): ?>
			data-tooltip="<?= esc_attr( $action['ts_tooltip_text'] ) ?>"
		<?php endif ?>
	>
		<a href="#" target="_blank" rel="nofollow" class="ts-action-con" data-product-id="<?= esc_attr( $post->get_id() ) ?>" onclick="Voxel.addToCartAction(event, this)" <?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>>
			<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div>
			<?= $action['ts_acw_initial_text'] ?>
		</a>
	</li>
<?php else: ?>
	<li class="elementor-repeater-item-<?= $action['_id'] ?> flexify ts-action <?= $this->get_settings_for_display('ts_al_columns_no') ?>"
		<?php if ($action['ts_cart_opts_enable_tooltip'] === 'yes'): ?>
			data-tooltip="<?= esc_attr( $action['ts_cart_opts_tooltip_text'] ) ?>"
		<?php endif ?>
	>
		<a href="<?= esc_url( $post->get_link() ) ?>" class="ts-action-con">
			<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_cart_opts_icon'] ) ?></div>
			<?= $action['ts_cart_opts_text'] ?>
		</a>
	</li>
<?php endif ?>
