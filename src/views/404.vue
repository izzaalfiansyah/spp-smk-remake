<template>
  <div class="bg-white rounded shadow p-4 h-85vh flex items-center justify-center">
    <div class="text-center">
      <div class="text-8xl font-semibold text-blue-500">
        40<span class="text-red-500">4</span>
      </div>
      <div class="mt-4">Halaman tidak tersedia. klik tombol dibawah untuk kembali.</div>
      <div class="form-field mt-2"><button @click="$router.replace('/')">Home</button></div>
    </div>
  </div>
</template>