<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});


// API
$router->group(['prefix' => 'api/v1/' ], function() use ($router) {

    // -- ALL --

        // Authentication
        $router->post("/register", "AuthController@register");
        $router->post("/login", "AuthController@login");

        $router->get("/verify-email/{newRememberToken}", "AuthController@verifyEmail");

        $router->post("/send-reset-password-email", "AuthController@sendResetPasswordEmail");
        $router->post("/reset-password/{newRememberToken}", "AuthController@resetPassword");

        // -- Show Photo Barang --
        $router->get("/files/photo-barang/{namaFile}/{tipeFile}", "FilesController@showPhotoBarang");

    // -- Logged In and Verify Email and Admin --

        $router->group(['middleware' => ['login', 'verify', 'admin'] ], function() use ($router) {
            $router->get("/admin/barangs", "BarangController@getAllBarangs");
            $router->post("/admin/barang/create", "BarangController@createBarang");
            $router->get("/admin/barang/specific/{id}", "BarangController@getSpecificBarang");
            $router->patch("/admin/barang/update/{id}", "BarangController@updateBarang");
            $router->delete("/admin/barang/delete/{id}", "BarangController@deleteBarang");

            $router->post("/admin/barang/import", "BarangController@importBarang");      

            $router->get("/admin/lelang/penawaran/all", "LelangController@getAllPenawaran");
            $router->get("/admin/lelang/penawaran/specific/{id}", "LelangController@getSpecificPenawaran");
            $router->patch("/admin/lelang/penawaran/approve/{id}", "LelangController@approvePenawaran");     

                  

        });


    // -- Logged In and Verify Email --

        $router->group(['middleware' => ['login', 'verify'] ], function() use ($router) {
            $router->get("/profile/{id}", "ProfileController@profile");
            $router->patch("/profile/update/{id}", "ProfileController@updateProfile");

            $router->get("/lelang/barangs/{id}", "LelangController@getBarangsPerKota");
            $router->get("/lelang/barangs/specific/{id}", "LelangController@getSpecificBarang");
            $router->get("/lelang/penawaran/list", "LelangController@getDataPenawaranSendiri");
            $router->get("/lelang/penawaran/list/{id}", "LelangController@getDataPenawaranSendiriPerBarang");
            $router->post("/lelang/penawaran/create", "LelangController@createPenawaran");
        });

    // $router->post("/admin/barang/import", "BarangController@importBarang");        

});