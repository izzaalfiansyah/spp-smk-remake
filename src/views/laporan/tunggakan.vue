<script setup>
	import { onMounted, reactive } from 'vue';
	import Card from '../../components/Card.vue';
	import Table from '../../components/Table.vue';
	import { formatMoney, http } from '../../lib';

	const state = reactive({
		data: {
			items: [],
			total_spp: 0,
			total_tabungan: 0,
		},
		print_url: '',
		excel_url: '',
	});

	function get() {
		http
			.get('/laporan/tunggakan')
			.then((res) => {
				state.print_url = res.headers.get('X-Print-Url');
				state.excel_url = res.headers.get('X-Excel-Url');
				return res.json();
			})
			.then((res) => {
				state.data.items = res;
				res.forEach((item) => {
					state.data.total_spp += (item.pembayaran_total - item.terbayar) * item.jumlah_spp;
					state.data.total_tabungan +=
						(item.pembayaran_total - item.terbayar) * item.jumlah_tabungan;
				});
			});
	}

	onMounted(() => {
		get();
	});
</script>

<template>
	<div class="mb-4 pb-2 border-b text-xl">Laporan Tunggakan</div>
	<Card>
		<div class="bg-gray-100 rounded p-4 mb-4">
			Data diambil berdasarkan tunggakan sampai akhir tahun ajaran.
		</div>
		<div class="text-right mb-4 text-sm">
			<a target="_blank" :href="state.excel_url" class="mr-4 text-green-500">EXCEL</a>
			<a target="_blank" :href="state.print_url" class="text-red-500">PRINT</a>
		</div>
		<Table
			:keys="{
				Kelas: 'kelas',
				Jumlah_Tunggakan: 'jumlah_tunggakan',
				Tunggakan_SPP: 'tunggakan_spp',
				Tunggakan_Tabsis: 'tunggakan_tabsis',
			}"
			:items="state.data.items"
		>
			<template #kelas="{ item }">
				{{ item.kelas }} {{ item.jurusan_kode }} {{ item.rombel }}
			</template>

			<template #jumlah_tunggakan="{ item }">
				{{ item.pembayaran_total - item.terbayar }}
			</template>

			<template #tunggakan_spp="{ item }">
				{{ formatMoney((item.pembayaran_total - item.terbayar) * item.jumlah_spp) }}
			</template>

			<template #tunggakan_tabsis="{ item }">
				{{ formatMoney((item.pembayaran_total - item.terbayar) * item.jumlah_tabungan) }}
			</template>
		</Table>
		<div class="bg-gray-100 p-4 rounded">
			<div>Total Tunggakan SPP : {{ formatMoney(state.data.total_spp) }}</div>
			<div>Total Tunggakan Tabsis : {{ formatMoney(state.data.total_tabungan) }}</div>
		</div>
	</Card>
</template>
