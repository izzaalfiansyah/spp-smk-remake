<script setup>
	import { onMounted, reactive, computed } from 'vue';
	import Card from '../../components/Card.vue';
	import Modal from '../../components/Modal.vue';
	import Table from '../../components/Table.vue';
	import { http, notify } from '../../lib';
	import Form, { rule } from '../../components/Form.vue';

	const state = reactive({
		isEdit: false,
		modal: {
			save: false,
			delete: false,
		},
		data: {
			items: [],
			totalCount: 0,
		},
		req: {
			id: '',
			username: '',
			password: '',
			nama: '',
			role: '',
		},
	});

	const rules = computed(() => {
		return {
			username: rule.string().min(3).required(),
			password: state.req.id ? rule.string().optional() : rule.string().min(8).required(),
			nama: rule.string().required(),
			role: rule.string().required(),
		};
	});

	function nullable() {
		state.req = {
			id: '',
			username: '',
			password: '',
			nama: '',
			role: '',
		};
	}

	function get() {
		nullable();
		http
			.get('/user', state.filter)
			.then((res) => res.json())
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
					.put('/user/' + state.req.id, state.req)
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
					.post('/user', state.req)
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
			.delete('/user/' + state.req.id)
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

	onMounted(() => {
		get();
	});
</script>

<template>
	<div class="mb-4 pb-2 border-b text-xl">Data User</div>
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
				Nama: 'nama',
				Username: 'username',
				Role: 'role_detail',
				Opsi: 'opsi',
			}"
			:items="state.data.items"
			class="mt-3"
		>
			<template #opsi="{ item }">
				<div class="bg-white rounded inline-block px-1">
					<button
						@click="
							state.req = JSON.parse(JSON.stringify(item));
              state.req.password = '';
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
		<Card :title="state.isEdit ? 'Edit User' : 'Tambah User'" class="w-550px max-w-full">
			<Form class="form-field" @submit="save" :values="state.req" :rules="rules">
				<label for="">Username</label>
				<input type="text" placeholder="Masukkan Username" v-model="state.req.username" />

				<label for="">Password</label>
				<input type="password" placeholder="Masukkan Password" v-model="state.req.password" />
        <div v-if="state.req.id" class="hint mb-3">Kosongkan jika tidak ingin mengganti password</div>

				<label for="">Nama</label>
				<input type="text" placeholder="Masukkan Nama" v-model="state.req.nama" />

				<label for="">Role</label>
				<select v-model="state.req.role">
					<option value="">Pilih Role</option>
					<option value="1">superadmin</option>
					<option value="2">administrator</option>
				</select>

				<div class="text-right mt-5">
					<button type="submit">Simpan</button>
				</div>
			</Form>
		</Card>
	</Modal>

	<Modal v-model="state.modal.delete">
		<Card title="Hapus User" class="max-w-full w-550px">
			<form @submit.prevent="destroy" class="form-field">
				<p>
					Anda yakin akan menghapus user dengan nama <strong>{{ state.req.nama }}</strong
					>?
				</p>

				<div class="mt-5 text-right">
					<button class="!bg-red-500" type="submit">Hapus</button>
				</div>
			</form>
		</Card>
	</Modal>
</template>
