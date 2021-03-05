<?php

namespace App\Imports;

use App\Models\Barang;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class BarangsImport implements ToModel, WithHeadingRow, WithValidation
{
    use Importable;

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        return new Barang([
            'kode_barang'   => $row['kode_barang'],
            'nama_barang'   => $row['nama_barang'],
            'detail_barang' => $row['detail_barang'],
            'kondisi_barang'    => $row['kondisi_barang'],
            'harga_awal_barang' => $row['harga_awal_barang'],
            'photo_barang'  => empty($row['photo_barang']) == true ? '' : $row['photo_barang'],
            'lokasi_barang' => $row['lokasi_barang'],
            'status_barang' =>  $row['status_barang']
        ]);
    }

    public function rules(): array
    {
        return [
            'kode_barang' => 'unique:barangs,kode_barang',
            'harga_awal_barang' => 'integer',
        ];
    }
}
