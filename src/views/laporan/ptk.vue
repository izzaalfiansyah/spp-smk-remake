<script setup>
	import { onMounted, reactive } from 'vue';
	import Card from '../../components/Card.vue';
	import Table from '../../components/Table.vue';
	import { formatMoney, http, notify } from '../../lib';

	const state = reactive({
		data: {
			items: [],
		},
		total: 0,
		print_url: '',
		excel_url: '',
	});

	function get() {
		http
			.get('/laporan/ptk', state.filter)
			.then((res) => {
				state.excel_url = res.headers.get('X-Excel-Url');
				state.print_url = res.headers.get('X-Print-Url');
				return res.json();
			})
			.then((res) => {
				state.data.items = res;
				state.total = 0;
				res.forEach((item) => {
					state.total += parseInt(item.total_tabungan);
				});
			})
			.catch((err) => notify(err, 'bg-red-400'));
	}

	onMounted(() => {
		get();
	});
</script>

<template>
	<div class="mb-4 pb-2 border-b text-xl">Laporan Tabungan PTK</div>
	<Card class="mb-4">
		<div class="text-right mb-4 text-sm">
			<a target="_blank" :href="state.excel_url" class="mr-4 text-green-500">EXCEL</a>
			<a target="_blank" :href="state.print_url" class="text-red-500">PRINT</a>
		</div>
		<Table
			:keys="{
				Kode: 'kode',
				Nama: 'nama',
				Jabatan: 'jabatan',
				Total_Tabungan: 'total_tabungan',
			}"
			:items="state.data.items"
		>
			<template #total_tabungan="{ item }">
				{{ formatMoney(item.total_tabungan) }}
			</template>
		</Table>
		<div class="p-4 bg-gray-50 rounded mb-4">
			<div>Total Tabungan: {{ formatMoney(state.total) }}</div>
		</div>
	</Card>
</template>
