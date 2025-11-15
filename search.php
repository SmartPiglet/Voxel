<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

get_header();
\Voxel\print_header();
?>

<?php the_archive_title() ?>
<?php the_archive_description() ?>
<?php if ( have_posts() ): ?>
	<ul>
		<?php while ( have_posts() ): the_post(); ?>
			<li>
				<a href="<?php the_permalink() ?>"><?php the_title() ?></a>
			</li>
		<?php endwhile ?>
	</ul>
	<?php echo paginate_links() ?>
<?php endif ?>

<?php
\Voxel\print_footer();
get_footer();
