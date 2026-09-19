<?php
// app/Helpers/ActivityLogger.php

namespace App\Helpers;

use App\Models\ActivityLog;
use Illuminate\Support\Facades\Auth;

class ActivityLogger
{
    public static function log($action, $description = null, $modelType = null, $modelId = null)
    {
        try {
            $user = Auth::user();

            ActivityLog::create([
                'user_id'    => $user->id_user ?? null,
                'user_name'  => $user->nama ?? 'Guest',
                'action'     => $action,
                'description'=> $description,
                'model_type' => $modelType,
                'model_id'   => $modelId,
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Diamkan aja kalo gagal log, biar gak ganggu proses utama
        }
    }
}