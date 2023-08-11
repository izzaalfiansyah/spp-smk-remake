<script setup>
	import { onMounted, reactive, watch } from 'vue';
	import Card from '../../components/Card.vue';
	import Table from '../../components/Table.vue';
	import { http, notify } from '../../lib';
	import Modal from '../../components/Modal.vue';
	import Form, { rule } from '../../components/Form.vue';
	import Pagination from '../../components/Pagination.vue';
	import TabunganPtk from './tabungan-ptk.vue';

	const state = reactive({
		isEdit: false,
		data: {
			items: [],
			totalCount: 0,
		},
		modal: {
			save: false,
			delete: false,
		},
		req: {
			id: '',
			kode: '',
			nama: '',
			jabatan: '',
		},
		filter: {
			_limit: 10,
			_page: 1,
			q: '',
		},
	});

	const rules = {
		kode: rule.string().required(),
		nama: rule.string().required(),
		jabatan: rule.string().nullable().optional(),
	};

	function nullable() {
		state.req = {
			id: '',
			kode: '',
			nama: '',
			jabatan: '',
		};
		state.isEdit = false;
	}

	function get() {
		nullable();
		http
			.get('/ptk', state.filter)
			.then((res) => {
				state.data.totalCount = parseInt(res.headers.get('X-Total-Count'));
				return res.json();
			})
			.then((res) => (state.data.items = res));
	}

	function save() {
		if (state.isEdit) {
			http
				.put('/ptk/' + state.req.id, state.req)
				.then((res) => res.json())
				.then((res) => {
					notify('data berhasil disimpan');
					state.modal.save = false;
					get();
				})
				.catch((err) => notify(err, 'bg-red-400'));
		} else {
			http
				.post('/ptk', state.req)
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
			.delete('/ptk/' + state.req.id)
			.then((res) => res.json())
			.then((res) => {
				notify('data berhasil dihapus');
				state.modal.delete = false;
				get();
			})
			.catch((err) => notify(err, 'bg-red-400'));
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
	});
</script>

<template>
	<div class="mb-4 pb-2 border-b text-xl">Data PTK</div>
	<Card title="List PTK" class="mb-4">
		<div class="mb-4 p-3 rounded bg-gray-100 form-field">
			<button
				type="button"
				@click="
					nullable();
					state.modal.save = true;
				"
			>
				+ Tambah
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
			<input type="text" placeholder="Cari" v-model.lazy="state.filter.q" class="!lg:w-1/2" />
		</div>
		<Table
			:keys="{
				Kode: 'kode',
				Nama: 'nama',
				Jabatan: 'jabatan',
				Opsi: 'opsi',
			}"
			:items="state.data.items"
		>
			<template #opsi="{ item }">
				<div class="bg-white rounded inline-block px-1">
					<button
						@click="
							state.req = JSON.parse(JSON.stringify(item));
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

	<!-- <TabunganPtk></TabunganPtk> -->

	<Modal v-model="state.modal.save">
		<Card :title="state.isEdit ? 'Edit PTK' : 'Tambah PTK'" class="!max-w-full !w-550px">
			<Form :values="state.req" :rules="rules" @submit="save" class="form-field">
				<label for="">Kode</label>
				<input type="text" v-model="state.req.kode" placeholder="Masukkan Kode" />
				<label for="">Nama PTK</label>
				<input type="text" v-model="state.req.nama" placeholder="Masukkan Nama PTK" />
				<label for="">Jabatan</label>
				<input type="text" v-model="state.req.jabatan" placeholder="Masukkan Jabatan" />

				<div class="mt-5 text-right">
					<button type="submit">Simpan</button>
				</div>
			</Form>
		</Card>
	</Modal>

	<Modal v-model="state.modal.delete">
		<Card title="Hapus PTK" class="max-w-full w-550px">
			<form @submit.prevent="destroy" class="form-field">
				<p>
					Anda yakin akan menghapus ptk dengan kode <strong>{{ state.req.kode }}</strong
					>?
				</p>

				<div class="mt-5 text-right">
					<button class="!bg-red-500" type="submit">Hapus</button>
				</div>
			</form>
		</Card>
	</Modal>
</template>
