<script setup>
	import { onMounted, reactive, watch } from 'vue';
	import Card from '../../components/Card.vue';
	import Modal from '../../components/Modal.vue';
	import Table from '../../components/Table.vue';
	import { http, notify, readFile } from '../../lib';
	import Pagination from '../../components/Pagination.vue';
	import Form, { rule } from '../../components/Form.vue';
	import Loading from '../../components/Loading.vue';

	const state = reactive({
		isEdit: false,
		isImport: false,
		modal: {
			save: false,
			delete: false,
			import: false,
		},
		data: {
			items: [],
			totalCount: 0,
		},
		jurusan: [],
		filter: {
			_limit: 10,
			_page: 1,
			_jurusan_kode: '',
			_kelas: '',
			q: '',
		},
		import: {
			file: '',
		},
		req: {
			nisn: '',
			nama: '',
			kelas: '',
			jurusan_kode: '',
			rombel: '1',
			diskon_spp: 0,
			diskon_biaya_lain: 0,
		},
		fileImport: '',
	});

	const rules = {
		nisn: rule.string().min(8).required(),
		nama: rule.string().required(),
		kelas: rule.string().required(),
		jurusan_kode: rule.string().required(),
		rombel: rule.number().required(),
		diskon_spp: rule.number().min(0).max(100),
		diskon_biaya_lain: rule.number().min(0).max(100),
	};

	function nullable() {
		state.req = {
			nisn: '',
			nama: '',
			kelas: '',
			jurusan_kode: '',
			rombel: '1',
			diskon_spp: 0,
			diskon_biaya_lain: 0,
		};
	}

	function get() {
		nullable();
		http
			.get('/siswa', state.filter)
			.then((res) => {
				state.data.totalCount = parseInt(res.headers.get('X-Total-Count'));
				state.fileImport = res.headers.get('X-Import-File');
				return res.json();
			})
			.then((res) => {
				state.data.items = res;
			})
			.catch((err) => {
				notify(err, 'bg-red-400');
			});
	}

	function save() {
		state.isEdit
			? http
					.put('/siswa/by-nisn', state.req)
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
					.post('/siswa', state.req)
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
			.delete('/siswa/' + state.req.old_nisn)
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

	function handleImport() {
		state.isImport = true;
		http
			.post('/siswa/import', state.import)
			.then((res) => res.json())
			.then((res) => {
				state.modal.import = false;
				notify(res);
				get();
			})
			.catch((err) => notify(err, 'bg-red-400'))
			.finally(() => (state.isImport = false));
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
		getData();
	});
</script>

<template>
	<div class="mb-4 pb-2 border-b text-xl">Data Siswa</div>
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
			<button @click="state.modal.import = true" class="!bg-green-500">Import</button>
		</div>
	</Card>
	<Card class="mb-4">
		<div class="form-field">
			<select class="!w-20 mr-2" v-model="state.filter._limit">
				<option value="5">5</option>
				<option value="10">10</option>
				<option value="20">20</option>
				<option value="50">50</option>
				<option value="100">100</option>
			</select>
			<select class="!w-40 mr-2" v-model="state.filter._kelas">
				<option value="">Semua Kelas</option>
				<option value="X">X</option>
				<option value="XI">XI</option>
				<option value="XII">XII</option>
				<option value="Alumni">Alumni</option>
			</select>
			<select class="!w-46 mr-2" v-model="state.filter._jurusan_kode">
				<option value="">Semua Jurusan</option>
				<option v-for="item in state.jurusan">{{ item.kode }}</option>
			</select>
			<input v-model.lazy="state.filter.q" type="text" class="!lg:w-1/2" placeholder="Cari..." />
		</div>
		<Table
			:keys="{
				NISN: 'nisn',
				Nama: 'nama',
				Kelas: 'kelas',
				Opsi: 'opsi',
			}"
			:items="state.data.items"
			class="mt-3"
		>
			<template #kelas="{ item }">
				{{ item.kelas }} {{ item.jurusan.kode }} {{ item.rombel }}
			</template>

			<template #opsi="{ item }">
				<div class="bg-white rounded inline-block px-1">
					<button
						@click="
							state.req = JSON.parse(JSON.stringify(item));
							state.req.old_nisn = state.req.nisn;
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
							state.req.old_nisn = state.req.nisn;
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
				:total-count="state.data.totalCount"
				v-model="state.filter._page"
				:limit="state.filter._limit"
			></Pagination>
		</div>
	</Card>

	<Modal v-model="state.modal.save">
		<Card :title="state.isEdit ? 'Edit Siswa' : 'Tambah Siswa'" class="w-550px max-w-full">
			<Form class="form-field" @submit="save" :values="state.req" :rules="rules">
				<label for="">NISN</label>
				<input type="text" placeholder="Masukkan NISN" v-model="state.req.nisn" />

				<label for="">Nama</label>
				<input type="text" placeholder="Masukkan Nama" v-model="state.req.nama" />

				<label for="">Kelas</label>
				<select v-model="state.req.kelas">
					<option value="">Pilih Kelas</option>
					<option v-for="item in ['X', 'XI', 'XII', 'Alumni']" :value="item">{{ item }}</option>
				</select>

				<label for="">Jurusan</label>
				<select v-model="state.req.jurusan_kode">
					<option value="">Pilih Jurusan</option>
					<option v-for="item in state.jurusan" :value="item.kode">
						{{ item.kode }} ({{ item.nama }})
					</option>
				</select>

				<label for="">Rombel</label>
				<input type="number" v-model="state.req.rombel" placeholder="Rombongan Belajar" />

				<div class="grid grid-cols-2 gap-3">
					<div>
						<label for="">Diskon SPP</label>
						<input type="number" v-model="state.req.diskon_spp" />
					</div>
					<div>
						<label for="">Diskon Biaya Lain</label>
						<input type="number" v-model="state.req.diskon_biaya_lain" />
					</div>
				</div>

				<div class="text-right mt-5">
					<button type="submit">Simpan</button>
				</div>
			</Form>
		</Card>
	</Modal>

	<Modal v-model="state.modal.delete">
		<Card title="Hapus Siswa" class="max-w-full w-550px">
			<form @submit.prevent="destroy" class="form-field">
				<p>
					Anda yakin akan menghapus siswa dengan nama <strong>{{ state.req.nama }}</strong
					>?
				</p>

				<div class="mt-5 text-right">
					<button class="!bg-red-500" type="submit">Hapus</button>
				</div>
			</form>
		</Card>
	</Modal>

	<Modal v-model="state.modal.import">
		<Card title="Import Siswa" class="max-w-full w-550px">
			<form @submit.prevent="handleImport" class="form-field relative">
				<Loading v-if="state.isImport"></Loading>
				<label for="">File</label>
				<input
					type="file"
					required
					class="border border-gray-100 w-full rounded p-2 bg-white"
					@change="
						(e) => {
							readFile(e.currentTarget.files[0])
								.then((res) => (state.import.file = res))
								.catch((err) => notify(err, 'bg-red-400'));
						}
					"
				/>
				<div class="mt-3 text-center">
					Unduh format file
					<a target="_blank" :href="state.fileImport" class="text-blue-500">disini</a>
				</div>

				<div class="mt-5 text-right">
					<button class="!bg-green-500" type="submit">Import</button>
				</div>
			</form>
		</Card>
	</Modal>
</template>
