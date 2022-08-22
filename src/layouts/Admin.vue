<script setup>
	import { reactive } from 'vue';
	import { RouterView, RouterLink } from 'vue-router';
	import Modal from '../components/Modal.vue';
	import Card from '../components/Card.vue';
	import { auth, notify } from '../lib';

	const state = reactive({
		sidebarOpen: false,
		modalLogout: false,
	});

	const sidebarItems = [
		{
			section: 'Main menu',
			items: [
				{
					title: 'Dashboard',
					icon: 'home',
					path: '/',
				},
				{
					title: 'Akun',
					icon: 'account_circle',
					path: '/akun',
				},
				{
					title: 'Logout',
					icon: 'logout',
					path: '/',
					event: () => {
						state.modalLogout = true;
					},
				},
			],
		},
		{
			section: 'Data Master',
			items:
				auth.role == '1'
					? [
							{
								title: 'Data User',
								icon: 'verified_user',
								path: '/data/user',
							},
							{
								title: 'Data Siswa',
								icon: 'supervisor_account',
								path: '/data/siswa',
							},
							{
								title: 'Data Jurusan',
								icon: 'account_tree',
								path: '/data/jurusan',
							},
							{
								title: 'Data Biaya Lain',
								icon: 'account_balance',
								path: '/data/biaya-lain',
							},
							{
								title: 'Data Pembayaran',
								icon: 'sd_card',
								path: '/data/pembayaran',
							},
							{
								title: 'Data PTK',
								icon: 'account_balance',
								path: '/data/ptk',
							},
					  ]
					: [
							{
								title: 'Data Biaya Lain',
								icon: 'account_balance',
								path: '/data/biaya-lain',
							},
							{
								title: 'Data Pembayaran',
								icon: 'sd_card',
								path: '/data/pembayaran',
							},
							{
								title: 'Data PTK',
								icon: 'account_balance',
								path: '/data/ptk',
							},
					  ],
		},
		{
			section: 'Laporan',
			items: [
				{
					title: 'Laporan SPP',
					icon: 'account_balance_wallet',
					path: '/laporan/spp',
				},
				{
					title: 'Laporan Biaya Lain',
					icon: 'account_balance',
					path: '/laporan/biaya-lain',
				},
				{
					title: 'Laporan Tabungan',
					icon: 'credit_card',
					path: '/laporan/tabungan',
				},
				{
					title: 'Laporan Tunggakan',
					icon: 'sd_card',
					path: '/laporan/tunggakan',
				},
				{
					title: 'Laporan PTK',
					icon: 'account_balance',
					path: '/laporan/ptk',
				},
			],
		},
	];

	function logout() {
		localStorage.removeItem('id');
		localStorage.removeItem('role');

		notify('berhasil logout');

		setTimeout(() => {
			window.location.href = window.location.origin + window.location.pathname;
		}, 400);
	}
</script>

<template>
	<div class="min-h-screen bg-gray-50">
		<div
			class="h-16 bg-gradient-to-r from-blue-500 to-blue-600 text-white shadow sticky rounded-b-xl top-0 flex items-center z-20 justify-between p-4"
		>
			<div class="text-xl">BANK MINI SEMARAK</div>
			<div class="text-right">
				<a
					@click="state.sidebarOpen = !state.sidebarOpen"
					class="material-icons !text-xl inline-block cursor-pointer !lg:hidden"
					>menu</a
				>
				<a
					@click="state.modalLogout = true"
					class="material-icons !text-xl inline-block cursor-pointer ml-3"
					>logout</a
				>
			</div>
		</div>
		<div class="p-4 flex w-full">
			<div class="lg:w-350px lg:pr-4">
				<div
					v-show="state.sidebarOpen"
					@click="state.sidebarOpen = !state.sidebarOpen"
					class="bg-black z-10 fixed top-0 left-0 right-0 bottom-0 bg-opacity-25 lg:hidden"
				></div>
				<div
					:class="`bg-white lg:rounded rounded-t-3xl z-20 fixed lg:sticky lg:top-20 bottom-0 left-0 right-0 w-full p-4 shadow h-85vh transition duration-500 overflow-y-auto transform lg:translate-y-0 translate-y-full ${
						state.sidebarOpen ? '!translate-y-0' : ''
					}`"
				>
					<template v-for="item in sidebarItems">
						<div
							class="uppercase text-xs text-center my-2 lg:text-left font-semibold pl-2 rounded p-1 bg-blue-500 text-white shadow-sm"
						>
							{{ item.section }}
						</div>

						<template v-for="link in item.items">
							<button
								v-if="link.event"
								class="flex w-full p-2 py-1.5 hover:bg-gray-50 items-center transition rounded hover:shadow block mb-1.5"
								@click="link.event"
							>
								<div class="mr-3 text-center">
									<i class="material-icons-outlined mt-1">{{ link.icon }}</i>
								</div>
								<div class="flex-1 text-left">{{ link.title }}</div>
							</button>
							<RouterLink
								v-else
								class="flex p-2 py-1.5 hover:bg-gray-50 items-center transition rounded hover:shadow block mb-1.5"
								:class="{ 'bg-gray-50 shadow': $route.path == link.path }"
								@click="state.sidebarOpen = !state.sidebarOpen"
								:to="link.path"
							>
								<div class="mr-3 text-center">
									<i class="material-icons-outlined mt-1">{{ link.icon }}</i>
								</div>
								<div class="flex-1">{{ link.title }}</div>
							</RouterLink>
						</template>
					</template>
				</div>
			</div>
			<div class="lg:w-[calc(100%-350px)] w-full custom-main-admin">
				<RouterView></RouterView>
			</div>
		</div>
	</div>
	<Modal v-model="state.modalLogout">
		<Card title="Logout" class="w-550px max-w-full">
			<p>Anda yakin akan logout?</p>

			<div class="mt-5 text-right form-field">
				<button @click="logout" class="!bg-red-500">Logout</button>
			</div>
		</Card>
	</Modal>
</template>

<style>
	.custom-main-admin > * {
		animation: animate-custom-main-admin 0.5s;
	}

	@keyframes animate-custom-main-admin {
		from {
			transform: skew(-45deg) translateX(100%);
		}
	}
</style>
