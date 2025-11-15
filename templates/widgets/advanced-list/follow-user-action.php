<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

if ( ! \Voxel\get( 'settings.timeline.enabled', true ) ) {
	return;
}

$current_post = \Voxel\get_current_post();
$author_id = $current_post ? $current_post->get_author_id() : null;
$status = is_user_logged_in() ? \Voxel\current_user()->get_follow_status( 'user', $author_id ) : null;
$is_active = $status === \Voxel\FOLLOW_ACCEPTED;
$is_intermediate = $status === \Voxel\FOLLOW_REQUESTED;
?>
<li class="elementor-repeater-item-<?= $action['_id'] ?> flexify ts-action <?= $this->get_settings_for_display('ts_al_columns_no') ?>"
	<?php if ($action['ts_enable_tooltip'] === 'yes' && ! empty( $action['ts_tooltip_text'] ) ): ?>
		tooltip-inactive="<?= esc_attr( $action['ts_tooltip_text'] ) ?>"
	<?php endif ?>
	<?php if ($action['ts_acw_enable_tooltip'] === 'yes' && ! empty( $action['ts_acw_tooltip_text'] ) ): ?>
		tooltip-active="<?= esc_attr( $action['ts_acw_tooltip_text'] ) ?>"
	<?php endif ?>
>
	<a
		href="<?= esc_url( add_query_arg( [
			'vx' => 1,
			'action' => 'user.follow_user',
			'user_id' => $author_id,
			'_wpnonce' => wp_create_nonce( 'vx_user_follow' ),
		], home_url( '/' ) ) ) ?>"
		rel="nofollow"
		class="ts-action-con ts-action-follow <?= $is_active ? 'active' : '' ?> <?= $is_intermediate ? 'intermediate' : '' ?>" role="button" <?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>>
		<span class="ts-initial">
			<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div><?= $action['ts_acw_initial_text'] ?>
		</span>

		<!--Reveal span when action is clicked (active class is added to the li) -->
		<span class="ts-reveal">
			<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_reveal_icon'] ) ?></div><?= $action['ts_acw_reveal_text'] ?>
		</span>
	</a>
</li>