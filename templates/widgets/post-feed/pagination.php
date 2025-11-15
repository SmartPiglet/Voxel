<?php if ( in_array( $data_source, [ 'search-form', 'search-filters' ], true ) ): ?>
	<?php if ( $pagination === 'prev_next' ): ?>
		<div class="feed-pagination flexify <?= ! ( $results['has_prev'] || $results['has_next'] ) ? 'hidden' : '' ?>">
			<a href="#" class="ts-btn ts-btn-1 ts-btn-large ts-load-prev <?= ! $results['has_prev'] ? 'disabled' : '' ?>">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_left') ) ?: \Voxel\svg( 'arrow-left.svg' ) ?>
				<?= _x( 'Previous', 'post feed', 'voxel' ) ?>
			</a>
			<a href="#" class="ts-btn ts-btn-1 ts-btn-large btn-icon-right ts-load-next <?= ! $results['has_next'] ? 'disabled' : '' ?>">
				<?= _x( 'Next', 'post feed', 'voxel' ) ?>
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'arrow-right.svg' ) ?>
			</a>
		</div>
	<?php elseif ( $pagination === 'load_more' ): ?>
		<div class="feed-pagination flexify">
			<a href="#" class="ts-btn ts-btn-1 ts-btn-large ts-load-more <?= ! $results['has_next'] ? 'hidden' : '' ?>">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_lm_icon') ) ?: \Voxel\svg( 'reload.svg' ) ?>
				<?= _x( 'Load more', 'post feed', 'voxel' ) ?>
			</a>
		</div>
	<?php endif ?>
<?php elseif ( $data_source === 'archive' ):
	$prev_link = \Voxel\get_previous_posts_link();
	$next_link = \Voxel\get_next_posts_link();
	?>
	<div class="feed-pagination wparchive-pagination flexify">
		<a href="<?= $prev_link ?>" class="ts-btn ts-btn-1 ts-btn-large ts-load-prev <?= ! $prev_link ? 'disabled' : '' ?>">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_left') ) ?: \Voxel\svg( 'arrow-left.svg' ) ?>
			<?= _x( 'Previous', 'post feed', 'voxel' ) ?>
		</a>
		<a href="<?= $next_link ?>" class="ts-btn ts-btn-1 ts-btn-large btn-icon-right ts-load-next <?= ! $next_link ? 'disabled' : '' ?>">
			<?= _x( 'Next', 'post feed', 'voxel' ) ?>
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'arrow-right.svg' ) ?>
		</a>
	</div>
<?php endif ?>
