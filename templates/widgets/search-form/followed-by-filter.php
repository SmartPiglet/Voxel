<script type="text/html" id="search-form-followed-by-filter">
	<form-group :popup-key="filter.id" ref="formGroup" v-if="filter.value !== null" :wrapper-class="repeaterId">
		<template #trigger>
			<label v-if="$root.config.showLabels" class="">{{ filter.label }}</label>
			<div class="ts-filter ts-popup-target" :class="{'ts-filled': filter.value !== null}">
				<span v-html="filter.icon"></span>
				<div class="ts-filter-text">
					<!-- <span v-if="filter.props.user.avatar" v-html="filter.props.user.avatar"></span> -->
					<template v-if="filter.props.user.name">
						{{ filter.props.user.name }}
					</template>
					<template v-else>
						<?= _x( 'Unknown', 'user filter', 'voxel' ) ?>
					</template>
				</div>
			</div>
		</template>
	</form-group>
</script>
