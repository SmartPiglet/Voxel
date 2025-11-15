<?php
if ( ! defined('ABSPATH') ) {
	exit;
} ?>
<script type="text/html" id="vx-dynamic-group-list">
	<div class="nvx-tagslist" ref="wrapper">
		<ul>
			<template v-for="group, groupKey, index in activeGroups" :key="groupKey">
				<li class="is-group" :class="{'is-open': !!group._open}">
					<span @click.prevent="toggleGroup(group)">
						{{ group.label }}

						<span v-if="typeof group.description === 'string' && group.description.length" class="vx-info-box" style="float: right;">
							<?php \Voxel\svg( 'info.svg' ) ?>
							<p>{{ group.description }}</p>
						</span>
					</span>
					<transition-height :active="transitionHeight">
						<template v-if="group._open">
							<property-list :group="group" :group-key="groupKey" :properties="group.group.exports" :methods="group.group.methods" :groupList="this"></property-list>
						</template>
					</transition-height>
				</li>
			</template>
			<template v-if="!Object.keys(activeGroups).length">
				<li>
					No results
				</li>
			</template>
		</ul>
	</div>
</script>

<script type="text/html" id="vx-dynamic-property-list">
	<ul>
		<template v-for="property, property_key in activeProperties" :key="getTagScript(property)">
			<template v-if="!property.hidden">
				<template v-if="property.type === 'object'">
					<li v-if="! ( property.subgroup && prependPath.length >= 2 )" class="is-group" :class="{'is-open': !!property._open}">
						<span @click.prevent="toggleSubgroup(property)">{{ property.label }}</span>
						<transition-height :active="groupList.transitionHeight">
							<template v-if="property._open">
								<property-list :group="group" :group-key="groupKey" :properties="getNestedProperties(property)" :prepend-path="getNestedPrependPath(property)" :group-list="groupList"></property-list>
							</template>
						</transition-height>
					</li>
				</template>
				<template v-else-if="property.type === 'object-list'">
					<li v-if="! ( property.subgroup && prependPath.length >= 2 )" class="is-group" :class="{'is-open': !!property._open}">
						<span @click.prevent="toggleSubgroup(property)">{{ property.label }}</span>
						<transition-height :active="groupList.transitionHeight">
							<template v-if="property._open">
								<property-list :group="group" :group-key="groupKey" :properties="getNestedProperties(property)" :prepend-path="getNestedPrependPath(property)" :group-list="groupList"></property-list>
							</template>
						</transition-height>
					</li>
				</template>
				<template v-else>
					<li>
						<span @click="selectItem(property)" @keydown.enter="selectItem(property)" draggable="true" @dragstart="onDragStart($event, property)">{{ property.label }}</span>
					</li>
				</template>
			</template>
		</template>

		<template v-for="method, method_key in methods" :key="getMethodScript(method)">
			<li>
				<span @click="selectMethod(method)" @keydown.enter="selectMethod(method)" draggable="true" @dragstart="onMethodDragStart($event, method)">
					<i class="las la-code"></i>
					{{ method.label }}
				</span>
			</li>
		</template>
	</ul>
</script>
