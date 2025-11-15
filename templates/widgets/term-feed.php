<?php
/**
 * Post feed template.
 *
 * @since 1.3
 */
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<?php $original_current_term = \Voxel\get_current_term() ?>

<div
	class="post-feed-grid <?= $this->get_settings('ts_wrap_feed') ?> <?= $this->get_settings('ts_wrap_feed') === 'ts-feed-nowrap' ? 'min-scroll min-scroll-h' : '' ?>
		<?= $this->get_settings('ts_loading_style') ?> <?= isset( $search_form ) ? 'sf-post-feed' : '' ?> "
	data-auto-slide="<?= $this->get_settings('carousel_autoplay') === 'yes' ? absint( $this->get_settings('carousel_autoplay_interval') ) : 0 ?>"
>
	<?php foreach ( $terms as $term ): \Voxel\set_current_term( $term ) ?>
		<div class="ts-preview" data-term-id="<?= esc_attr( $term->get_id() ) ?>" <?= ( $this->get_settings('mod_accent') === 'yes' && ! empty( $term->get_color() ) ) ? sprintf( 'style="--e-global-color-accent: %s;"', $term->get_color() ) : '' ?>>
			<?php \Voxel\print_template( $template_id ) ?>
		</div>
	<?php endforeach ?>
</div>

<?php \Voxel\set_current_term( $original_current_term ) ?>

<?php require locate_template( 'templates/widgets/post-feed/carousel-nav.php' ) ?>
