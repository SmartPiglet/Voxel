<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

$current_post = \Voxel\get_current_post();
if ( ! ( $current_post && $current_post->is_editable_by_current_user() ) ) {
	return;
}

if ( $current_post->get_status() !== 'expired' ) {
	return;
} ?>

<?= $action['li_start'] ?>
<a
	href="<?= esc_url( wp_nonce_url( home_url( '/?vx=1&action=paid_listings.relist_post&post_id='.$current_post->get_id() ), 'vx_relist_post' ) ) ?>"
	vx-action
	rel="nofollow"
	class="ts-action-con"
	<?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>
>
	<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div>
	<?= $action['ts_acw_initial_text'] ?>
</a>
<?= $action['li_end'] ?>
