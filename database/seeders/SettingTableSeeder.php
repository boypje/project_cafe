<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('setting')->insert([
            'id_setting' => 1,
            'nama_usaha' => 'Warung Altari',
            'alamat' => 'Bluru Permai Blok FE-09',
            'telepon' => '085778121249',
            'path_logo' => '/img/altari.png'
        ]);
    }
}
