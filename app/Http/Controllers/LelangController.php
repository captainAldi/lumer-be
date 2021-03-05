<?php

namespace App\Http\Controllers;

use App\Models\Barang;
use App\Models\Lelang;
use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;

use App\Mail\LelangApproveEmail;

use App\Jobs\SendEmailLelangApproveJob;

class LelangController extends Controller
{

    public function getBarangsPerKota($id)
    {
        $dataBarang = Barang::where('lokasi_barang', $id)
                        ->where('status_barang', 'Belum Terjual')
                        ->get();

        return response()->json([
            'message' => 'Data Berhasil di Ambil !',
            'data'     => $dataBarang
        ], 200);
    }

    public function getSpecificBarang($id) {
        $dataBarang = Barang::where('kode_barang', $id)
                        ->where('status_barang', 'Belum Terjual')
                        ->get();

        return response()->json([
            'message' => 'Data Berhasil di Ambil !',
            'data'     => $dataBarang
        ], 200);
    }

    public function createPenawaran(Request $request)
    {
        // Pesan Jika Error
        $messages = [
            'harga_tawaran.required'   => 'Masukkan Harga Tawaran !',
        ];
        
        //Validasi Data
        $validasiData = $this->validate($request, [
            'harga_tawaran'   => 'required',
        ], $messages);


        // Get API Key
        $api_token = $request->header('Authorization');

        // Find User
        $user = User::where('api_token', $api_token)->first();

        // Get Data Inputan
        $penawar_id = $user->id;
        $barang_id = $request->input('id_barang');
        $harga_tawaran = (double)$request->input('harga_tawaran');
        $status_lelang = 'Waiting';

        // Cek Harga Dasar
        $barangTarget = Barang::where('id', $barang_id)->first();

        if(!$barangTarget) {
            return response()->json([
                'message' => 'Data Lelang Tidak Ada !',
            ], 404);
        }

        if($barangTarget->status_barang == 'Terjual') {
            return response()->json([
                'message' => 'Barang Sudah Terjual !',
            ], 406);
        }

        if($harga_tawaran < $barangTarget->harga_awal_barang) {
            return response()->json([
                'message' => 'Harga Penawaran Harus Lebih Tinggi dari Harga Asal !',
            ], 406);
        }

        // Simpan 
        $dataLelang = new Lelang();
        $dataLelang->penawar_id = $penawar_id;
        $dataLelang->barang_id = $barang_id;
        $dataLelang->harga_tawaran = $harga_tawaran;
        $dataLelang->status_lelang = $status_lelang;

        $dataLelang->save();

        return response()->json([
            'message' => 'Penawaran Berhasil di Ajukan !',
            'data'  => $dataLelang
        ], 200);

    }

    public function getAllPenawaran(Request $request)
    {
    
        // Cari Data
        $dataPenawaran = Lelang::with(['user', 'barang'])->get();

        return response()->json([
            'messsage' => 'data berhasil di ambil !',
            'data'  => $dataPenawaran
        ], 200);
    }

    public function getDataPenawaranSendiri(Request $request)
    {
        // Get API Key
        $api_token = $request->header('Authorization');

        // Find User
        $user = User::where('api_token', $api_token)->first();

        // Cari Data
        $dataPenawaran = Lelang::with(['user', 'barang'])
                            ->where('penawar_id', $user->id)->get();

        // Cek Data Ada atau Tidak
        if(!$dataPenawaran) {
            return response()->json([
                'message' => 'Data Tidak Ada !',
            ], 404);
        }

        return response()->json([
            'messsage' => 'data berhasil di ambil !',
            'data'  => $dataPenawaran
        ], 200);
    }

    public function getDataPenawaranSendiriPerBarang(Request $request, $id)
    {
        // Get API Key
        $api_token = $request->header('Authorization');

        // Find User
        $user = User::where('api_token', $api_token)->first();

        // Find Barang
        $barang = Barang::where('kode_barang', $id)->first();

        // Cari Data
        $dataPenawaran = Lelang::where('penawar_id', $user->id)
                            ->where('barang_id', $barang->id)
                            ->get();

        // Cek Data Ada atau Tidak
        if(!$dataPenawaran) {
            return response()->json([
                'message' => 'Data Tidak Ada !',
            ], 404);
        }

        return response()->json([
            'messsage' => 'data berhasil di ambil !',
            'data'  => $dataPenawaran
        ], 200);
    }

    public function approvePenawaran(Request $request, $id)
    {

        // Get Data Penawaran
        $dataLelang = Lelang::where('id', $id)->first();

        if(!$dataLelang) {
            return response()->json([
                'message' => 'Data Lelang Tidak Ada !',
            ], 404);
        }

        $dataLelang->penawar_id = $dataLelang->penawar_id;
        $dataLelang->barang_id = $dataLelang->barang_id;
        $dataLelang->harga_tawaran = $dataLelang->harga_tawaran;
        $dataLelang->status_lelang = 'Approved';

        $dataUser = User::where('id', $dataLelang->penawar_id)->first();
        $dataBarang = Barang::where('id', $dataLelang->barang_id)->first();
        
        if(!$dataUser) {
            return response()->json([
                'message' => 'Data User Tidak Ada !',
            ], 404);
        }

        if(!$dataBarang) {
            return response()->json([
                'message' => 'Data Barang Tidak Ada !',
            ], 404);
        }

        $dataToSend = [
            'nama' => $dataUser->name,
            'harga' => $dataLelang->harga_tawaran,
            'data_barang' => $dataBarang
        ];


        dispatch(new SendEmailLelangApproveJob($dataUser->email, $dataToSend));


        $dataLelang->save();

        // Update Data Lelang Barang Terkait
        $dataBarangTerkait = Lelang::where('barang_id', '=', $dataLelang->barang_id)
                                ->where('id', '!=', $id)
                                ->get();

        foreach ($dataBarangTerkait as $key) {
            $key->penawar_id = $key->penawar_id;
            $key->barang_id = $key->barang_id;
            $key->harga_tawaran = $key->harga_tawaran;
            $key->status_lelang = 'Declined';
            $key->save();
        }

        // Update Status Barang

        $dataBarang->status_barang = 'Terjual';
        $dataBarang->save();

        return response()->json([
            'message' => 'Penawaran Berhasil di Approve !',
            'data'  => $dataLelang
        ], 200);

    }


    public function getSpecificLelang($id)
    {
        // Cari Data
        $dataLelang = Lelang::where('id', $id)->first();

        // Cek Data Ada atau Tidak
        if(!$dataLelang) {
            return response()->json([
                'message' => 'Data Tidak Ada !',
            ], 404);
        }

        return response()->json([
            'messsage' => 'data berhasil di ambil !',
            'data'  => $dataLelang
        ], 200);
    }

}
