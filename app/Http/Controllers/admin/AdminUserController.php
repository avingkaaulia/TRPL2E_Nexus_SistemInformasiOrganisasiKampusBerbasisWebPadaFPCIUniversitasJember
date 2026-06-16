<?php
// app/Http/Controllers/Admin/AdminUserController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class AdminUserController extends Controller
{
    // Halaman utama kelola user
    public function index(Request $request)
    {
        $query = User::with('role');
        
        if ($request->has('search') && $request->search != '') {
            $query->where('nama', 'like', '%' . $request->search . '%')
                  ->orWhere('email', 'like', '%' . $request->search . '%')
                  ->orWhere('username', 'like', '%' . $request->search . '%');
        }
        
        if ($request->has('role') && $request->role != '') {
            $query->where('id_role', $request->role);
        }
        
        $users = $query->orderBy('id_user', 'desc')->paginate(15);
        $roles = Role::all();
        
        return view('admin.users.index', compact('users', 'roles'));
    }
    
    // Form edit user
    public function edit($id)
    {
        $user = User::with('role')->findOrFail($id);
        $roles = Role::all();
        
        return view('admin.users.edit', compact('user', 'roles'));
    }
    
    // Update user (role dan data)
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);
        
        $request->validate([
            'nama' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users,username,' . $id . ',id_user',
            'email' => 'required|email|max:100|unique:users,email,' . $id . ',id_user',
            'id_role' => 'required|exists:roles,id_role',
            'password' => 'nullable|string|min:6'
        ]);
        
        $data = [
            'nama' => $request->nama,
            'username' => $request->username,
            'email' => $request->email,
            'id_role' => $request->id_role,
        ];
        
        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }
        
        $user->update($data);
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User ' . $user->nama . ' berhasil diupdate');
    }
    
    // Update role saja (AJAX)
    public function updateRole(Request $request, $id)
{
    $request->validate([
        'id_role' => 'required|exists:roles,id_role'
    ]);
    
    $user = User::findOrFail($id);
    $oldRole = $user->id_role;
    $oldRoleName = $this->getRoleName($oldRole);
    $newRoleName = $this->getRoleName($request->id_role);
    
    $user->id_role = $request->id_role;
    $user->save();
    
    // 🔥 UBAH JADI REDIRECT BIASA, BUKAN JSON
    return redirect()->route('admin.users.index')
        ->with('success', 'Role user ' . $user->nama . ' berhasil diubah dari ' . $oldRoleName . ' menjadi ' . $newRoleName);
}
    
    private function getRoleName($roleId)
    {
        $role = Role::find($roleId);
        return $role ? $role->nama_role : 'Unknown';
    }
    
    // Hapus user
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        $nama = $user->nama;
        
        // Cek apakah user memiliki data anggota
        $anggota = \App\Models\Anggota::where('id_user', $id)->first();
        if ($anggota) {
            return redirect()->back()->with('error', 'User ' . $nama . ' memiliki data anggota, hapus anggota terlebih dahulu!');
        }
        
        $user->delete();
        
        return redirect()->route('admin.users.index')
            ->with('success', 'User ' . $nama . ' berhasil dihapus');
    }
}