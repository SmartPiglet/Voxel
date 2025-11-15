<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

get_header();
\Voxel\print_header();

while ( have_posts() ): the_post();
	the_title();
	the_content();
endwhile;

\Voxel\print_footer();
get_footer();
