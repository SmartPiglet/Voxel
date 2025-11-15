
<ul class="ts-gallery flexify simplify-ul">
	<div class="ts-gallery-grid">
		<?php foreach ( $visible as $image ): ?>
			<li>
				<a
					href="<?= esc_url( $image['src_lightbox'] ) ?>"
					data-elementor-open-lightbox="yes"
					<?= $is_slideshow ? sprintf( 'data-elementor-lightbox-slideshow="%s"', $gallery_id ) : '' ?>
					data-elementor-lightbox-description="<?= esc_attr( $image['caption'] ?: ( $image['alt'] ?: $image['description'] ) ) ?>"
				>
					<div class="ts-image-overlay"></div>
					<?php echo wp_get_attachment_image($image['id'], $image['display_size'], false, ['alt' => esc_attr( $image['alt'] ?: $image['description'] )]); ?>
				</a>
			</li>
		<?php endforeach ?>

		<?php if ( count( $hidden ) ): ?>
			<li class="ts-gallery-last-item">
				<a
					href="<?= esc_url( $hidden[0]['src_lightbox'] ) ?>"
					data-elementor-open-lightbox="yes"
					<?= $is_slideshow ? sprintf( 'data-elementor-lightbox-slideshow="%s"', $gallery_id ) : '' ?>
					data-elementor-lightbox-description="<?= esc_attr( $hidden[0]['caption'] ?: ( $hidden[0]['alt'] ?: $hidden[0]['description'] ) ) ?>"
				>
					<div class="ts-image-overlay">
						
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_gl_general_view_icon') ) ?: \Voxel\svg( 'grid.svg' ) ?>
						<p><?= sprintf( '+%d', count( $hidden ) ) ?></p>
					</div>
					<?php echo wp_get_attachment_image($hidden[0]['id'], $hidden[0]['display_size'], false, ['alt' => esc_attr( $hidden[0]['alt'] ?: $hidden[0]['description'] )]); ?>
				</a>

				<div class="hidden">
					<?php foreach ( $hidden as $index => $image ): ?>
						<?php if ( $index === 0 ) continue; ?>
						<a
							href="<?= esc_url( $image['src_lightbox'] ) ?>"
							data-elementor-open-lightbox="yes"
							data-elementor-lightbox-slideshow="<?= $gallery_id ?>"
							data-elementor-lightbox-description="<?= esc_attr( $image['caption'] ?: ( $image['alt'] ?: $image['description'] ) ) ?>"
						></a>
					<?php endforeach ?>
				</div>
			</li>
		<?php endif ?>

		<?php if ( $filler_count >= 1 ): ?>
			<?php while ( $filler_count >= 1 ): $filler_count--; ?>
				<li class="ts-empty-item">
					<div></div>
				</li>
			<?php endwhile ?>
		<?php endif ?>
	</div>
</ul>

