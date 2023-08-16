<!-- <script setup>
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
</template> -->
<script setup>
	import { onMounted, reactive, watch } from 'vue';
	import Card from '../../components/Card.vue';
	import Table from '../../components/Table.vue';
	import { auth, formatDate, formatMoney, http, notify, nowDate } from '../../lib';
	import Modal from '../../components/Modal.vue';
	import Form, { rule } from '../../components/Form.vue';
	import Pagination from '../../components/Pagination.vue';

	const state = reactive({
		isEdit: false,
		data: {
			items: [],
			ptk: [],
			totalCount: 0,
		},
		modal: {
			save: false,
			delete: false,
		},
		req: {
			id: '',
			ptk_id: '',
			user_id: auth.id,
			nominal: '',
			status: '',
		},
		filter: {
			_limit: 10,
			_page: 1,
			_ptk_id: '',
			_tanggal: '',
		},
		excel_url: '',
		print_url: '',
	});

	const rules = {
		ptk_id: rule.string().required(),
		status: rule.string().required(),
		user_id: rule.string().required(),
		nominal: rule.number().required(),
	};

	function nullable() {
		state.req = {
			id: '',
			ptk_id: '',
			user_id: auth.id,
			nominal: '',
			status: '',
		};
		state.isEdit = false;
	}

	function get() {
		nullable();
		http
			.get('/ptk/tabungan', state.filter)
			.then((res) => {
				state.data.totalCount = parseInt(res.headers.get('X-Total-Count'));
				state.print_url = res.headers.get('X-Print-Url');
				state.excel_url = res.headers.get('X-Excel-Url');
				return res.json();
			})
			.then((res) => (state.data.items = res));
	}

	function save() {
		const request = { ...state.req };
		if (state.req.status == '2') {
			request.nominal = 0 - state.req.nominal;
		}

		if (state.isEdit) {
			http
				.put('/ptk/tabungan/' + state.req.id, request)
				.then((res) => res.json())
				.then((res) => {
					notify('data berhasil disimpan');
					state.modal.save = false;
					get();
				})
				.catch((err) => notify(err, 'bg-red-400'));
		} else {
			http
				.post('/ptk/tabungan', request)
				.then((res) => res.json())
				.then((res) => {
					notify('data berhasil disimpan');
					state.modal.save = false;
					get();
				})
				.catch((err) => notify(err, 'bg-red-400'));
		}
	}

	function destroy() {
		http
			.delete('/ptk/tabungan/' + state.req.id)
			.then((res) => res.json())
			.then((res) => {
				notify('data berhasil dihapus');
				state.modal.delete = false;
				get();
			})
			.catch((err) => notify(err, 'bg-red-400'));
	}

	function getPtk() {
		http
			.get('/ptk')
			.then((res) => res.json())
			.then((res) => (state.data.ptk = res));
	}

	watch(
		() => JSON.parse(JSON.stringify(state.filter)),
		(val, old) => {
			if (val._page == old._page) {
				state.filter._page = 1;
			}

			get();
		},
	);

	onMounted(() => {
		get();
		getPtk();
	});
</script>

<template>
	<Card title="Laporan PTK" class="mb-4">
		<div class="form-field flex lg:flex-row flex-col items-center gap-x-3">
			<select v-model="state.filter._limit" class="!lg:w-1/4">
				<option value="5">5</option>
				<option value="10">10</option>
				<option value="20">20</option>
				<option value="50">50</option>
				<option value="100">100</option>
			</select>
			<select v-model="state.filter._ptk_id" class="!lg:w-1/4">
				<option value="">Semua PTK</option>
				<option v-for="item in state.data.ptk" :value="item.id">
					{{ item.kode }} - {{ item.nama }}
				</option>
			</select>
			<input type="date" v-model.lazy="state.filter._tanggal" class="!lg:w-1/2" />
		</div>
		<div class="p-4 bg-gray-50 rounded mb-4">Tanggal: {{ state.filter._tanggal != '' ? formatDate(state.filter._tanggal) : '-' }}</div>
		<div class="text-right mb-4 text-sm">
			<a target="_blank" :href="state.excel_url" class="mr-4 text-green-500">EXCEL</a>
			<a target="_blank" :href="state.print_url" class="text-red-500">PRINT</a>
		</div>
		<Table
			:keys="{
				No: 'no',
				Kode: 'kode',
				PTK: 'ptk',
				Jabatan: 'jabatan',
				Id: 'status',
				Nominal: 'nominal',
				Operator: 'operator',
			}"
			:items="state.data.items"
		>
			<template #no="{ index }">{{ index + 1 }}</template>

			<template #kode="{ item }">
				<template v-if="item.ptk">
					{{ item.ptk.kode }}
				</template>
			</template>
			
			<template #ptk="{ item }">
				<template v-if="item.ptk">
					{{ item.ptk.nama }}
				</template>
			</template>

			<template #jabatan="{ item }">
				<template v-if="item.ptk">
					{{ item.ptk.jabatan }}
				</template>
			</template>

			<template #status="{ item }">
				{{ item.nominal > 0 ? '1' : '2' }}
			</template>

			<template #operator="{ item }">{{ item.user.nama }}</template>

			<template #nominal="{ item }">
				{{ formatMoney(item.nominal) }}
			</template>
		</Table>
		<div class="flex items-center justify-end">
			<Pagination
				:limit="state.filter._limit"
				:total-count="state.data.totalCount"
				v-model="state.filter._page"
			></Pagination>
		</div>
	</Card>

	<Modal v-model="state.modal.save">
		<Card
			:title="state.isEdit ? 'Edit Tabungan PTK' : 'Tambah Tabungan PTK'"
			class="!max-w-full !w-550px"
		>
			<Form :values="state.req" :rules="rules" @submit="save" class="form-field">
				<label for="">PTK</label>
				<select v-model="state.req.ptk_id">
					<option disabled value="">Pilih PTK</option>
					<option v-for="item in state.data.ptk" :value="item.id">
						{{ item.kode }} - {{ item.nama }}
					</option>
				</select>
				<label for="">Nominal</label>
				<input type="number" v-model="state.req.nominal" placeholder="Masukkan Nominal" />

				<div class="mt-5 text-right">
					<button :class="{ '!bg-red-500': state.req.status == '2' }" type="submit">
						{{ state.req.status == '1' ? 'Simpan' : 'Ambil' }}
					</button>
				</div>
			</Form>
		</Card>
	</Modal>

	<Modal v-model="state.modal.delete">
		<Card title="Hapus Tabungan PTK" class="max-w-full w-550px">
			<form @submit.prevent="destroy" class="form-field">
				<p>Anda yakin akan menghapus data terpilih?</p>

				<div class="mt-5 text-right">
					<button class="!bg-red-500" type="submit">Hapus</button>
				</div>
			</form>
		</Card>
	</Modal>
</template>
