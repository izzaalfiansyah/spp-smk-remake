<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SiswaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $total = 1000;
        $faker = \Faker\Factory::create('id_ID');

        $jurusan_builder = new \App\Models\Jurusan();
        $jurusan_total = $jurusan_builder->count();
        $jurusan = $jurusan_builder->get();

        for ($i=0; $i < $total; $i++) { 
            \App\Models\Siswa::create([
                'nisn' => '450688' . $faker->randomNumber(7, true),
                'nama' => $faker->name(),
                'kelas' => ['X', 'XI', 'XII'][random_int(0, 2)],
                'jurusan_kode' => $jurusan[random_int(0, $jurusan_total - 1)]->kode,
                'rombel' => random_int(1, 3)
            ]);
        }
    }
}
