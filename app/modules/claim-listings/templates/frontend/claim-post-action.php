<?php

use \Voxel\Modules\Claim_Listings as Module;

if ( ! defined('ABSPATH') ) {
	exit;
}

$post = \Voxel\get_current_post();
if ( ! ( $post && Module\is_claimable( $post ) ) ) {
	return;
}

if ( is_user_logged_in() ) {
	$current_user = \Voxel\get_current_user();

	if ( (int) $current_user->get_id() === (int) $post->get_author_id() ) {
		return;
	}
}

$redirect_to = add_query_arg( [
	'process' => 'claim',
	'post_id' => $post->get_id(),
], get_permalink( \Voxel\get('paid_listings.settings.templates.pricing') ) );
?>
<?= $action['li_start'] ?>
<a
	href="<?= esc_url( $redirect_to ) ?>"
	rel="nofollow"
	class="ts-action-con"
	role="button"
	<?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?>
		aria-label="<?= esc_attr( ! empty( $action['ts_acw_initial_text'] )
			? $action['ts_acw_initial_text']
			: $action['ts_tooltip_text'] ) ?>"
	<?php endif ?>
>
	<span class="ts-initial">
		<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div><?= $action['ts_acw_initial_text'] ?>
	</span>
</a>
<?= $action['li_end'] ?>
