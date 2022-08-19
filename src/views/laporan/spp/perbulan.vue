<script setup>
	import { onMounted, reactive, watch } from 'vue';
	import Card from '../../../components/Card.vue';
	import Table from '../../../components/Table.vue';
	import { formatDate, formatMoney, http, notify, nowDate } from '../../../lib';

	const state = reactive({
		filter: {
			_tanggal_awal: '',
			_tanggal_akhir: '',
		},
		total: 0,
		data: [],
		excel_url: '',
		print_url: '',
	});

	function get() {
		if (state.filter._tanggal_awal && state.filter._tanggal_akhir)
			http
				.get('/laporan/spp/perbulan', state.filter)
				.then((res) => {
					state.excel_url = res.headers.get('X-Excel-Url');
					state.print_url = res.headers.get('X-Print-Url');
					return res.json();
				})
				.then((res) => {
					state.total = 0;
					state.data = res;

					res.forEach((item) => {
						state.total += item.total_bayar + item.total_tabungan;
					});
				})
				.catch((err) => notify(err, 'bg-red-400'));
	}

	watch(state.filter, () => {
		get();
	});

	onMounted(() => {
		state.filter._tanggal_awal = nowDate();
		state.filter._tanggal_akhir = nowDate();
	});
</script>

<template>
	<Card title="Laporan Perbulan">
		<div class="form-field mb-3 grid lg:grid-cols-2 grid-cols-1 gap-3">
			<div>
				<label for="">Tanggal Awal</label>
				<input type="date" class="!mb-0" v-model="state.filter._tanggal_awal" />
			</div>
			<div>
				<label for="">Tanggal Akhir</label>
				<input type="date" class="!mb-0" v-model="state.filter._tanggal_akhir" />
			</div>
		</div>
		<div class="p-4 bg-gray-50 rounded mb-4">
			Rentang Waktu: {{ formatDate(state.filter._tanggal_awal) }} -
			{{ formatDate(state.filter._tanggal_akhir) }}
		</div>
		<div class="text-right mb-4 text-sm">
			<a target="_blank" :href="state.excel_url" class="mr-4 text-green-500">EXCEL</a>
			<a target="_blank" :href="state.print_url" class="text-red-500">PRINT</a>
		</div>
		<Table
			:keys="{
				No: 'no',
				Kelas: 'kelas',
				Jumlah_Pembayaran: 'jumlah_pembayaran',
				Jumlah_Total: 'total_bayar',
				Jumlah_Keringanan: 'jumlah_keringanan',
				Uang_Keringanan: 'uang_keringanan',
				Total_SPP_Keringanan: 'total_keringanan',
			}"
			:items="state.data"
		>
			<template #no="{ index }">
				{{ index + 1 }}
			</template>

			<template #kelas="{ item }">
				{{ item.kelas }} {{ item.jurusan_kode }} {{ item.rombel }}
			</template>

			<template #jumlah_pembayaran="{ item }"> {{ item.jumlah_pembayaran }} Transaksi </template>

			<template #jumlah_keringanan="{ item }"> {{ item.keringanan.jumlah }} Transaksi </template>

			<template #uang_keringanan="{ item }">
				{{ formatMoney(item.keringanan.uang) }}
			</template>

			<template #total_keringanan="{ item }">
				{{ formatMoney(item.keringanan.total) }}
			</template>

			<template #total_bayar="{ item }">
				{{ formatMoney(item.total_bayar + item.total_tabungan) }}
			</template>
		</Table>
		<div class="p-4 bg-gray-50 rounded mb-4">Total: {{ formatMoney(state.total) }}</div>
	</Card>
</template>
