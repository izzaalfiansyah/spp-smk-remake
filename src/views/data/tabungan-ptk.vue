<script setup>
	import { onMounted, reactive, watch, watchEffect } from 'vue';
	import Card from '../../components/Card.vue';
	import Table from '../../components/Table.vue';
	import { auth, formatMoney, http, notify } from '../../lib';
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
			_limit: 5,
			_page: 1,
			_ptk_id: '',
		},
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
			if (val.page !== old.page) {
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
	<Card title="Tabungan PTK" class="mb-4">
		<div class="mb-4 p-3 rounded bg-gray-100 form-field">
			<button
				type="button"
				class="mr-2"
				@click="
					nullable();
					state.req.status = '1';
					state.modal.save = true;
				"
			>
				+ Tambah
			</button>
			<button
				type="button"
				class="!bg-red-500"
				@click="
					nullable();
					state.req.status = '2';
					state.modal.save = true;
				"
			>
				- Ambil
			</button>
		</div>
		<div class="form-field">
			<select v-model="state.filter._limit" class="!mr-3 !w-20">
				<option value="5">5</option>
				<option value="10">10</option>
				<option value="20">20</option>
				<option value="50">50</option>
				<option value="100">100</option>
			</select>
			<select v-model="state.filter._ptk_id" class="!lg:w-1/2">
				<option value="">Semua PTK</option>
				<option v-for="item in state.data.ptk" :value="item.id">
					{{ item.kode }} - {{ item.nama }}
				</option>
			</select>
		</div>
		<Table
			:keys="{
				No: 'no',
				PTK: 'ptk',
				Id: 'status',
				Nominal: 'nominal',
				Operator: 'operator',
				Opsi: 'opsi',
			}"
			:items="state.data.items"
		>
			<template #no="{ index }">{{ index + 1 }}</template>

			<template #ptk="{ item }">{{ item.ptk.kode }} - {{ item.ptk.nama }}</template>

			<template #status="{ item }">
				{{ item.nominal > 0 ? '1' : '2' }}
			</template>

			<template #operator="{ item }">{{ item.user.nama }}</template>

			<template #nominal="{ item }">
				{{ formatMoney(item.nominal) }}
			</template>

			<template #opsi="{ item }">
				<div class="bg-white rounded inline-block px-1">
					<button
						@click="
							state.req = JSON.parse(JSON.stringify(item));
							state.req.status = item.nominal > 0 ? '1' : '2';
							state.isEdit = true;
							state.modal.save = true;
						"
						class="material-icons-outlined text-blue-500 !text-xl"
					>
						edit
					</button>
					<button
						@click="
							state.req = JSON.parse(JSON.stringify(item));
							state.modal.delete = true;
						"
						class="material-icons-outlined text-red-500 !text-xl"
					>
						delete
					</button>
				</div>
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
