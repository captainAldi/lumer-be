<?php

namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;

use App\Mail\VerifyEmail;
use App\Mail\ResetPasswordEmail;

use App\Jobs\SendEmailVerifyJob;
use App\Jobs\SendEmailResetPasswordJob;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        // Validasi
        $this->validate($request, [
            'name'     => 'required',
            'email'    => 'required|unique:users',
            'password' => 'required',
        ]);

        //Simpan Data Guru
        $dataUser       = new User();
        $dataUser->name = ucwords($request->input('name'));
        $dataUser->email = strtolower($request->input('email'));
        $dataUser->password = Hash::make($request->input('password'));
        $dataUser->role = 'User';

        // Cek domain email
        $namaDomain = explode('@', $request->input('email'));

        if($namaDomain[1] != 'alterra.id' )
        {
            return response()->json([
                'messages' => 'Gunakan E-Mail Alterra !',
            ], 406);
        }
        
        if ($dataUser->save()) {
            $this->sendVerifyEmail($request->input('email'));

            $out = [
                "message" => "Register Berhasil",
                "code"    => 201,
            ];
        } else {
            $out = [
                "message" => "Register Gagal",
                "code"    => 406,
            ];
        }
        
        return response()->json($out, $out['code']);


    }
 
    public function login(Request $request)
    {
        $this->validate($request, [
            'email' => 'required',
            'password' => 'required'
        ]);
 
        $email = $request->input("email");
        $password = $request->input("password");
 
        $user = User::where("email", $email)->first();
 
        if (!$user) {
            $out = [
                "message" => "User Tidak di Temukan !",
                "code"    => 404,
                "result"  => [
                    "token" => null,
                ]
            ];
            return response()->json($out, $out['code']);
        }

        if (!($user->email_verified_at)) {
            
            $out = [
                "message" => "Verifikasi Email Dahulu !",
                "code"    => 403,
                "result"  => [
                    "token" => null,
                ]
            ];

            return response()->json($out, $out['code']);
        }
 
        if (Hash::check($password, $user->password)) {
            $newtoken  = Str::random(32);
 
            $user->update([
                'api_token' => $newtoken
            ]);
 
            $out = [
                "message" => "Login Sukses",
                "code"    => 200,
                "result"  => [
                    "profile"   => $user,
                    "api_token" => $newtoken,
                ]
            ];
        } else {
            $out = [
                "message" => "Password Salah !",
                "code"    => 401,
                "result"  => [
                    "token" => null,
                ]
            ];
        }


 
        return response()->json($out, $out['code']);
    }

    // Verify Email
    public function sendVerifyEmail($email)
    {

        // $this->validate($request, [
        //     'email' => 'required',
        // ]);

        // $emailUser = $request->input('email');
        $user = User::where("email", $email)->first();

        // if (!$user) {
        //     $out = [
        //         "message" => "User Tidak di Temukan !",
        //         "code"    => 401,
        //         "result"  => [
        //             "token" => null,
        //         ]
        //     ];
        //     return response()->json($out, $out['code']);
        // }

        $newRememberToken  = Str::random(32);
 
        $user->update([
            'rememberToken' => $newRememberToken
        ]);

        $dataToSend = [
            'newRememberToken' => $newRememberToken,
            'appUrl'    => env('VUE_APP_URL')
        ];

        // Mail::to($email)->send(new VerifyEmail($dataToSend));
        dispatch(new SendEmailVerifyJob($email, $dataToSend));

        // return response()->json([
        //     'message' => 'email telah dikirm !'
        // ], 200);
    }

     public function verifyEmail($newRememberToken)
    {

        $user = User::where("rememberToken", $newRememberToken)->first();

        if (!$user) {
            $out = [
                "message" => "Link Tidak Valid !",
                "code"    => 401,
                "result"  => [
                    "token" => null,
                ]
            ];
            return response()->json($out, $out['code']);
        }

 
        $user->update([
            'rememberToken' => null,
            'email_verified_at' => date("Y-m-d H:i:s")
        ]);

        return response()->json([
            'message' => 'Verifikasi Email Berhasil !'
        ], 200);
    }

    // Reset Password
    public function sendResetPasswordEmail(Request $request)
    {

        $this->validate($request, [
            'email' => 'required',
        ]);

        $emailUser = $request->input('email');
        $user = User::where("email", $emailUser)->first();

        if (!$user) {
            $out = [
                "message" => "User Tidak di Temukan !",
                "code"    => 401,
                "result"  => [
                    "token" => null,
                ]
            ];
            return response()->json($out, $out['code']);
        }

        $newRememberToken  = Str::random(32);
 
        $user->update([
            'rememberToken' => $newRememberToken
        ]);

        $dataToSend = [
            'newRememberToken' => $newRememberToken,
            'appUrl'    => env('VUE_APP_URL')
        ];

        // Mail::to($emailUser)->send(new ResetPasswordEmail($dataToSend));
        dispatch(new SendEmailResetPasswordJob($emailUser, $dataToSend));

        return response()->json([
            'message' => 'email telah dikirm !'
        ], 200);
    }

     public function resetPassword(Request $request, $newRememberToken)
    {
        $this->validate($request, [
            'password' => 'required',
        ]);

        $user = User::where("rememberToken", $newRememberToken)->first();

        if (!$user) {
            $out = [
                "message" => "Link Tidak Valid !",
                "code"    => 401,
                "result"  => [
                    "token" => null,
                ]
            ];
            return response()->json($out, $out['code']);
        }

        $passwordBaru = Hash::make($request->input('password'));

 
        $user->update([
            'rememberToken' => null,
            'password' => $passwordBaru
        ]);

        return response()->json([
            'message' => 'Reset Password Berhasil !'
        ], 200);
    }

 
}
