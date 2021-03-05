<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

use App\Models\Barang;

class BarangTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'kode_barang'   => 'JKT_Clkn02',
                'nama_barang'   => 'Kabel Colokan',
                'detail_barang' => 'P: 2 M',
                'kondisi_barang'    => 'Good',
                'harga_awal_barang' => '19999',
                'photo_barang'  => '',
                'lokasi_barang' => 'Jakarta',
                'status_barang' => 'Belum Terjual'
            ],
            [
                'kode_barang'   => 'JKT_Clkn03',
                'nama_barang'   => 'Kabel Colokan',
                'detail_barang' => 'P: 1,5 M',
                'kondisi_barang'    => 'Good',
                'harga_awal_barang' => '19999',
                'photo_barang'  => '',
                'lokasi_barang' => 'Jakarta',
                'status_barang' => 'Belum Terjual'
            ]
        ];

        foreach ($data as $key) {
            Barang::create($key);
        }
    }
}
