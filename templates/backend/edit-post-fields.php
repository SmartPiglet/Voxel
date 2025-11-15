<?php
/**
 * Edit post fields form in backend.
 *
 * @since 1.0
 */

if ( ! defined('ABSPATH') ) {
	exit;
}

wp_enqueue_style( 'elementor-frontend' );
wp_enqueue_script( 'jquery' );
add_filter( '_voxel/enqueue-custom-popup-kit', '__return_false' );
?>
<?php get_header() ?>
<style type="text/css">
	html {
		scrollbar-gutter: stable;
		box-sizing: border-box;

	}





	body {
		font-family: "Arimo", sans-serif;
		font-size: 16px;
	    --ts-shade-1: #313135;
	    --ts-shade-2: #797a88;
	    --ts-shade-3: #afb3b8;
	    --ts-shade-4: #cfcfcf;
	    --ts-shade-5: #f3f3f3;
	    --ts-shade-6: #f8f8f8;
	    --ts-shade-7: #fcfcfc;
	    --ts-accent-1: #4F46E5;
	    --ts-accent-2: #6A62F2;

/*		min-height: 1000px;*/
		overflow: hidden;
		scrollbar-gutter: stable;
	}

	.ts-save-changes {
		display: none;
	}




	.ts-form-group {
		width: 100%;
	}
	.ts-filter.ts-filled svg, .ts-filter.ts-filled i {
	    color: var(--ts-accent-1);
	    fill: var(--ts-accent-1);
	}

	.iframe-editor-vx {
		max-width: 650px;
		margin: auto;
		padding-bottom: 300px;
		padding: 40px;
		padding-bottom: 500px;
	}

	@media (max-width: 1224px) {
		.iframe-editor-vx {
			max-width: 450px;

		}
	}

	<?php if ( post_type_supports( $post_type->get_key(), 'title' ) ): ?>
		.field-key-title {
			display: none !important;
		}
	<?php endif ?>

	<?php if ( post_type_supports( $post_type->get_key(), 'editor' ) ): ?>
		.field-key-description {
			display: none !important;
		}
	<?php endif ?>

	.field-key-_thumbnail_id, .ui-image-field {
		display: none !important;
	}
</style>
<div data-elementor-type="wp-page" data-elementor-id="0" class="elementor elementor-0 iframe-editor-vx">
	<?php $widget->print_element() ?>
</div>

<?php get_footer() ?>