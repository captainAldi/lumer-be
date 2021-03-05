<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class FilesController extends Controller
{
    // Show PP
    public function showPhotoBarang($namaFile, $tipeFile)
    {

        $cariPhoto = storage_path('app/data-aplikasi/photo-barang/' . $namaFile . '.' .$tipeFile);
            
        $isiResponse = response()->download($cariPhoto);

        $ppGuru = !empty($cariPhoto) ? $isiResponse : null;

        return $ppGuru;
    }
}
