<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeriodePendaftaran;
use App\Models\Pendaftaran;
use App\Models\JenisBerkas;
use App\Models\BerkasPendaftaran;
use App\Models\FormField;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PendaftaranController extends Controller
{
    public function index()
    {
        $periodeAktif = PeriodePendaftaran::where('is_active', 1)
            ->where('tanggal_mulai', '<=', date('Y-m-d'))
            ->where('tanggal_selesai', '>=', date('Y-m-d'))
            ->first();
        
        $config = DB::table('pendaftaran_config')->first();
        $formFields = FormField::getActiveFields();
        $jenisBerkas = JenisBerkas::all();
        
        return view('pendaftaran', compact('periodeAktif', 'config', 'formFields', 'jenisBerkas'));
    }
    
    public function store(Request $request)
    {
        // 🔥 LOG UNTUK DEBUG
        Log::info('Pendaftaran store method called');
        
        // 🔥 VALIDASI DINAMIS BERDASARKAN DATABASE
        $rules = [];
        
        // Validasi form fields dari database
        $formFields = FormField::getActiveFields();
        foreach ($formFields as $field) {
            $fieldRules = [];
            if ($field->is_required) {
                $fieldRules[] = 'required';
            }
            
            switch ($field->field_type) {
                case 'email':
                    $fieldRules[] = 'email';
                    if ($field->field_name == 'email') {
                        $fieldRules[] = 'unique:pendaftaran,email';
                    }
                    break;
                case 'number':
                    $fieldRules[] = 'numeric';
                    break;
                case 'tel':
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:20';
                    break;
                default:
                    $fieldRules[] = 'string';
                    $fieldRules[] = 'max:255';
            }
            
            if ($field->field_name == 'nim') {
                $fieldRules[] = 'unique:pendaftaran,nim';
                // 🔥 TAMBAHKAN VALIDASI NIM HARUS DIAWALI "240" (Khusus Mahasiswa UNEJ)
                $fieldRules[] = 'regex:/^240/';
            }
            
            $rules[$field->field_name] = $fieldRules;
        }
        
        // 🔥 VALIDASI BERKAS DENGAN FORMAT YANG BENAR
        $jenisBerkas = JenisBerkas::all();
        foreach ($jenisBerkas as $berkas) {
            $berkasRules = [];
            
            if ($berkas->is_required) {
                $berkasRules[] = 'required';
            }
            
            $berkasRules[] = 'file';
            $berkasRules[] = 'mimes:' . $berkas->file_type;
            $berkasRules[] = 'max:' . $berkas->max_size;
            
            $rules['berkas_' . $berkas->id_jenis] = $berkasRules;
        }
        
        $rules['id_periode'] = 'required|exists:periode_pendaftaran,id_periode';
        
        // Custom error messages
        $messages = [];
        foreach ($jenisBerkas as $berkas) {
            $maxSizeMB = round($berkas->max_size / 1024, 2);
            $messages['berkas_' . $berkas->id_jenis . '.max'] = 'Ukuran file ' . $berkas->nama_jenis . ' maksimal ' . $maxSizeMB . ' MB.';
            $messages['berkas_' . $berkas->id_jenis . '.mimes'] = 'Format file ' . $berkas->nama_jenis . ' harus .' . $berkas->file_type;
            $messages['berkas_' . $berkas->id_jenis . '.required'] = 'File ' . $berkas->nama_jenis . ' wajib diupload.';
        }
        
        // 🔥 TAMBAHKAN PESAN ERROR UNTUK VALIDASI NIM
        $messages['nim.regex'] = 'NIM harus diawali dengan 240 (khusus mahasiswa UNEJ).';
        $messages['nim.unique'] = 'NIM sudah terdaftar, silahkan gunakan NIM lain.';
        
        try {
            $request->validate($rules, $messages);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput();
        }
        
        // 🔥 CEK PERIODE AKTIF
        $periode = PeriodePendaftaran::find($request->id_periode);
        if (!$periode || !$periode->isAktif()) {
            return redirect()->back()->with('error', 'Periode pendaftaran sudah berakhir!')->withInput();
        }
        
        // 🔥 CEK KUOTA
        if ($periode->getSisaKuotaAttribute() <= 0) {
            return redirect()->back()->with('error', 'Maaf, kuota pendaftaran sudah penuh!')->withInput();
        }
        
        // 🔥 SIMPAN DATA PENDAFTARAN (DINAMIS)
        $dataPendaftaran = [
            'id_periode' => $request->id_periode,
            'status' => 'menunggu',
            'tanggal_daftar' => Carbon::now()
        ];
        
        foreach ($formFields as $field) {
            $dataPendaftaran[$field->field_name] = $request->{$field->field_name};
        }
        
        $pendaftaran = Pendaftaran::create($dataPendaftaran);
        
        // 🔥 SIMPAN BERKAS-BERKAS
        foreach ($jenisBerkas as $berkas) {
            $fieldName = 'berkas_' . $berkas->id_jenis;
            if ($request->hasFile($fieldName)) {
                $file = $request->file($fieldName);
                
                $fileSizeKB = round($file->getSize() / 1024);
                if ($fileSizeKB > $berkas->max_size) {
                    $maxSizeMB = round($berkas->max_size / 1024, 2);
                    return redirect()->back()->with('error', 'Ukuran file ' . $berkas->nama_jenis . ' (' . round($fileSizeKB/1024,2) . ' MB) melebihi batas maksimal ' . $maxSizeMB . ' MB.')->withInput();
                }
                
                $extension = $file->getClientOriginalExtension();
                $fileName = time() . '_' . Str::slug($request->nama) . '_' . $berkas->nama_jenis . '.' . $extension;
                
                $uploadDir = public_path('berkas/' . $pendaftaran->id_pendaftaran);
                if (!file_exists($uploadDir)) {
                    mkdir($uploadDir, 0755, true);
                }
                
                $file->move($uploadDir, $fileName);
                $filePath = 'berkas/' . $pendaftaran->id_pendaftaran . '/' . $fileName;
                
                BerkasPendaftaran::create([
                    'id_pendaftaran' => $pendaftaran->id_pendaftaran,
                    'id_jenis' => $berkas->id_jenis,
                    'file_path' => $filePath
                ]);
            }
        }
        
        return redirect()->back()->with('success', 'Pendaftaran berhasil! Silahkan tunggu konfirmasi melalui email.');
    }
    
    public function cekStatus($email)
    {
        $pendaftaran = Pendaftaran::where('email', $email)->first();
        if ($pendaftaran) {
            $pendaftaran->berkas = BerkasPendaftaran::with('jenisBerkas')
                ->where('id_pendaftaran', $pendaftaran->id_pendaftaran)
                ->get();
        }
        return response()->json($pendaftaran);
    }
}