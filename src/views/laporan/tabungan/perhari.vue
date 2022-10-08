<script setup>
	import { onMounted, reactive, watch } from 'vue';
	import Card from '../../../components/Card.vue';
	import Table from '../../../components/Table.vue';
	import { auth, formatDate, formatMoney, http, notify, nowDate } from '../../../lib';

	const state = reactive({
		filter: {
			_tanggal: '',
			_user_id: auth.id,
		},
		total: 0,
		data: [],
		excel_url: '',
		print_url: '',
	});

	function get() {
		http
			.get('/laporan/tabungan/perhari', state.filter)
			.then((res) => {
				state.excel_url = res.headers.get('X-Excel-Url');
				state.print_url = res.headers.get('X-Print-Url');
				return res.json();
			})
			.then((res) => {
				state.total = 0;
				state.data = res;

				res.forEach((item) => {
					state.total += item.jumlah_bayar;
				});
			})
			.catch((err) => notify(err, 'bg-red-400'));
	}

	watch(state.filter, () => {
		get();
	});

	onMounted(() => {
		state.filter._tanggal = nowDate();
	});
</script>

<template>
	<Card title="Laporan Perhari">
		<div class="form-field">
			<input type="date" v-model="state.filter._tanggal" />
		</div>
		<div class="p-4 bg-gray-50 rounded mb-4">Tanggal: {{ formatDate(state.filter._tanggal) }}</div>
		<div class="text-right mb-4 text-sm">
			<a target="_blank" :href="state.excel_url" class="mr-4 text-green-500">EXCEL</a>
			<a target="_blank" :href="state.print_url" class="text-red-500">PRINT</a>
		</div>
		<Table
			:keys="{
				No: 'no',
				Nisn: 'siswa_nisn',
				Nama_Siswa: 'siswa_nama',
				Kelas: 'siswa_kelas',
				Id: 'id',
				Jenis_Tabungan: 'status_tabungan_detail',
				Nominal: 'jumlah_bayar',
				Operator: 'operator',
			}"
			:items="state.data"
		>
			<template #no="{ index }">
				{{ index + 1 }}
			</template>

			<template #siswa_nama="{ item }">
				{{ item.siswa.nama }}
			</template>

			<template #siswa_kelas="{ item }">
				{{ item.siswa.kelas }} {{ item.siswa.jurusan_kode }} {{ item.siswa.rombel }}
			</template>

			<template #id="{ item }">
				{{ item.jumlah_bayar > 0 ? '1' : '2' }}
			</template>

			<template #jumlah_bayar="{ item }">
				{{ formatMoney(item.jumlah_bayar) }}
			</template>

			<template #operator="{ item }">
				{{ item.user.nama }}
			</template>
		</Table>
		<div class="p-4 bg-gray-50 rounded mb-4">Total: {{ formatMoney(state.total) }}</div>
	</Card>
</template>
