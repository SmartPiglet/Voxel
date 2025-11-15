<?php
$current_post = \Voxel\get_current_post();
if ( ! ( $current_post && $current_post->is_editable_by_current_user() ) ) {
	return;
}

if ( ! ( $current_post->post_type && $current_post->post_type->is_tracking_enabled() ) ) {
	return;
} ?>

<?= $start_action ?>
<a href="<?= esc_url( add_query_arg( 'post_id', $current_post->get_id(), get_permalink( \Voxel\get('templates.post_stats') ) ?: home_url('/') ) ) ?>" rel="nofollow" class="ts-action-con" <?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>>
	<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div>
	<?= $action['ts_acw_initial_text'] ?>
</a>
<?= $end_action ?>
