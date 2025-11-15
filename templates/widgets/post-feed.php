<?php
/**
 * Post feed template.
 *
 * @since 1.0
 */
?>

<?php if ( isset( $results['total_count'] ) ): ?>
	<div class="post-feed-header">
		<span class="result-count <?= $results['total_count'] === 0 ? 'hidden' : '' ?>">
			<?= \Voxel\count_format( count( $results['ids'] ), $results['total_count'] ) ?>
		</span>
	</div>
<?php endif ?>

<div
	class="post-feed-grid <?= $this->get_settings('ts_wrap_feed') ?> <?= $this->get_settings('ts_wrap_feed') === 'ts-feed-nowrap' ? 'min-scroll min-scroll-h' : '' ?>
		<?= $this->get_settings('ts_loading_style') ?> <?= isset( $search_form ) ? 'sf-post-feed' : '' ?> <?= empty( $results['ids'] ) ? 'post-feed-no-results' : '' ?>"
	data-auto-slide="<?= $this->get_settings('carousel_autoplay') === 'yes' ? absint( $this->get_settings('carousel_autoplay_interval') ) : 0 ?>"
>

	<?php if ( isset( $additional_markers ) ): ?>
		<div class="ts-additional-markers hidden"><?= $additional_markers['render'] ?></div>
	<?php endif ?>

	<?= $results['render'] ?? '' ?>
</div>
<?php $data_source = $this->get_settings('ts_source'); ?>
<?php require locate_template( 'templates/widgets/post-feed/no-results.php' ) ?>
<?php require locate_template( 'templates/widgets/post-feed/pagination.php' ) ?>
<?php require locate_template( 'templates/widgets/post-feed/carousel-nav.php' ) ?>
