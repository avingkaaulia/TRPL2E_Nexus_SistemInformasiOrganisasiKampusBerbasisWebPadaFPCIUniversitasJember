<?php

namespace Tests\Feature;

use App\Http\Controllers\Admin\AdminTitleController;
use App\Models\Setting;
use Illuminate\Support\Facades\Route;
use Tests\DatabaseTestCase;

// AdminTitleController belum dipakai: tidak ada route-nya dan method Setting::getSiteTitle() / setSiteTitle() belum dibuat
class AdminTitleControllerTest extends DatabaseTestCase
{
    // ---- index() ----
    public function test_controller_title_belum_punya_route()
    {
        $this->assertFalse(Route::has('admin.title.index'));
    }

    public function test_index_title_gagal_karena_method_getSiteTitle_belum_ada_di_model_setting()
    {
        $this->assertFalse(method_exists(Setting::class, 'getSiteTitle'));
        $this->assertTrue(method_exists(AdminTitleController::class, 'index'));
    }

    // ---- update() ----
    public function test_update_title_gagal_karena_method_setSiteTitle_belum_ada_di_model_setting()
    {
        $this->assertFalse(method_exists(Setting::class, 'setSiteTitle'));
        $this->assertTrue(method_exists(AdminTitleController::class, 'update'));
    }
}
