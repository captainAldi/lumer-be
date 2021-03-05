<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use App\Jobs\SendEmailVerifyJob;

class ProfileController extends Controller
{
   
    public function profile(Request $request, $id)
    {
        // Get API Key
        $api_token = $request->header('Authorization');

        // Find User
        $user = User::where('api_token', $api_token)->first();

        return response()->json([
            'message' => 'Data Berhasil di Ambil !',
            'data'     => $user
        ], 200);

        if($user->id != $id)
        {
            return response()->json([
                'message' => 'Anda Tidak Berhak !',
            ], 401);
        }

       return response()->json([
           'message' => 'Data Berhasil di Ambil !',
           'data'     => $user
       ], 200);
    }

     public function updateProfile(Request $request, $id)
    {

        // Get API Key
        $api_token = $request->header('Authorization');

        // Find User
        $dataProfile = User::where('api_token', $api_token)->first();

        if($dataProfile->id != $id)
        {
            return response()->json([
                'messages' => 'Anda Tidak Berhak !',
            ], 401);
        }

        // Cek domain email
        $namaDomain = explode('@', $request->input('email'));

        if($namaDomain[1] != 'alterra.id' )
        {
            return response()->json([
                'messages' => 'Gunakan E-Mail Alterra !',
            ], 406);
        }


        $passwordLama = $dataProfile->password;

        // Cek Jika Ada Password atau File Baru

        $cekAdaPassword = $request->has('password') ? 'required' : 'nullable';
        // $cekAdaProfilePicture = $request->hasFile('profile_picture') ? 'required|mimes:jpg,png,jpeg' : 'nullable';

        // Cek Admin atau Bukan
        $cekAdmin = $dataProfile->role != 'Admin' ? 'User' : 'Admin';

        // Pesan Jika Error
        $messages = [
            'name.required'          => 'Masukkan Nama !',
            'email.required'         => 'Masukkan Email !',
            'email.unique'           => 'Email Sudah Terdaftar !',
            'password.required'      => 'Masukkan Password !',
            //'profile_picture.required' => 'Masukkan Photo'
        ];
        
        //Validasi Data
        $validasiData = $this->validate($request, [
            'name'          => 'required',
            'email'         => 'required|unique:users,email,' . $dataProfile->id,
            'password'      => $cekAdaPassword,
            //'profile_picture'   => $cekAdaProfilePicture
        ], $messages);

        // Cek Email Beda dengan Sebelum nya
        if ($dataProfile->email != strtolower($request->input('email'))) {
            $newRememberToken  = Str::random(32);
 
            $dataProfile->update([
                'email_verified_at' => null,
                'rememberToken' => $newRememberToken
            ]);

            $dataToSend = [
                'newRememberToken' => $newRememberToken,
                'appUrl'    => env('VUE_APP_URL')
            ];

            dispatch(new SendEmailVerifyJob(strtolower($request->input('email')), $dataToSend));
        }

        //Simpan Data Profile
        $dataProfile->name = ucwords($request->input('name'));
        $dataProfile->email = strtolower($request->input('email'));
        $dataProfile->role = $cekAdmin;

        //Khusus Password
        if ($cekAdaPassword == 'required') {
            $dataProfile->password = Hash::make($request->input('password'));
        }


        $dataProfile->save();

        // Find Old Token
        $oldToken = $dataProfile->api_token;

        return response()->json([
            'message' => 'data berhasil di ubah !',
            "result"  => [
                "profile"   => $dataProfile,
                "api_token" => $oldToken,
            ]
        ], 200);
    }

}


