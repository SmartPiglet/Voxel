<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="product-booking">
	<template v-if="field.props.booking_type === 'timeslots'">
		<?php require_once locate_template('templates/widgets/create-post/product-field/booking/type-timeslots.php') ?>
	</template>
	<template v-else>
		<?php require_once locate_template('templates/widgets/create-post/product-field/booking/type-days.php') ?>
	</template>
</script>
