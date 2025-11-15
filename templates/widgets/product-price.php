<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>

<?php if ( $is_available ): ?>
	<?php if ( $discount_price < $regular_price ): ?>
		<span class="vx-price"><?= \Voxel\currency_format( $discount_price, $currency, false ) ?><?= $suffix ?></span>
		<span class="vx-price"><s><?= \Voxel\currency_format( $regular_price, $currency, false ) ?><?= $suffix ?></s></span>
	<?php else: ?>
		<span class="vx-price"><?= \Voxel\currency_format( $regular_price, $currency, false ) ?><?= $suffix ?></span>
	<?php endif ?>
<?php else: ?>
	<span class="vx-price no-stock"><?= $error_message ?></span>
<?php endif ?>

