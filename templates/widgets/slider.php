<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<?php if ( count( $images ) === 1 ): ?>
	<div class="ts-preview ts-single-slide">
		<?php foreach ( $images as $i => $image ): ?>
				<?php if ( $link_type === 'custom_link' ): ?>
					<?php $this->add_link_attributes( 'ts_slider_link' . $i, $this->get_settings('ts_link_src') ) ?>
					<a <?= $this->get_render_attribute_string( 'ts_slider_link' . $i ) ?>>
						<?= wp_get_attachment_image( $image['id'], $image['display_size'], false, [ 'loading' => 'lazy' ] ); ?>
					</a>
				<?php elseif ( $link_type === 'lightbox' ) : ?>
					<a
						href="<?= esc_url( $image['src_lightbox'] ) ?>"
						data-elementor-open-lightbox="yes"
						<?= $is_slideshow ? sprintf( 'data-elementor-lightbox-slideshow="%s"', $gallery_id ) : '' ?>
						data-elementor-lightbox-description="<?= esc_attr( $image['caption'] ?: ( $image['alt'] ?: $image['description'] ) ) ?>"
					>
						<?= wp_get_attachment_image( $image['id'], $image['display_size'], false, [ 'loading' => 'lazy' ] ); ?>
					</a>
				<?php else: ?>
					<?= wp_get_attachment_image( $image['id'], $image['display_size'], false, [ 'loading' => 'lazy' ] ); ?>
				<?php endif ?>
		<?php endforeach ?>
	</div>
<?php endif ?>
<?php if ( count( $images ) > 1 ): ?>
	<div class="ts-slider flexify">
		<div class="post-feed-grid ts-feed-nowrap nav-type-dots" data-auto-slide="<?= $this->get_settings('carousel_autoplay') === 'yes' ? absint( $this->get_settings('carousel_autoplay_interval') ) : 0 ?>">
			<?php foreach ( $images as $i => $image ): ?>
				<div class="ts-preview" _id="slide-<?= $slider_id ?>-<?= $image['id'] ?>" id="ts-media-<?= $image['id'] ?>">
					<?php if ( $link_type === 'custom_link' ): ?>
						<?php $this->add_link_attributes( 'ts_slider_link' . $i, $this->get_settings('ts_link_src') ) ?>
						<a <?= $this->get_render_attribute_string( 'ts_slider_link' . $i ) ?>>
							<?= wp_get_attachment_image( $image['id'], $image['display_size'], false, [ 'loading' => 'lazy' ] ); ?>
						</a>
					<?php elseif ( $link_type === 'lightbox' ) : ?>
						<a
							href="<?= esc_url( $image['src_lightbox'] ) ?>"
							data-elementor-open-lightbox="yes"
							<?= $is_slideshow ? sprintf( 'data-elementor-lightbox-slideshow="%s"', $gallery_id ) : '' ?>
							data-elementor-lightbox-description="<?= esc_attr( $image['caption'] ?: ( $image['alt'] ?: $image['description'] ) ) ?>"
						>
							<?= wp_get_attachment_image( $image['id'], $image['display_size'], false, [ 'loading' => 'lazy' ] ); ?>
						</a>
					<?php else: ?>
						<?= wp_get_attachment_image( $image['id'], $image['display_size'], false, [ 'loading' => 'lazy' ] ); ?>
					<?php endif ?>
				</div>
			<?php endforeach ?>
		</div>
	</div>
<?php endif ?>
<?php if ( $this->get_settings('ts_show_navigation') === 'yes' && count( $images ) > 1 ): ?>
	<div class="ts-slide-nav">
		<?php foreach ( $images as $image ): ?>
			<a href="#" onclick="let s=event.target.closest('.elementor-widget-ts-slider').querySelector('#ts-media-<?= absint( $image['id'] ) ?>');s&&(s.parentElement.scrollLeft=s.offsetLeft); return !1;">
				<?= wp_get_attachment_image( $image['id'], 'thumbnail', false, [ 'loading' => 'lazy' ] ); ?>
			</a>
		<?php endforeach ?>
	</div>
<?php endif ?>

<?php if ( count( $images ) > 1 ): ?>
	<ul class="simplify-ul flexify post-feed-nav">
		<li>
			<a href="#" class="ts-icon-btn ts-prev-page" aria-label="Previous">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_left') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?>
			</a>
		</li>
		<li>
			<a href="#" class="ts-icon-btn ts-next-page" aria-label="Next">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
			</a>
		</li>
	</ul>
<?php endif ?>
