<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PeriodePendaftaran;
use App\Models\Pendaftaran;
use App\Models\JenisBerkas;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB; // 🔥 TAMBAHKAN INI

class PendaftaranController extends Controller
{
    public function index()
    {
        $periodeAktif = PeriodePendaftaran::where('is_active', 1)
            ->where('tanggal_mulai', '<=', date('Y-m-d'))
            ->where('tanggal_selesai', '>=', date('Y-m-d'))
            ->first();
        
        $config = DB::table('pendaftaran_config')->first(); // 🔥 PAKAI DB INI
        $jenisBerkas = JenisBerkas::all();
        
        return view('pendaftaran', compact('periodeAktif', 'config', 'jenisBerkas'));
    }
    
    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:pendaftaran,email',
            'no_hp' => 'required|string|max:20',
            'nim' => 'required|string|max:20',
            'jurusan' => 'required|string|max:100',
            'fakultas' => 'required|string|max:100',
            'alamat' => 'required|string',
            'alasan' => 'required|string',
            'id_periode' => 'required|exists:periode_pendaftaran,id_periode'
        ]);
        
        Pendaftaran::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'no_hp' => $request->no_hp,
            'nim' => $request->nim,
            'jurusan' => $request->jurusan,
            'fakultas' => $request->fakultas,
            'alamat' => $request->alamat,
            'alasan' => $request->alasan,
            'id_periode' => $request->id_periode,
            'status' => 'menunggu',
            'tanggal_daftar' => Carbon::now()
        ]);
        
        return redirect()->back()->with('success', 'Pendaftaran berhasil! Silahkan tunggu konfirmasi.');
    }
    
    public function cekStatus($email)
    {
        $pendaftaran = Pendaftaran::where('email', $email)->first();
        return response()->json($pendaftaran);
    }
}