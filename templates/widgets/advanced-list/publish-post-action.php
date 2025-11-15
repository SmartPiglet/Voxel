<?php
$current_post = \Voxel\get_current_post();
if ( ! ( $current_post && $current_post->is_editable_by_current_user() ) ) {
	return;
}

if ( $current_post->get_status() !== 'unpublished' ) {
	return;
} ?>

<?= $start_action ?>
<a
	href="<?= esc_url( wp_nonce_url( home_url( '/?vx=1&action=user.posts.republish_post&post_id='.$current_post->get_id() ), 'vx_modify_post' ) ) ?>"
	vx-action
	rel="nofollow"
	class="ts-action-con"
	<?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>
>
	<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div>
	<?= $action['ts_acw_initial_text'] ?>
</a>
<?= $end_action ?>
