import { createRouter, createWebHashHistory } from 'vue-router';

const router = createRouter({
	history: createWebHashHistory(import.meta.env.BASE_URL),
	routes: [
		{
			path: '/',
			component: () => import('../views/index.vue'),
		},
		{
			path: '/akun',
			component: () => import('../views/akun.vue'),
		},
		{
			path: '/data/user',
			component: () => import('../views/data/user.vue'),
		},
		{
			path: '/data/siswa',
			component: () => import('../views/data/siswa.vue'),
		},
		{
			path: '/data/jurusan',
			component: () => import('../views/data/jurusan.vue'),
		},
		{
			path: '/data/biaya-lain',
			component: () => import('../views/data/biaya-lain.vue'),
		},
		{
			path: '/data/pembayaran',
			component: () => import('../views/data/pembayaran.vue'),
		},
		{
			path: '/data/pembayaran/:nisn',
			component: () => import('../views/data/pembayaran.vue'),
		},
		{
			path: '/data/ptk',
			component: () => import('../views/data/ptk.vue'),
		},
		{
			path: '/laporan/spp',
			component: () => import('../views/laporan/spp.vue'),
		},
		{
			path: '/laporan/biaya-lain',
			component: () => import('../views/laporan/biaya-lain.vue'),
		},
		{
			path: '/laporan/tabungan',
			component: () => import('../views/laporan/tabungan.vue'),
		},
		{
			path: '/login',
			component: () => import('../views/login.vue'),
		},
	],
});

export default router;
