<script setup>
	import { reactive } from 'vue';
	import Form, { rule } from '../components/Form.vue';
	import { http, notify } from '../lib';
	import logo from "../images/smk.png";

	const state = reactive({
		req: {
			username: '',
			password: '',
		},
		showPassword: false,
	});

	const rules = {
		username: rule.string().required(),
		password: rule.string().required(),
	};

	function login() {
		http
			.post('/login', state.req)
			.then((res) => res.json())
			.then((res) => {
				if (res[0]) {
					notify(res, 'bg-red-400');
				} else {
					notify('berhasil login');
					state.req = {};

					localStorage.setItem('id', res.id);
					localStorage.setItem('role', res.role);

					setTimeout(() => {
						window.location.href = window.location.origin + window.location.pathname;
					}, 400);
				}
			})
			.catch((err) => notify(err, 'bg-red-400'));
	}
</script>

<template>
	<div class="min-h-screen bg-gradient-to-b from-[#202b46] to-gray-800 flex items-center justify-center p-5">
		<!-- <div class="absolute bottom-0 left-0 right-0 h-[50vh] bg-gray-100">
		</div> -->
		<div class="rounded-xl relative shadow-lg flex justify-between w-full lg:w-3/4 overflow-hidden">
			<div class="grow lg:flex hidden w-full bg-black bg-opacity-25 items-center justify-center" style="backdrop-filter: blur(8px);">
				<img :src="logo" alt="Logo SMK" class="w-[200px]">
			</div>
			<div class="grow p-10 lg:py-20 bg-white">
				<div class="text-xl mb-6 border-b pb-3 font-semibold">Login</div>
				<Form @submit="login" class="form-field" :values="state.req" :rules="rules">
					<label for="">Username</label>
					<input type="text" placeholder="Masukkan Username" v-model="state.req.username" />
					<label for="">Password</label>
					<input
						:type="state.showPassword ? 'text' : 'password'"
						placeholder="Masukkan Password"
						v-model="state.req.password"
					/>
					<input type="checkbox" v-model="state.showPassword" /> Lihat Password
		
					<div class="mt-8">
						<button type="submit" class="w-full ripple">Login</button>
					</div>
				</Form>
			</div>
		</div>
	</div>
</template>

<style scoped>
	.animate-up {
		animation: animate-up .5s;
	}

	.animate-down {
		animation: animate-down .5s;
	}

	@keyframes animate-up {
		from {
			transform: translateY(100%);
		}
	}

	@keyframes animate-down {
		from {
			transform: translateY(-100%);
		}
	}
</style>