<script setup>
	const props = defineProps(['keys', 'items']);

	const titles = Object.keys(props.keys);
</script>

<template>
	<div class="overflow-x-auto">
		<table class="w-full lg:whitespace-nowrap">
			<thead class="hidden lg:table-header-group">
				<tr>
					<td v-for="title in titles" class="px-3 p-1 uppercase text-sm font-semibold">
						{{ title.replace(/_/gi, ' ') }}
					</td>
				</tr>
				<tr class="h-2"></tr>
			</thead>
			<tbody>
				<template v-if="props.items.length" v-for="(item, i) in props.items">
					<tr
						class="border border-gray-100 block lg:table-row transition rounded hover:bg-blue-500 hover:text-white"
					>
						<td v-for="title in titles" class="p-3 flex items-center justify-between lg:table-cell">
							<div class="lg:hidden pr-4 font-semibold">
								{{ title.replace(/_/gi, ' ') }}
							</div>
							<div class="lg:text-left text-right max-w-250px lg:max-w-full">
								<slot :name="props.keys[title]" :item="item" :index="i">
									{{ item[props.keys[title]] }}
								</slot>
							</div>
						</td>
					</tr>
					<tr class="h-3"></tr>
				</template>
				<template v-else>
					<tr class="border border-gray-100 text-center transition rounded">
						<td class="p-3 text-center" :colspan="titles.length">Data tidak tersedia.</td>
					</tr>
					<tr class="h-3"></tr>
				</template>
			</tbody>
		</table>
	</div>
</template>
