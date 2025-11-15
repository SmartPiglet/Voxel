<script type="text/json" class="vxconfig"><?= wp_specialchars_decode( wp_json_encode( $config ) ) ?></script>
<div class="ts-vendor-stats" v-cloak>
	<ul class="ts-generic-tabs simplify-ul flexify bar-chart-tabs">
		<li :class="{'ts-tab-active': activeChart === 'this-week'}">
			<a href="#" @click.prevent="activeChart = 'this-week'"><?= _x( 'Week', 'sales chart', 'voxel' ) ?></a>
		</li>
		<li :class="{'ts-tab-active': activeChart === 'this-month'}">
			<a href="#" @click.prevent="activeChart = 'this-month'"><?= _x( 'Month', 'sales chart', 'voxel' ) ?></a>
		</li>
		<li :class="{'ts-tab-active': activeChart === 'this-year'}">
			<a href="#" @click.prevent="activeChart = 'this-year'"><?= _x( 'Year', 'sales chart', 'voxel' ) ?></a>
		</li>
		<li :class="{'ts-tab-active': activeChart === 'all-time'}">
			<a href="#" @click.prevent="activeChart = 'all-time'"><?= _x( 'All time', 'sales chart', 'voxel' ) ?></a>
		</li>
	</ul>
	<div v-if="currentChart" class="ts-chart" :key="activeChart" :class="[loading?'vx-pending':'','chart-'+activeChart]">
		<div v-if="currentChart.meta.state.has_activity" class="chart-contain">
			<div class="chart-content ">
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
					<!-- <ul class="flexify simplify-ul bar-item-data">
						<li><small>Value</small>{{ item.earnings }}</li>
						<li><small>Orders</small>{{ item.orders }}</li>
					</ul> -->
				</div>
			</div>
			<ul ref="popup" class="flexify simplify-ul bar-item-data" :class="{active: !!activeItem}">
				<li><small><?= _x( 'Value', 'sales chart', 'voxel' ) ?></small>{{ activeItem ? activeItem.earnings : '' }}</li>
				<li><small><?= _x( 'Orders', 'sales chart', 'voxel' ) ?></small>{{ activeItem ? activeItem.orders : '' }}</li>
			</ul>
		</div>
		<div v-else class="ts-no-posts">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('chart_icon') ) ?: \Voxel\svg( 'chart.svg' ) ?>
			<p><?= _x( 'No activity', 'sales chart', 'voxel' ) ?></p>
		</div>
		<div class="ts-chart-nav">
			<p class="">{{ currentChart.meta.label }}</p>
			<a href="#" @click.prevent="loadMore('prev')" :class="{'vx-disabled': !currentChart.meta.state.has_prev}" class="ts-icon-btn">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_left') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?>
			</a>
			<a href="#" @click.prevent="loadMore('next')" :class="{'vx-disabled': !currentChart.meta.state.has_next}" class="ts-icon-btn">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
			</a>
		</div>
	</div>
</div>
