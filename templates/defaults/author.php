<?php

if ( ! defined('ABSPATH') ) {
	exit;
}

get_header();
\Voxel\print_header();

the_archive_title();

\Voxel\print_footer();
get_footer();
