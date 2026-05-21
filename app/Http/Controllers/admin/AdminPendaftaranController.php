<?php
// app/Http/Controllers/Admin/AdminPendaftaranController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pendaftaran;
use App\Models\PeriodePendaftaran;
use App\Models\JenisBerkas;
use App\Models\BerkasPendaftaran;
use App\Models\FormField;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AdminPendaftaranController extends Controller
{
    // Halaman utama kelola pendaftaran
    public function index(Request $request)
    {
        $query = Pendaftaran::with(['periode']);
        
        if ($request->has('status') && $request->status != '') {
            $query->where('status', $request->status);
        }
        
        if ($request->has('periode') && $request->periode != '') {
            $query->where('id_periode', $request->periode);
        }
        
        if ($request->has('search') && $request->search != '') {
            $query->where(function($q) use ($request) {
                $q->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('nim', 'like', '%' . $request->search . '%');
            });
        }
        
        $pendaftaran = $query->orderBy('tanggal_daftar', 'desc')->paginate(15);
        $periodeList = PeriodePendaftaran::all();
        $statusList = ['menunggu', 'diterima', 'ditolak'];
        $formFields = FormField::where('is_active', 1)->orderBy('sort_order')->get();
        
        return view('admin.pendaftaran.index', compact('pendaftaran', 'periodeList', 'statusList', 'formFields'));
    }
    
    // Detail pendaftaran
    public function show($id)
    {
        $pendaftaran = Pendaftaran::with(['periode', 'berkas.jenisBerkas'])->findOrFail($id);
        $formFields = FormField::where('is_active', 1)->orderBy('sort_order')->get();
        $jenisBerkas = JenisBerkas::all();
        
        return view('admin.pendaftaran.show', compact('pendaftaran', 'formFields', 'jenisBerkas'));
    }
    
    public function updateStatus(Request $request, $id)
    {
        $pendaftaran = Pendaftaran::findOrFail($id);
        $pendaftaran->status = $request->status;
        $pendaftaran->save();
        
        return redirect()->back()->with('success', 'Status pendaftaran berhasil diupdate');
    }
    
    public function destroy($id)
    {
        $pendaftaran = Pendaftaran::findOrFail($id);
        
        foreach ($pendaftaran->berkas as $berkas) {
            $filePath = public_path($berkas->file_path);
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            $berkas->delete();
        }
        
        $pendaftaran->delete();
        
        return redirect()->route('admin.pendaftaran.index')
            ->with('success', 'Data pendaftaran berhasil dihapus');
    }
    
    public function accept($id)
    {
        $pendaftaran = Pendaftaran::findOrFail($id);
        $pendaftaran->status = 'diterima';
        $pendaftaran->save();
        
        return redirect()->back()->with('success', 'Pendaftar ' . $pendaftaran->nama . ' telah DITERIMA');
    }
    
    public function reject($id)
    {
        $pendaftaran = Pendaftaran::findOrFail($id);
        $pendaftaran->status = 'ditolak';
        $pendaftaran->save();
        
        return redirect()->back()->with('success', 'Pendaftar ' . $pendaftaran->nama . ' telah DITOLAK');
    }
    
    public function downloadBerkas($id)
    {
        $berkas = BerkasPendaftaran::findOrFail($id);
        $filePath = public_path($berkas->file_path);
        
        if (file_exists($filePath)) {
            return response()->download($filePath);
        }
        
        return redirect()->back()->with('error', 'File tidak ditemukan');
    }
    
    // 🔥 HALAMAN KONFIGURASI PENDAFTARAN
    public function config()
    {
        $config = DB::table('pendaftaran_config')->first();
        $periodeAktif = PeriodePendaftaran::where('is_active', 1)->first();
        
        return view('admin.pendaftaran.config', compact('config', 'periodeAktif'));
    }
    
    // 🔥 UPDATE KONFIGURASI - SINKRON DENGAN PERIODE
    public function updateConfig(Request $request)
    {
        $request->validate([
            'is_open' => 'nullable|boolean',
            'welcome_text' => 'nullable|string',
            'closing_text' => 'nullable|string'
        ]);
        
        $isOpen = $request->has('is_open') ? 1 : 0;
        
        // Update config
        DB::table('pendaftaran_config')->updateOrInsert(
            ['id_config' => 1],
            [
                'is_open' => $isOpen,
                'welcome_text' => $request->welcome_text,
                'closing_text' => $request->closing_text,
                'updated_at' => now()
            ]
        );
        
        // 🔥 SINKRON: Jika config is_open = 0, nonaktifkan semua periode
        if ($isOpen == 0) {
            PeriodePendaftaran::where('is_active', 1)->update(['is_active' => 0]);
        }
        
        return redirect()->route('admin.pendaftaran.config')
            ->with('success', 'Konfigurasi pendaftaran berhasil diupdate');
    }
    
    // 🔥 HALAMAN PERIODE PENDAFTARAN
    public function periode()
    {
        $periode = PeriodePendaftaran::orderBy('tanggal_mulai', 'desc')->get();
        $config = DB::table('pendaftaran_config')->first();
        
        return view('admin.pendaftaran.periode', compact('periode', 'config'));
    }
    
    // 🔥 TAMBAH PERIODE PENDAFTARAN
    public function storePeriode(Request $request)
    {
        $request->validate([
            'tahun_ajaran' => 'required|string|max:20',
            'nama_periode' => 'required|string|max:50',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'kuota' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string'
        ]);
        
        // Cek apakah ada periode aktif sebelumnya
        $config = DB::table('pendaftaran_config')->first();
        
        // Jika config is_open = 0, periode baru tidak bisa diaktifkan
        $isActive = ($config->is_open ?? 0) == 1 ? 0 : 0;
        
        PeriodePendaftaran::create([
            'tahun_ajaran' => $request->tahun_ajaran,
            'nama_periode' => $request->nama_periode,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_active' => $isActive,
            'kuota' => $request->kuota,
            'deskripsi' => $request->deskripsi
        ]);
        
        return redirect()->route('admin.pendaftaran.periode')
            ->with('success', 'Periode pendaftaran berhasil ditambahkan');
    }
    
    // 🔥 UPDATE PERIODE - SINKRON DENGAN CONFIG
    public function updatePeriode(Request $request, $id)
    {
        $periode = PeriodePendaftaran::findOrFail($id);
        $config = DB::table('pendaftaran_config')->first();
        
        $request->validate([
            'tahun_ajaran' => 'required|string|max:20',
            'nama_periode' => 'required|string|max:50',
            'tanggal_mulai' => 'required|date',
            'tanggal_selesai' => 'required|date|after:tanggal_mulai',
            'kuota' => 'required|integer|min:1',
            'deskripsi' => 'nullable|string'
        ]);
        
        $isActive = $request->has('is_active') ? 1 : 0;
        
        // 🔥 SINKRON: Jika config is_open = 0, periode tidak bisa diaktifkan
        if (($config->is_open ?? 0) == 0 && $isActive == 1) {
            return redirect()->back()->with('error', 'Pendaftaran sedang ditutup. Aktifkan konfigurasi pendaftaran terlebih dahulu.');
        }
        
        $periode->update([
            'tahun_ajaran' => $request->tahun_ajaran,
            'nama_periode' => $request->nama_periode,
            'tanggal_mulai' => $request->tanggal_mulai,
            'tanggal_selesai' => $request->tanggal_selesai,
            'is_active' => $isActive,
            'kuota' => $request->kuota,
            'deskripsi' => $request->deskripsi
        ]);
        
        // Jika periode ini diaktifkan, nonaktifkan periode lain
        if ($isActive == 1) {
            PeriodePendaftaran::where('id_periode', '!=', $id)
                ->where('is_active', 1)
                ->update(['is_active' => 0]);
        }
        
        // 🔥 SINKRON: Jika ada periode yang aktif, pastikan config is_open = 1
        $hasActivePeriode = PeriodePendaftaran::where('is_active', 1)->exists();
        if ($hasActivePeriode) {
            DB::table('pendaftaran_config')->updateOrInsert(
                ['id_config' => 1],
                ['is_open' => 1, 'updated_at' => now()]
            );
        } else {
            DB::table('pendaftaran_config')->updateOrInsert(
                ['id_config' => 1],
                ['is_open' => 0, 'updated_at' => now()]
            );
        }
        
        return redirect()->route('admin.pendaftaran.periode')
            ->with('success', 'Periode pendaftaran berhasil diupdate');
    }
    
    public function destroyPeriode($id)
    {
        $periode = PeriodePendaftaran::findOrFail($id);
        $periode->delete();
        
        // Cek apakah masih ada periode aktif
        $hasActivePeriode = PeriodePendaftaran::where('is_active', 1)->exists();
        if (!$hasActivePeriode) {
            DB::table('pendaftaran_config')->updateOrInsert(
                ['id_config' => 1],
                ['is_open' => 0, 'updated_at' => now()]
            );
        }
        
        return redirect()->route('admin.pendaftaran.periode')
            ->with('success', 'Periode pendaftaran berhasil dihapus');
    }
    
    // Halaman manajemen form fields
    public function formFields()
    {
        $formFields = FormField::orderBy('sort_order')->get();
        return view('admin.pendaftaran.form-fields', compact('formFields'));
    }
    
    public function storeFormField(Request $request)
    {
        $request->validate([
            'field_name' => 'required|string|max:50|unique:form_fields,field_name',
            'field_label' => 'required|string|max:100',
            'field_type' => 'required|in:text,email,tel,textarea,number,date',
            'is_required' => 'boolean',
            'placeholder' => 'nullable|string|max:255',
            'sort_order' => 'integer'
        ]);
        
        FormField::create([
            'field_name' => $request->field_name,
            'field_label' => $request->field_label,
            'field_type' => $request->field_type,
            'is_required' => $request->is_required ?? 1,
            'placeholder' => $request->placeholder,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => 1
        ]);
        
        $this->addColumnToPendaftaran($request->field_name);
        
        return redirect()->route('admin.pendaftaran.form-fields')
            ->with('success', 'Form field berhasil ditambahkan');
    }
    
    public function updateFormField(Request $request, $id)
    {
        $field = FormField::findOrFail($id);
        
        $request->validate([
            'field_name' => 'required|string|max:50|unique:form_fields,field_name,' . $id . ',id_field',
            'field_label' => 'required|string|max:100',
            'field_type' => 'required|in:text,email,tel,textarea,number,date',
            'placeholder' => 'nullable|string|max:255',
            'sort_order' => 'integer',
        ]);
        
        $isRequired = $request->has('is_required') ? 1 : 0;
        $isActive = $request->has('is_active') ? 1 : 0;
        
        if ($field->field_name != $request->field_name) {
            $this->renameColumnInPendaftaran($field->field_name, $request->field_name);
        }
        
        $field->update([
            'field_name' => $request->field_name,
            'field_label' => $request->field_label,
            'field_type' => $request->field_type,
            'is_required' => $isRequired,
            'placeholder' => $request->placeholder,
            'sort_order' => $request->sort_order ?? 0,
            'is_active' => $isActive
        ]);
        
        return redirect()->route('admin.pendaftaran.form-fields')
            ->with('success', 'Form field berhasil diupdate');
    }
    
    public function destroyFormField($id)
    {
        $field = FormField::findOrFail($id);
        $this->dropColumnFromPendaftaran($field->field_name);
        $field->delete();
        
        return redirect()->route('admin.pendaftaran.form-fields')
            ->with('success', 'Form field berhasil dihapus');
    }
    
    private function addColumnToPendaftaran($columnName)
    {
        try {
            DB::statement("ALTER TABLE pendaftaran ADD COLUMN `{$columnName}` TEXT NULL");
        } catch (\Exception $e) {}
    }
    
    private function renameColumnInPendaftaran($oldName, $newName)
    {
        try {
            DB::statement("ALTER TABLE pendaftaran CHANGE COLUMN `{$oldName}` `{$newName}` TEXT NULL");
        } catch (\Exception $e) {}
    }
    
    private function dropColumnFromPendaftaran($columnName)
    {
        try {
            DB::statement("ALTER TABLE pendaftaran DROP COLUMN `{$columnName}`");
        } catch (\Exception $e) {}
    }
    
    public function jenisBerkas()
    {
        $jenisBerkas = JenisBerkas::all();
        return view('admin.pendaftaran.jenis-berkas', compact('jenisBerkas'));
    }
    
    public function storeJenisBerkas(Request $request)
    {
        $request->validate([
            'nama_jenis' => 'required|string|max:100|unique:jenis_berkas,nama_jenis',
            'is_required' => 'boolean',
            'file_type' => 'required|string|max:20',
            'max_size' => 'required|integer|min:100|max:10240'
        ]);
        
        JenisBerkas::create([
            'nama_jenis' => $request->nama_jenis,
            'is_required' => $request->is_required ?? 0,
            'file_type' => $request->file_type,
            'max_size' => $request->max_size
        ]);
        
        return redirect()->route('admin.pendaftaran.jenis-berkas')
            ->with('success', 'Jenis berkas berhasil ditambahkan');
    }
    
    public function updateJenisBerkas(Request $request, $id)
    {
        $jenis = JenisBerkas::findOrFail($id);
        
        $request->validate([
            'nama_jenis' => 'required|string|max:100|unique:jenis_berkas,nama_jenis,' . $id . ',id_jenis',
            'is_required' => 'boolean',
            'file_type' => 'required|string|max:20',
            'max_size' => 'required|integer|min:100|max:10240'
        ]);
        
        $jenis->update([
            'nama_jenis' => $request->nama_jenis,
            'is_required' => $request->is_required ?? 0,
            'file_type' => $request->file_type,
            'max_size' => $request->max_size
        ]);
        
        return redirect()->route('admin.pendaftaran.jenis-berkas')
            ->with('success', 'Jenis berkas berhasil diupdate');
    }
    
    public function destroyJenisBerkas($id)
    {
        $jenis = JenisBerkas::findOrFail($id);
        $jenis->delete();
        
        return redirect()->route('admin.pendaftaran.jenis-berkas')
            ->with('success', 'Jenis berkas berhasil dihapus');
    }
}