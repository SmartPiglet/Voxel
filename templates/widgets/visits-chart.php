<script type="text/json" class="vxconfig"><?= wp_specialchars_decode( wp_json_encode( $config ) ) ?></script>
<div class="ts-visits-chart" v-cloak>
	<ul class="ts-generic-tabs simplify-ul flexify bar-chart-tabs">
		<li :class="{'ts-tab-active': active_chart === '24h'}">
			<a href="#" @click.prevent="active_chart = '24h'"><?= _x( '24 hours', 'visits chart', 'voxel' ) ?></a>
		</li>
		<li :class="{'ts-tab-active': active_chart === '7d'}">
			<a href="#" @click.prevent="active_chart = '7d'"><?= _x( '7 days', 'visits chart', 'voxel' ) ?></a>
		</li>
		<li :class="{'ts-tab-active': active_chart === '30d'}">
			<a href="#" @click.prevent="active_chart = '30d'"><?= _x( '30 days', 'visits chart', 'voxel' ) ?></a>
		</li>
		<li :class="{'ts-tab-active': active_chart === '12m'}">
			<a href="#" @click.prevent="active_chart = '12m'"><?= _x( '12 months', 'visits chart', 'voxel' ) ?></a>
		</li>
	</ul>
	<div v-if="currentChart" class="ts-chart" :key="active_chart" :class="[loading?'vx-pending':'','chart-'+active_chart]">
		<div v-if="currentChart.loaded === false" class="chart-contain">
			<div class="ts-no-posts">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('chart_icon') ) ?: \Voxel\svg( 'chart.svg' ) ?>
				<p><?= _x( 'Loading data', 'visits chart', 'voxel' ) ?></p>
			</div>
		</div>
		<template v-else>
			<div v-if="currentChart.meta.has_activity" class="chart-contain">
				<div class="chart-content">
					<div class="bar-item-con bar-values">
						<span v-for="step in currentChart.steps">{{ step }}</span>
					</div>
				</div>
				<div class="chart-content min-scroll min-scroll-h" ref="scrollArea">
					<div v-for="item in currentChart.items" class="bar-item-con ">
						<div class="bi-hold">
							<div @mouseover="showPopup($event, item)" @mouseleave="hidePopup" class="bar-item bar-animate" :style="{height: item.percent+'%'}"></div>
						</div>
						<span>{{ item.label }}</span>
					</div>
				</div>
				<ul ref="popup" class="flexify simplify-ul bar-item-data" :class="{active: !!activeItem}">
					<li>
						<small><?= _x( 'Views', 'visits chart', 'voxel' ) ?></small>
						{{ activeItem ? activeItem.count : '' }}
					</li>
					<li>
						<small><?= _x( 'Unique views', 'visits chart', 'voxel' ) ?></small>
						{{ activeItem ? activeItem.unique_count : '' }}
					</li>
				</ul>
			</div>
			<div v-else class="ts-no-posts">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('chart_icon') ) ?: \Voxel\svg( 'chart.svg' ) ?>
				<p><?= _x( 'No activity', 'visits chart', 'voxel' ) ?></p>
			</div>
			<!-- <div class="ts-chart-nav">
				<p class="">{{ currentChart.meta.label }}</p>
			</div> -->
		</template>
	</div>
</div>
