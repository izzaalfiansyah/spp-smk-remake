<script setup>
	import { onMounted, reactive } from 'vue';
	import Card from '../../components/Card.vue';
	import { rule } from '../../components/Form.vue';
	import Modal from '../../components/Modal.vue';
	import { http, notify, formatMoney } from '../../lib';
	import Form from '../../components/Form.vue';

	const state = reactive({
		isEdit: false,
		modal: {
			save: false,
			delete: false,
		},
		data: {
			items: [],
		},
		req: {
			kode: '',
			nama: '',
			jumlah_spp: 0,
		},
	});

	const rules = {
		kode: rule.string().required(),
		nama: rule.string().required(),
		jumlah_spp: rule.number().required(),
	};

	function nullable() {
		state.req = {
			kode: '',
			nama: '',
			jumlah_spp: 0,
		};
	}

	function get() {
		nullable();
		http
			.get('/jurusan')
			.then((res) => res.json())
			.then((res) => (state.data.items = res))
			.catch((err) => {
				notify(err, 'bg-red-400');
			});;
	}

	function save() {
		state.isEdit
			? http
					.put('/jurusan/' + state.req.kode, state.req)
					.then((res) => res.json())
					.then((res) => {
						notify('data berhasil diedit');
						state.modal.save = false;
						get();
					})
					.catch((err) => notify(err, 'bg-red-400'))
			: http
					.post('/jurusan', state.req)
					.then((res) => res.json())
					.then((res) => {
						notify('data berhasil ditambah');
						state.modal.save = false;
						get();
					})
					.catch((err) => notify(err, 'bg-red-400'));
	}

	function destroy() {
		http
			.delete('/jurusan/' + state.req.kode)
			.then((res) => res.json())
			.then((res) => {
				notify('data berhasil dihapus');
				state.modal.delete = false;
				get();
			})
			.catch((err) => notify(err, 'bg-red-400'));
	}

	onMounted(() => {
		get();
	});
</script>

<template>
	<div class="mb-4 pb-2 border-b text-xl">Data Jurusan</div>

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
		<div class="grid lg:grid-cols-5 grid-cols-2 gap-3" v-if="state.data.items.length">
			<div class="border group border-gray-100" v-for="item in state.data.items">
				<div
					class="text-2xl transform group-hover:bg-blue-500 bg-gray-50 group-hover:text-white transition text-blue-500 font-semibold text-center py-10"
				>
					<span
						style="
							font-family: 'Lucida Sans', 'Lucida Sans Regular', 'Lucida Grande',
								'Lucida Sans Unicode', Geneva, Verdana, sans-serif;
						"
						class="text-shadow"
					>
						{{ item.kode }}
					</span>
				</div>
				<div class="p-2 text-center">
					<div class="border-b mb-2 pb-1 text-xs font-semibold">
						SPP: {{ formatMoney(item.jumlah_spp) }}
					</div>
					<button
						@click="
							state.modal.save = true;
							state.isEdit = true;
							state.req = JSON.parse(JSON.stringify(item));
						"
						class="material-icons-outlined text-blue-500 !text-xl"
					>
						edit
					</button>
					<button
						@click="
							state.modal.delete = true;
							state.req = JSON.parse(JSON.stringify(item));
						"
						class="material-icons-outlined text-red-500 !text-xl"
					>
						delete
					</button>
				</div>
			</div>
		</div>
		<div v-else class="p-3 border border-gray-100 text-center">Data tidak tersedia.</div>
	</Card>

	<Modal v-model="state.modal.save">
		<Card :title="state.isEdit ? 'Edit Jurusan' : 'Tambah Jurusan'" class="w-550px max-w-full">
			<Form class="form-field" :values="state.req" :rules="rules" @submit="save">
				<label for="">Kode</label>
				<input type="text" placeholder="Masukkkan Kode" v-model="state.req.kode" />
				<label for="">Nama</label>
				<input type="text" placeholder="Masukkan Nama" v-model="state.req.nama" />
				<label for="">Jumlah SPP</label>
				<input type="number" placeholder="Masukkan Jumlah SPP" v-model="state.req.jumlah_spp" />
				<label for="">Tabungan Wajib</label>
				<input type="number" disabled placeholder="Tabungan Wajib" value="15000" />

				<div class="text-right mt-5">
					<button>Simpan</button>
				</div>
			</Form>
		</Card>
	</Modal>

	<Modal v-model="state.modal.delete">
		<Card title="Hapus Jurusan" class="w-550px max-w-full">
			<form class="form-field" @submit.prevent="destroy">
				<p>
					Anda yakin menghapus jurusan <strong>{{ state.req.kode }} ({{ state.req.nama }})</strong>?
				</p>

				<div class="text-right mt-5">
					<button class="!bg-red-500">Hapus</button>
				</div>
			</form>
		</Card>
	</Modal>
</template>
