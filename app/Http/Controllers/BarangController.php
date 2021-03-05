<?php

namespace App\Http\Controllers;

use App\Models\Barang;

use Illuminate\Http\Request;
use App\Imports\BarangsImport;
use Maatwebsite\Excel\Facades\Excel;

class BarangController extends Controller
{

    public function getAllBarangs()
    {
        $dataBarang = Barang::all();

        return response()->json([
            'message' => 'Data Berhasil di Ambil !',
            'data'     => $dataBarang
        ], 200);
    }

    public function createBarang(Request $request)
    {
        // Pesan Jika Error
        $messages = [
            'kode_barang.required'   => 'Masukkan Kode Barang !',
            'kode_barang.unique'     => 'Kode Barang Sudah di Ambil !',
            'nama_barang.required'   => 'Masukkan Nama Barang !',
            'detail_barang.required'   => 'Masukkan Detail Barang !',
            'kondisi_barang.required'   => 'Masukkan Kondisi Barang !',
            'harga_awal_barang.required'   => 'Masukkan Harga Awal Barang !',
            'lokasi_barang.required' => 'Masukkan Lokasi Barang !',
        ];
        
        //Validasi Data
        $validasiData = $this->validate($request, [
            'kode_barang'   => 'required|unique:barangs',
            'nama_barang'   => 'required',
            'detail_barang'   => 'required',
            'kondisi_barang'   => 'required',
            'harga_awal_barang'   => 'required',
            'lokasi_barang' => 'required',
        ], $messages);

        // Get Data Inputan
        $kode_barang = $request->input('kode_barang');
        $nama_barang = $request->input('nama_barang');
        $detail_barang = $request->input('detail_barang');
        $kondisi_barang = $request->input('kondisi_barang');
        $harga_awal_barang = $request->input('harga_awal_barang');
        $lokasi_barang = $request->input('lokasi_barang');

        $cekAdaPhotoBarang = $request->hasFile('photo_barang') ? true : false ;

        // Simpan 
        $dataBarang = new Barang();
        $dataBarang->kode_barang = $kode_barang;
        $dataBarang->nama_barang = $nama_barang;
        $dataBarang->detail_barang = $detail_barang;
        $dataBarang->kondisi_barang = $kondisi_barang;
        $dataBarang->harga_awal_barang = $harga_awal_barang;
        $dataBarang->lokasi_barang = $lokasi_barang;
        $dataBarang->status_barang = 'Belum Terjual';
        $dataBarang->photo_barang = '';

        // Untuk foto Barang
        if($cekAdaPhotoBarang) {
            //Nama Asli File
            $fileNameOriginal = $request->fileName;

            //Naming
            $photoBarangName  = rand().'-'.$fileNameOriginal;

            //Ekstensi
            $getFileExt     = explode('.', $fileNameOriginal);
            $file_ext       = end($getFileExt);

            $tipeValid      = [
                'jpg', 'png', 'jpeg'
            ];

            foreach ($tipeValid as $tipe) {
                if (!in_array($file_ext, $tipeValid)) {
                return response()->json([
                    'message' => 'Tipe File Tidak di Dukung !'
                ], 415);
                }
            }

            $request->file('photo_barang')->storeAs('data-aplikasi/photo-barang', $photoBarangName);

            $dataBarang->photo_barang = $photoBarangName;
        }

        $dataBarang->save();

        return response()->json([
            'message' => 'data berhasil di Tambah !',
            'data'  => $dataBarang
        ], 200);

    }

    public function getSpecificBarang($id)
    {
        // Cari Data
        $dataBarang = Barang::where('kode_barang', $id)->first();

        // Cek Data Ada atau Tidak
        if(!$dataBarang) {
            return response()->json([
                'message' => 'Data Tidak Ada !',
            ], 404);
        }

        return response()->json([
            'messsage' => 'data berhasil di ambil !',
            'data'  => $dataBarang
        ], 200);
    }

