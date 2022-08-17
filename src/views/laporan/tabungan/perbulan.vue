<script setup>
	import { onMounted, reactive, watch } from 'vue';
	import Card from '../../../components/Card.vue';
	import Table from '../../../components/Table.vue';
	import { formatDate, formatMoney, http, notify } from '../../../lib';

	const state = reactive({
		filter: {
			_bulan: '',
		},
		total: {
			total: 0,
			tambah: 0,
			ambil: 0,
		},
		data: [],
		excel_url: '',
		print_url: '',
	});

	const bulan = [
		'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember',
	];

	function get() {
		http
			.get('/laporan/tabungan/perbulan', state.filter)
			.then((res) => {
				state.excel_url = res.headers.get('X-Excel-Url');
				state.print_url = res.headers.get('X-Print-Url');
				return res.json();
			})
			.then((res) => {
				state.total = {
					total: 0,
					tambah: 0,
					ambil: 0,
				};
				state.data = res;

				res.forEach((item) => {
					state.total.total += item.total_tambah + item.total_ambil;
					state.total.tambah += item.total_tambah;
					state.total.ambil += item.total_ambil;
				});
			})
			.catch((err) => notify(err, 'bg-red-400'));
	}

	watch(state.filter, () => {
		get();
	});

	onMounted(() => {
		const date = new Date();

		state.filter._bulan = date.getMonth() + 1;
	});
</script>

<template>
	<Card title="Laporan Perbulan">
		<div class="form-field mb-3">
			<select v-model="state.filter._bulan">
				<option value="">Pilih Bulan</option>
				<option v-for="(item, i) in bulan" :value="i + 1">{{ item }}</option>
			</select>
		</div>
		<div class="p-4 bg-gray-50 rounded mb-4">Bulan: {{ bulan[state.filter._bulan - 1] }}</div>
		<div class="text-right mb-4 text-sm">
			<a target="_blank" :href="state.excel_url" class="mr-4 text-green-500">EXCEL</a>
			<a target="_blank" :href="state.print_url" class="text-red-500">PRINT</a>
		</div>
		<Table
			:keys="{
				No: 'no',
				Kelas: 'kelas',
				Jumlah_Menabung: 'jumlah_tambah',
				Saldo_Menabung: 'total_tambah',
				Jumlah_Mengambil: 'jumlah_ambil',
				Saldo_Mengambil: 'total_ambil',
			}"
			:items="state.data"
		>
			<template #no="{ index }">
				{{ index + 1 }}
			</template>

			<template #kelas="{ item }">
				{{ item.siswa_kelas }} {{ item.siswa_jurusan_kode }} {{ item.siswa_rombel }}
			</template>

			<template #total_tambah="{ item }">
				{{ formatMoney(item.total_tambah) }}
			</template>

			<template #total_ambil="{ item }">
				{{ formatMoney(item.total_ambil) }}
			</template>
		</Table>
		<div class="p-4 bg-gray-50 rounded mb-4">
			<table>
				<tr>
					<td>Total Menabung</td>
					<td class="px-3">:</td>
					<td>{{ formatMoney(state.total.tambah) }}</td>
				</tr>
				<tr>
					<td>Total Mengambil</td>
					<td class="px-3">:</td>
					<td>{{ formatMoney(state.total.ambil) }}</td>
				</tr>
				<tr>
					<td>Total Semua</td>
					<td class="px-3">:</td>
					<td>{{ formatMoney(state.total.total) }}</td>
				</tr>
			</table>
		</div>
	</Card>
</template>
