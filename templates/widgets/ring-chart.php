<?php
$chartSuffix = $this->get_settings('ts_chart_value_suffix');
$chartValue = $this->get_settings('ts_chart_value');
$size = $this->get_settings('ts_chart_size');
$stroke_width = $this->get_settings('ts_chart_stroke_width');
$radius = 100/(pi()*2);
$cxcy = $radius + $stroke_width/2;
$circleColor = $this->get_settings('ts_chart_cirle_color');
$circleFillColor = $this->get_settings('ts_chart_fill_color');
?>

<div class="circle-chart-position ">
	<div class="circle-chart-wrapper flexify">
		<div class="circle-chart">
			<svg class="circle-chart" viewbox="0 0 <?= $cxcy * 2 ?> <?= $cxcy * 2 ?>" width="<?= $size ?>" height="<?= $size ?>" xmlns="http://www.w3.org/2000/svg">
				<circle class="circle-chart__background" stroke="<?= $circleColor ?>" stroke-width="<?= $stroke_width ?>" fill="none" cx="<?= $cxcy ?>" cy="<?= $cxcy ?>" r="<?= $radius ?>" />
				<circle class="circle-chart__circle" stroke="<?= $circleFillColor ?>" stroke-width="<?= $stroke_width ?>" stroke-dasharray="<?= $chartValue ?>,100" stroke-linecap="round" fill="none" cx="<?= $cxcy ?>" cy="<?= $cxcy ?>" r="<?= $radius ?>" />
			</svg>
			<p class="chart-value"><?= $chartValue . $chartSuffix ?></p>
		</div>
	</div>
</div>