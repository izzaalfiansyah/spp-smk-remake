<script setup>
	import { onMounted, reactive, watch } from 'vue';
	import Card from '../../components/Card.vue';
	import Modal from '../../components/Modal.vue';
	import Table from '../../components/Table.vue';
	import { http, notify, formatMoney } from '../../lib';
	import Form, { rule } from '../../components/Form.vue';

	const state = reactive({
		isEdit: false,
		modal: {
			save: false,
			delete: false,
		},
		data: {
			items: [],
		},
		jurusan: [],
		kelas: ['X', 'XI', 'XII'],
		req: {
			id: '',
			jenis: '',
			jumlah_bayar: 0,
			jurusan_data: [],
			kelas_data: [],
		},
	});

	const rules = {
		jenis: rule.string().required(),
		jumlah_bayar: rule.number().required(),
		jurusan_data: rule.array().required(),
		kelas_data: rule.array().required(),
	};

	function nullable() {
		state.req = {
			id: '',
			jenis: '',
			jumlah_bayar: 0,
			jurusan_data: state.jurusan.map((item) => item.kode),
			kelas_data: state.kelas,
		};
	}

	function get() {
		nullable();
		http
			.get('/biaya-lain')
			.then((res) => res.json())
			.then((res) => {
				state.data.items = res;
			})
			.catch((err) => {
				notify(err, 'bg-red-400');
			});
	}

	function save() {
		console.log(state.req);
		state.isEdit
			? http
					.put('/biaya-lain/' + state.req.id, state.req)
					.then((res) => res.json())
					.then((res) => {
						state.modal.save = false;
						notify('data berhasil diedit');
						get();
					})
					.catch((res) => {
						notify(res, 'bg-red-400');
					})
			: http
					.post('/biaya-lain', state.req)
					.then((res) => res.json())
					.then((res) => {
						state.modal.save = false;
						notify('data berhasil ditambah');
						get();
					})
					.catch((res) => {
						notify(res, 'bg-red-400');
					});
	}

	function destroy() {
		http
			.delete('/biaya-lain/' + state.req.id)
			.then((res) => res.json())
			.then((res) => {
				state.modal.delete = false;
				notify('data berhasil dihapus');
				get();
			})
			.catch((res) => {
				notify(res, 'bg-red-400');
			});
	}

	function getData() {
		http
			.get('/jurusan')
			.then((res) => res.json())
			.then((res) => {
				state.jurusan = res;
			});
	}

	onMounted(() => {
		get();
		getData();
	});
</script>

<template>
	<div class="mb-4 pb-2 border-b text-xl">Data Biaya Lain</div>
	<Card class="mb-4">
		<div class="form-field">
			<button
				@click="
					state.isEdit = false;
					nullable();
					state.modal.save = true;
				"
				class="mr-2"
			>
				+ Tambah
			</button>
		</div>
	</Card>
	<Card class="mb-4">
		<Table
			:keys="{
				Jenis: 'jenis',
				// Tahun: 'tahun',
				Jumlah_Bayar: 'jumlah_bayar',
				Jurusan: 'jurusan_data',
				Kelas: 'kelas_data',
				Opsi: 'opsi',
			}"
			:items="state.data.items"
		>
			<template #jurusan_data="{ item }">
				{{ item.jurusan_data.join(', ') }}
			</template>

			<template #tahun="{ item }">
				{{ item.created_at.slice(0, 4) }}
			</template>

			<template #kelas_data="{ item }">
				{{ item.kelas_data.join(', ') }}
			</template>

			<template #jumlah_bayar="{ item }">
				{{ formatMoney(item.jumlah_bayar) }}
			</template>

			<template #opsi="{ item }">
			<div class="inline-block px-1 bg-white rounded">
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
	</Card>

	<Modal v-model="state.modal.save">
		<Card
			:title="state.isEdit ? 'Edit Biaya Lain' : 'Tambah Biaya Lain'"
			class="w-550px max-w-full"
		>
			<Form class="form-field" @submit="save" :values="state.req" :rules="rules">
				<label for="">Jenis</label>
				<input type="text" placeholder="Masukkan Jenis" v-model="state.req.jenis" />

				<label for="">Jumlah Bayar</label>
				<input type="number" placeholder="Masukkan Jumlah Bayar" v-model="state.req.jumlah_bayar" />

				<div class="grid lg:grid-cols-2 grid-cols-1 gap-3">
					<div>
						<label for="">Jurusan</label>
						<div class="mb-2" v-for="item in state.jurusan">
							<input type="checkbox" v-model="state.req.jurusan_data" :value="item.kode" />
							{{ item.kode }}
						</div>
					</div>
					<div>
						<label for="">Kelas</label>
						<div class="mb-2" v-for="item in state.kelas">
							<input type="checkbox" v-model="state.req.kelas_data" :value="item" /> {{ item }}
						</div>
					</div>
				</div>

				<div class="text-right mt-5">
					<button type="submit">Simpan</button>
				</div>
			</Form>
		</Card>
	</Modal>

	<Modal v-model="state.modal.delete">
		<Card title="Hapus Biaya Lain" class="max-w-full w-550px">
			<form @submit.prevent="destroy" class="form-field">
				<p>
					Anda yakin akan menghapus pembayaran untuk <strong>{{ state.req.jenis }}</strong
					>?
				</p>

				<div class="mt-5 text-right">
					<button class="!bg-red-500" type="submit">Hapus</button>
				</div>
			</form>
		</Card>
	</Modal>
</template>
