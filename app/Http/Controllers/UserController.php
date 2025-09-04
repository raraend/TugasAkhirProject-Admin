<?php
// Mendefinisikan namespace agar controller ini bisa digunakan dengan autoload
namespace App\Http\Controllers;

// Import model dan fasad yang dibutuhkan
use App\Models\User;
use App\Models\Role;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class UserController extends Controller
{
    // Konstruktor yang menetapkan middleware 'role:superadmin'
    // Artinya hanya Superadmin yang bisa mengakses controller ini
    public function __construct()
    {
        $this->middleware('role:superadmin');
    }

    // Method untuk menampilkan daftar semua user
    public function index(Request $request)
    {
        $query = User::with(['role', 'department']);

        if ($request->has('search') && $request->search !== '') {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name_user', 'like', "%$search%")
                    ->orWhere('email', 'like', "%$search%");
            });
        }

        $users = $query->get();

        // Jika permintaan dari AJAX (live search)
        if ($request->ajax()) {
            return view('user.partials.table', compact('users'))->render();
        }

        // Jika bukan ajax (first load)
        return view('user.index', compact('users'));
    }



    // Menampilkan detail user berdasarkan UUID (khusus signed URL atau UUID access)
    public function show($uuid)
    {
        // Cek role user yang sedang login, hanya Superadmin (RL01) yang boleh akses
        if (auth()->user()->role_id !== 'RL01') {
            abort(403, 'Akses ditolak. Hanya Superadmin yang diizinkan.');
        }

        // Ambil user beserta relasinya berdasarkan UUID
        $user = User::with('role', 'department')->where('uuid', $uuid)->firstOrFail();
        // Tampilkan view detail user
        return view('user.show', compact('user'));
    }

    // Menampilkan form edit user berdasarkan UUID
    public function edit($uuid)
    {
        // Cek akses, hanya Superadmin
        if (auth()->user()->role_id !== 'RL01') {
            abort(403, 'Akses ditolak. Hanya Superadmin yang diizinkan.');
        }

        // Ambil user berdasarkan UUID
        $user = User::where('uuid', $uuid)->firstOrFail();
        // Ambil semua role dan department untuk ditampilkan di form
        $roles = Role::all();
        $departments = Department::all();

        // Tampilkan form edit dengan data yang dibutuhkan
        return view('user.edit', compact('user', 'roles', 'departments'));
    }

    // Menampilkan form create user baru
    public function create()
    {
        // Ambil semua role dan department
        $roles = Role::all(); // Untuk pilihan role
        $departments = Department::all(); // Untuk pilihan department

        // Tampilkan form pembuatan user
        return view('user.create', compact('roles', 'departments'));
    }



    public function store(Request $request)
    {
        $validator = Validator::make(
            $request->all(),
            [
                'name_user' => 'required|string|max:50',
                'email' => 'required|email|max:50|unique:users,email|regex:/@gmail\.com$/',
                'password' => 'required|string|max:8',
                'role_id' => 'required|exists:roles,id_roles',
                'id_departments' => 'required|exists:departments,id_departments',
            ],

            [
                'name_user.required' => 'Nama user wajib diisi.',
                'name_user.max' => 'Nama user maksimal 50 karakter.',
                'email.required' => 'Email wajib diisi.',
                'email.email' => 'Format email tidak valid.',
                'email.max' => 'Email maksimal 50 karakter.',
                'email.unique' => 'Email sudah digunakan.',
                'email.regex' => 'Email harus menggunakan domain @gmail.com.',
                'password.required' => 'Password wajib diisi.',
                'password.max' => 'Password minimal 8 karakter.',
                'role_id.required' => 'Role wajib dipilih.',
                'role_id.exists' => 'Role tidak valid.',
                'id_departments.required' => 'Departemen wajib dipilih.',
                'id_departments.exists' => 'Departemen tidak valid.',
            ]
        );


        $validator->validate();
        DB::beginTransaction();
        try {
            $user = new User();
            $user->name_user = $request->name_user;
            $user->email = $request->email;
            $user->password = Hash::make($request->password);
            $user->role_id = $request->role_id;
            $user->id_departments = $request->id_departments;
            $user->save();

            DB::commit();
            return redirect()->route('user.index')->with('success', 'User berhasil dibuat');
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Gagal menyimpan user: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan user. Silakan coba lagi.');
        }
    }



    // Memperbarui data user yang sudah ada
     public function update(Request $request, $id)
    {
        // Validasi input
        $request->validate([
            'name_user' => 'required|string|max:50|min:13',
            'email' => 'required|email|unique:users,email,' . $id . '|regex:/@gmail\.com$/',
            'password' => 'nullable|string|min:8', // opsional saat update
            'role_id' => 'required|exists:roles,id_roles',
            'id_departments' => 'required|exists:departments,id_departments',
        ], [
            'name_user.required' => 'Nama user wajib diisi.',
            'name_user.max' => 'Nama user maksimal 50 karakter.',
            'name_user.min' => 'Nama user maksimal 13 karakter.',
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'email.regex' => 'Email harus menggunakan domain @gmail.com.',
            'password.min' => 'Password minimal 8 karakter.',
            'role_id.required' => 'Role wajib dipilih.',
            'role_id.exists' => 'Role tidak valid.',
            'id_departments.required' => 'Departemen wajib dipilih.',
            'id_departments.exists' => 'Departemen tidak valid.',
        ]);

        // Cari user
        $user = User::findOrFail($id);

        // Update data
        $user->name_user = $request->name_user;
        $user->email = $request->email;
        $user->role_id = $request->role_id;
        $user->id_departments = $request->id_departments;

        // Update password jika diisi
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return redirect()->route('user.index')->with('success', 'User berhasil diperbarui');
    }
    // Menghapus user dari database
    public function destroy($id)
    {
        // Cari user berdasarkan ID
        $user = User::findOrFail($id);
        $user->delete(); // Hapus user (soft delete jika pakai soft delete)

        // Redirect kembali dengan pesan sukses
        return redirect()->route('user.index')->with('success', 'User berhasil dihapus');
    }
}