    public function updateBarang(Request $request, $id)
    {
        // Barang
        $dataBarang = Barang::where('kode_barang', $id)->first();

        if (!$dataBarang) {
            return response()->json([
                'message' => 'Barang Tidak Ada !',
            ], 403);
        }

        // Data Memiliki Relasi
        if($dataBarang->lelangs()->exists()) 
        {
            return response()->json([
                'message' => 'Barang Memiliki Relasi !',
            ], 406);
        } 

        $cekAdaPhotoBarang = $request->hasFile('photo_barang') ? true : false ;

        // Pesan Jika Error
        $messages = [
            'kode_barang.required'   => 'Masukkan Kode Barang !',
            'kode_barang.unique'     => 'Kode Barang Sudah di Ambil !',
            'nama_barang.required'   => 'Masukkan Nama Barang !',
            'detail_barang.required'   => 'Masukkan Detail Barang !',
            'kondisi_barang.required'   => 'Masukkan Kondisi Barang !',
            'harga_awal_barang.required'   => 'Masukkan Harga Awal Barang !',
            'lokasi_barang.required' => 'Masukkan Lokasi Barang !',
        ];
        
        //Validasi Data
        $validasiData = $this->validate($request, [
            'kode_barang'   => 'required|unique:barangs,kode_barang,' . $dataBarang->id,
            'nama_barang'   => 'required',
            'detail_barang'   => 'required',
            'kondisi_barang'   => 'required',
            'harga_awal_barang'   => 'required',
            'lokasi_barang' => 'required',
        ], $messages);

        // Get Data Inputan
        $kode_barang = $request->input('kode_barang');
        $nama_barang = $request->input('nama_barang');
        $detail_barang = $request->input('detail_barang');
        $kondisi_barang = $request->input('kondisi_barang');
        $harga_awal_barang = $request->input('harga_awal_barang');
        $lokasi_barang = $request->input('lokasi_barang');
        $status_barang = $request->input('status_barang');

        // Simpan Inputan
        $dataBarang->kode_barang = $kode_barang;
        $dataBarang->nama_barang = $nama_barang;
        $dataBarang->detail_barang = $detail_barang;
        $dataBarang->kondisi_barang = $kondisi_barang;
        $dataBarang->harga_awal_barang = $harga_awal_barang;
        $dataBarang->status_barang = $status_barang;

        // Untuk foto Barang

        if($cekAdaPhotoBarang) {
            //Nama Asli File
            $fileNameOriginal = $request->fileName;

            //Naming
            $photoBarangName  = rand().'-'.$fileNameOriginal;

            //Ekstensi
            $getFileExt     = explode('.', $fileNameOriginal);
            $file_ext       = end($getFileExt);

            $tipeValid      = [
                'jpg', 'png', 'jpeg'
            ];

            foreach ($tipeValid as $tipe) {
                if (!in_array($file_ext, $tipeValid)) {
                return response()->json([
                    'message' => 'Tipe File Tidak di Dukung !'
                ], 415);
                }
            }

            if (empty($dataBarang->photo_barang)) {
                $request->file('photo_barang')->storeAs('data-aplikasi/photo-barang', $photoBarangName);

                $dataBarang->photo_barang = $photoBarangName;
            } else {
                $photoBarangLama = $dataBarang->photo_barang;
                unlink(storage_path('app/data-aplikasi/photo-barang/' . $photoBarangLama));

                $request->file('photo_barang')->storeAs('data-aplikasi/photo-barang', $photoBarangName);
                $dataBarang->photo_barang = $photoBarangName;
            }

        }
        // Simpan

        $dataBarang->save();

        return response()->json([
            'message' => 'data berhasil di ubah !',
            'data'  => $dataBarang
        ], 200);


    }
    

    public function deleteBarang($id)
    {
        $dataBarang = Barang::where('kode_barang', $id)->first();

        // Cek Data Ada atau Tidak
        if(!$dataBarang) {
            return response()->json([
                'message' => 'Data Tidak Ada !',
            ], 404);
        }

        // Data Memiliki Relasi
        if($dataBarang->lelangs()->exists()) 
        {
            return response()->json([
                'message' => 'Barang Memiliki Relasi !',
            ], 406);
        } 

        // Jika Ada photo
        if (!empty($dataBarang->photo_barang)) {
            unlink(storage_path('app/data-aplikasi/photo-barang/' . $dataBarang->photo_barang));
        } 

        // Delete records
        $dataBarang->delete();

        return response()->json([
            'message' => 'Data Berhasil di Hapus',
        ], 200);

    }

    public function importBarang(Request $request)
    {
        $file = $request->file('file_barang'); //GET FILE
        
        try {

            Excel::import(new BarangsImport, $file); //IMPORT FILE

            return response()->json([
                'message' => 'Data Berhasil di Import !',
            ], 200);

        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
     
            return response()->json([
                'message' => $failures,
            ], 422);
        }

    }




}
