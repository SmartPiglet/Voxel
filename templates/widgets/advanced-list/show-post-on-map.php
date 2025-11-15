<?php
$current_post = \Voxel\get_current_post();
if ( ! $current_post ) {
	return;
}

$location_field = $current_post->get_field('location');
$location = $location_field ? $location_field->get_value() : [];
if ( ! ( is_numeric( $location['latitude'] ?? null ) && is_numeric( $location['longitude'] ?? null ) ) ) {
	return;
}

$href = 'javascript:void(0);';
foreach ( $current_post->post_type->get_filters() as $filter ) {
	if ( $filter->get_type() === 'location' ) {
		$href = esc_url( add_query_arg( [
			'type' => $current_post->post_type->get_key(),
			$filter->get_key() => sprintf( '%s;%s,%s,%s', $location['address'] ?? '', $location['latitude'], $location['longitude'], 12 ),
		], $current_post->post_type->get_archive_link() ) );
	}
}

?>
<?= $start_action ?>
<a href="<?= $href ?>" rel="nofollow" class="ts-action-con ts-action-show-on-map" data-post-id="<?= esc_attr( $current_post->get_id() ) ?>" <?php if (!empty($action['ts_acw_initial_text']) || !empty($action['ts_tooltip_text'])): ?> aria-label="<?= esc_attr( !empty($action['ts_acw_initial_text']) ? $action['ts_acw_initial_text'] : $action['ts_tooltip_text'] ) ?>"<?php endif ?>>
	<div class="ts-action-icon"><?php \Voxel\render_icon( $action['ts_acw_initial_icon'] ) ?></div>
	<?= $action['ts_acw_initial_text'] ?>
</a>
<?= $end_action ?>
