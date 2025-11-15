<div class="ts-no-posts <?= ! empty( $results['ids'] ) ? 'hidden' : '' ?>">
	<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_noresults_icon') ) ?: \Voxel\svg( 'keyword-research.svg' ) ?>
	<p><?= $this->get_settings_for_display('ts_noresults_text') ?>
		<?php if ( isset( $data_source ) && $data_source === 'search-form' ): ?>
		<a href="#" class="ts-feed-reset">
			<?= _x( 'Reset filters?', 'post feed', 'voxel' ) ?>
		</a>
		<?php endif; ?>
	</p>
	
</div>
