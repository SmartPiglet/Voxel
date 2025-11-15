<script type="text/json" class="vxconfig"><?= wp_specialchars_decode( wp_json_encode( $config ) ) ?></script>
<div class="ts-booking-calendar" v-cloak>
	<div class="ts-form ts-order-filters min-scroll min-scroll-h">
		<form-group popup-key="timeframe" ref="timeframe" @save="$refs.timeframe.blur(); filters.timeframe === 'custom' || getItems();" @clear="">
			<template #trigger>
				<div class="ts-filter ts-popup-target ts-filled" @mousedown="$root.activePopup = 'timeframe'">
					<div class="ts-filter-text">
						{{ config.timeframes[ filters.timeframe ] }}
					</div>
					<div class="ts-down-icon"></div>
				</div>
			</template>
			<template #popup>
				<div class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
					<ul class="simplify-ul ts-term-dropdown-list min-scroll">
						<li v-for="label, key in config.timeframes">
							<a href="#" class="flexify" @click.prevent="filters.timeframe = key">
								<div class="ts-radio-container">
									<label class="container-radio">
										<input
											type="radio"
											:value="key"
											:checked="filters.timeframe === key"
											disabled
											hidden
										>
										<span class="checkmark"></span>
									</label>
								</div>
								<span>{{ label }}</span>
								<div class="ts-term-icon">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_calendar_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
								</div>
							</a>
						</li>
					</ul>
				</div>
			</template>
		</form-group>

		<form-group v-if="filters.timeframe === 'custom'" popup-key="customDate" ref="customDate" @save="$refs.customDate.blur(); getItems();" @clear="$refs.datePicker.reset()">
			<template #trigger>
				<div class="ts-filter ts-popup-target" @mousedown="$root.activePopup = 'customDate'" :class="{'ts-filled': filters.custom_date !== null}">
					<div class="ts-filter-text">
						<template v-if="filters.custom_date">
							{{ $w.Voxel.helpers.dateFormat( filters.custom_date ) }}
						</template>
						<template v-else>
							<?= _x( 'Select date', 'booking calendar', 'voxel' ) ?>
						</template>
					</div>
					<div class="ts-down-icon"></div>
				</div>
			</template>
			<template #popup>
				<date-picker ref="datePicker"></date-picker>
			</template>
		</form-group>

		<form-group popup-key="postFilter" ref="postFilter" @save="$refs.postFilter.blur(); getItems();" @clear="filters.post_id = null; post_filter.active = null;">
			<template #trigger>
				<div class="ts-filter ts-popup-target" @mousedown="$root.activePopup = 'postFilter'; postFilterOpened()" :class="{'ts-filled': filters.post_id !== null}">
					<div class="ts-filter-text">
						<template v-if="post_filter.active">
							{{ post_filter.active.title }}
						</template>
						<template v-else>
							<?= _x( 'All posts', 'booking calendar', 'voxel' ) ?>
						</template>
					</div>
					<div class="ts-down-icon"></div>
				</div>
			</template>
			<template #popup>
				<div class="ts-term-dropdown ts-md-group ts-multilevel-dropdown">
					<ul class="simplify-ul ts-term-dropdown-list min-scroll">
						<template v-if="post_filter.posts.length">
							<li v-for="post in post_filter.posts">
								<a href="#" class="flexify" @click.prevent="filters.post_id = post.id; post_filter.active = post;">
									<div class="ts-radio-container">
										<label class="container-radio">
											<input
												type="radio"
												:value="post.id"
												:checked="filters.post_id === post.id"
												disabled
												hidden
											>
											<span class="checkmark"></span>
										</label>
									</div>
									<span>{{ post.title }}</span>
									<div class="ts-term-icon">
										<!-- <span v-if="post.logo" v-html="post.logo"></span> -->
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_listing_icon') ) ?: \Voxel\svg( 'file.svg' ) ?>
									</div>
								</a>
							</li>
							<li>
								<a href="#" v-if="post_filter.has_more" @click.prevent="loadPosts" class="ts-btn ts-btn-4" :class="{'vx-pending': post_filter.loading}">
									<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_timeline_load_icon') ) ?: \Voxel\svg( 'reload.svg' ) ?>
									<?= __( 'Load more', 'voxel' ) ?>
								</a>
							</li>
						</template>
						<template v-else>
							<li v-if="post_filter.loading"><a href="#" class="flexify"><span><?= __( 'Loading', 'voxel' ) ?></span></a></li>
							<li v-else><a href="#" class="flexify"><span><?= _x( 'No posts with active reservations found.', 'booking calendar', 'voxel' ) ?></span></a></li>
						</template>
					</ul>
				</div>
			</template>
		</form-group>
		<div class="ts-form-group ts-grid-nav ts-form-submit">
			<div class="ts-icon-btn prev-item">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_left') ) ?: \Voxel\svg( 'chevron-left.svg' ) ?>
			</div>
			<div class="ts-icon-btn next-item">
				<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_chevron_right') ) ?: \Voxel\svg( 'chevron-right.svg' ) ?>
			</div>
		</div>
	</div>

	<div class="ts-schedule-calendar" :class="{'vx-pending': loading}">
		<div class="ts-cal-grid min-scroll min-scroll-h" ref="scrollArea">
			<div v-for="day in items" class="ts-cal-box min-scroll">
				<div class="ts-cal-date">
					<p>{{ day.weekday }} <span>{{ day.label }}</span></p>
				</div>
				<div v-if="day.items.length" class="ts-cal-bookings">
					<a v-for="order in day.items" :href="order.link" @click="itemClicked($event)" class="ts-cal-item">
						<div class="tci-top">
							<span v-html="order.customer.avatar"></span>
							<p class="tci-title">{{ order.customer.name }}</p>
						</div>

						<ul v-if="order.labels.length" class="simplify-ul flexify tci-labels">
							<li v-for="label in order.labels" :style="{backgroundColor: label.background, color: label.color}">
								{{ label.content }}
							</li>
						</ul>
					</a>

					<a href="#" v-if="day.has_more" @click.prevent="loadMore(day)" class="ts-btn ts-btn-4" :class="{'vx-pending': day.loading}">
						<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_timeline_load_icon') ) ?: \Voxel\svg( 'reload.svg' ) ?>
						<?= __( 'Load more', 'voxel' ) ?>
					</a>
				</div>
				<div v-else class="ts-no-posts">
					<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_nobookings_icon') ) ?: \Voxel\svg( 'calendar.svg' ) ?>
					<p><?= _x( 'No bookings found', 'booking calendar', 'voxel' ) ?></p>
				</div>
			</div>
		</div>
	</div>
	<div class="ts-chart-nav">
		<p class="">{{ timeframeLabel }}</p>
		<a href="#" @click.prevent="prevWeek" class="ts-icon-btn">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_left') ) ?: \Voxel\svg( 'arrow-left.svg' ) ?>
		</a>
		<a href="#" @click.prevent="nextWeek" class="ts-icon-btn">
			<?= \Voxel\get_icon_markup( $this->get_settings_for_display('ts_arrow_right') ) ?: \Voxel\svg( 'arrow-right.svg' ) ?>
		</a>
	</div>
</div>
