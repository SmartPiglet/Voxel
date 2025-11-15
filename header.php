<!DOCTYPE html>
<html class="no-js" <?php language_attributes() ?>>
	<head>
		<meta charset="<?php bloginfo('charset') ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0<?= \Voxel\_get_user_scalable_string() ?>" />
		<link rel="profile" href="https://gmpg.org/xfn/11">
		<?php wp_head() ?>
	</head>
	<body <?php body_class() ?>><?php wp_body_open() ?>
		<?php require locate_template( 'templates/components/popup.php' ) ?>
		<?php require locate_template( 'templates/components/form-group.php' ) ?>