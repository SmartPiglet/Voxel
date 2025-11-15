<?php
if ( ! defined('ABSPATH') ) {
	exit;
}

$current_post = \Voxel\get_current_post();
$link = \Voxel\Utils\Sharer::get_google_calendar_link( [
	'start' => $action['ts_action_cal_start_date'],
	'end' => $action['ts_action_cal_end_date'],
	'title' => $action['ts_action_cal_title'],
	'description' => $action['ts_action_cal_desc'],
	'location' => $action['ts_action_cal_location'],
	'timezone' => $current_post ? $current_post->get_timezone()->getName() : wp_timezone()->getName(),
] );

if ( ! $link ) {
	return;
}
?>
<?= $start_action ?>
<a href="<?= esc_url( $link ) ?>" target="_blank" rel="nofollow" class="ts-action-con" <?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>>
	<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div>
	<?= $action['ts_acw_initial_text'] ?>
</a>
<?= $end_action ?>
