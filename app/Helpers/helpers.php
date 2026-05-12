<?php
// app/Helpers/helpers.php

if (!function_exists('getImageUrl')) {
    function getImageUrl($path)
    {
        if (!$path) {
            return asset('assets/img/default-image.jpg');
        }
        
        // Cek di storage (untuk gambar yang diupload via admin)
        $storagePath = storage_path('app/public/' . $path);
        if (file_exists($storagePath)) {
            return asset('storage/' . $path);
        }
        
        // Cek di public (untuk gambar lama)
        $publicPath = public_path($path);
        if (file_exists($publicPath)) {
            return asset($path);
        }
        
        // Cek di public/assets/img
        $assetsPath = public_path('assets/' . $path);
        if (file_exists($assetsPath)) {
            return asset('assets/' . $path);
        }
        
        // Cek di public/img
        $imgPath = public_path('img/' . $path);
        if (file_exists($imgPath)) {
            return asset('img/' . $path);
        }
        
        // Cek di storage/img
        $storageImgPath = storage_path('app/public/img/' . basename($path));
        if (file_exists($storageImgPath)) {
            return asset('storage/img/' . basename($path));
        }
        
        // Fallback ke default image
        return asset('assets/img/default-image.jpg');
    }
}