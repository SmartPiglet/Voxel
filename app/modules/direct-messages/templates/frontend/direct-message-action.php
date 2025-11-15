<?php
$current_post = \Voxel\get_current_post();
if ( ! ( $current_post && $current_post->can_send_messages() ) ) {
	return;
}
?>
<?= $action['li_start'] ?>
<a href="<?= esc_url( add_query_arg( 'chat', 'p'.$current_post->get_id(), get_permalink( \Voxel\get('templates.inbox') ) ?: home_url('/') ) ) ?>" rel="nofollow" class="ts-action-con" onclick="Voxel.requireAuth(event)" <?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>>
	<span class="ts-initial">
		<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div><?= $action['ts_acw_initial_text'] ?>
	</span>
</a>
<?= $action['li_end'] ?>