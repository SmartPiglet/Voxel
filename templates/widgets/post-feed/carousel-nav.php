<?php if ( $this->get_settings('ts_wrap_feed') === 'ts-feed-nowrap' ): ?>
	<ul class="simplify-ul flexify post-feed-nav">
		<li>
			<a href="#" class="ts-icon-btn ts-prev-page <?= ! is_admin() ? 'disabled' : '' ?>" aria-label="Previous">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_left') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?>
			</a>
		</li>
		<li>
			<a href="#" class="ts-icon-btn ts-next-page <?= ! is_admin() ? 'disabled' : '' ?>" aria-label="Next">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
			</a>
		</li>
	</ul>
<?php endif ?>
