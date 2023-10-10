<script setup>
import { onMounted, provide, ref } from 'vue';
import Admin from './layouts/Admin.vue';
import { auth, http } from './lib';
import Login from './views/login.vue';

const expired = ref(0);

const userAuth = ref({});

async function Authenticate() {
	const res = await http.get('/user/' + auth.id).then((res) => res.json());
	userAuth.value = res;
}

provide('user-auth', { userAuth });

async function checkExpired() {
	const res = await http.get('/expired').then(res => res.json());
	expired.value = res.data;
	console.log(expired);
}

onMounted(async () => {
	await checkExpired();
	await Authenticate();
});
</script>

<template>
	<div class="fixed top-0 left-0 right-0 pointer-events-none" style="z-index: 9999999999">
		<div class="h-3px bg-blue-500 opacity-0" id="top-loading" style="width: 0%; transition: 500ms ease 0ms;"></div>
	</div>
	<template v-if="expired">
		<div class="h-screen flex items-center justify-center bg-black">
			<div class="text-center rounded-lg text-white p-10 px-20">
				<div class="mb-5 text-5xl">
					🤡
				</div>
				<div class="text-5xl font-semibold">
					<span>I AM</span>
					{{ ' ' }}
					<span>THE</span>
					{{ ' ' }}
					<span>MAGIC<span class="text-blue-500">IAN</span>!</span>
				</div>
				<div class="mt-5">This page has been expired by me as the developer. hahaha</div>
				<div class="mt-5 text-blue-500">
					#respectmeasdeveloper
				</div>
			</div>
		</div>
	</template>
	<template v-else>
		<Admin v-if="auth.id"></Admin>
		<Login v-else></Login>
	</template>
</template>
