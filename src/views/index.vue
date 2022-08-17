<script setup>
	import { onMounted, reactive } from 'vue';
	import Card from '../components/Card.vue';
	import { http, notify } from '../lib';

	const state = reactive({
		total: {
			siswa: 0,
			jurusan: 0,
		},
	});

	function get() {
		http
			.get('/siswa')
			.then((res) => res.json())
			.then((res) => (state.total.siswa = res.length))
			.catch((err) => notify(err, 'bg-red-400'));
		http
			.get('/jurusan')
			.then((res) => res.json())
			.then((res) => (state.total.jurusan = res.length));
	}

	onMounted(() => {
		get();
	});
</script>

<template>
	<div class="grid lg:grid-cols-2 grid-cols-1 gap-3">
		<Card title="Total Siswa" class="!bg-gradient-to-r from-green-400 to-green-500 text-white">
			<div class="flex items-center">
				<div class="text-5xl flex-1">{{ state.total.siswa }}</div>
				<div class="flex-1 text-right">
					<i class="material-icons !text-5xl">supervisor_account</i>
				</div>
			</div>
		</Card>
		<Card title="Total Jurusan" class="!bg-gradient-to-r from-blue-400 to-blue-500 text-white">
			<div class="flex items-center">
				<div class="text-5xl flex-1">{{ state.total.jurusan }}</div>
				<div class="flex-1 text-right">
					<i class="material-icons !text-5xl">account_tree</i>
				</div>
			</div>
		</Card>
	</div>
	<div class="mt-3">
		<Card>
			<div class="text-center p-6">
				<img
					src="http://smkpgri13sby.sch.id/wp-content/uploads/2017/09/xcropped-PGRI-1.png.pagespeed.ic_.f-537LOiGG.png"
					alt=""
					class="inline lg:w-300px lg:h-300px w-200px h-200px"
				/>
			</div>
		</Card>
	</div>
</template>
