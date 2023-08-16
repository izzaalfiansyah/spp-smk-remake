<script setup>
	import { onMounted, reactive, watch } from 'vue';
	import Card from '../../../components/Card.vue';
	import Table from '../../../components/Table.vue';
	import { auth, formatDate, formatMoney, http, notify, nowDate } from '../../../lib';

	const state = reactive({
		filter: {
			_tanggal: '',
			_user_id: auth.role == '1' ? '' : auth.id,
		},
		total: 0,
		total_bulan: 0,
		data: [],
		excel_url: '',
		print_url: '',
		user: [],
	});

	function get() {
		http
			.get('/laporan/spp/perhari', state.filter)
			.then((res) => {
				state.excel_url = res.headers.get('X-Excel-Url');
				state.print_url = res.headers.get('X-Print-Url');
				return res.json();
			})
			.then((res) => {
				state.total = 0;
				state.data = res;

				res.forEach((item) => {
					state.total_bulan += parseInt(item.total_bulan.replace(' Bulan'));
					state.total += item.total_bayar + item.total_tabungan + item.total_uang_praktik;
				});
			})
			.catch((err) =>
			 notify(err, 'bg-red-400'));
	}

	function getUser() {
		http.get('/user').then((res) => res.json()).then((res) => {
			state.user = res;
		});
	}

	watch(state.filter, () => {
		get();
	});

	onMounted(() => {
		getUser();
		state.filter._tanggal = nowDate();
	});
</script>

<template>
	<Card title="Laporan Perhari">
		<div class="flex lg:flex-row flex-col gap-x-3">
			<div class="form-field flex-1">
				<input type="date" v-model.lazy="state.filter._tanggal" />
			</div>
			<div class="form-field flex-1">
				<select v-model="state.filter._user_id">
					<option value="">Semua User</option>
					<option v-for="item in state.user" :value="item.id">{{ item.nama }}</option>
				</select>
			</div>
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
				Bulan: 'total_bulan',
				Total: 'total_bayar',
				Waktu: 'waktu',
				Petugas: 'operator',
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

			<template #total_bayar="{ item }">
				{{ formatMoney(item.total_bayar + item.total_tabungan + item.total_uang_praktik) }}
			</template>

			<template #operator="{ item }">
				{{ item.operator.nama }}
			</template>
		</Table>
		<div class="p-4 bg-gray-50 rounded mb-4">
			<div>Total Bulan: {{ state.total_bulan }} Bulan</div>
			<div>Total Pembayaran: {{ formatMoney(state.total) }}</div>
		</div>
	</Card>
</template>
