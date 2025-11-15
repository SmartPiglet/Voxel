	<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

$post = \Voxel\get_current_post();
if ( ! ( is_user_logged_in() && $post && $post->promotions->is_promotable_by_user( \Voxel\get_current_user() ) ) ) {
	return;
}

$active_package = $post->promotions->get_active_package();
if ( $active_package && is_numeric( $active_package['order_id'] ?? null ) ):
	$order_link = add_query_arg(
		'order_id',
		$active_package['order_id'],
		get_permalink( \Voxel\get( 'templates.orders' ) ) ?: home_url('/')
	); ?>
	<li class="elementor-repeater-item-<?= $action['_id'] ?> flexify ts-action <?= $this->get_settings_for_display('ts_al_columns_no') ?>"
		<?php if ($action['ts_acw_enable_tooltip'] === 'yes'): ?>
			data-tooltip="<?= esc_attr( $action['ts_acw_tooltip_text'] ) ?>"
		<?php endif ?>
	>
		<a href="<?= esc_url( $order_link ) ?>" rel="nofollow" class="ts-action-con active">
			<span class="ts-reveal">
				<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_reveal_icon'] ) ?></div><?= $action['ts_acw_reveal_text'] ?>
			</span>
		</a>
	</li>
<?php else:
	$promote_link = add_query_arg( [
		'screen' => 'promote',
		'post_id' => $post->get_id(),
	], get_permalink( \Voxel\get( 'templates.checkout' ) ) ?: home_url('/') ); ?>
	<?= $start_action ?>
	<a href="<?= esc_url( $promote_link ) ?>" rel="nofollow" class="ts-action-con" role="button" <?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>>
		<span class="ts-initial">
			<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div><?= $action['ts_acw_initial_text'] ?>
		</span>
	</a>
	<?= $end_action ?>
<?php endif ?>
