<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\User;
use App\Models\Content;
use Illuminate\Http\Request;
use App\Helpers\IdGenerator;
use Illuminate\Support\Facades\DB;

class DepartmentController extends Controller
{
    public function __construct()
    {
        // Middleware untuk batasi akses ke controller ini hanya untuk role 'superadmin'
        $this->middleware('role:superadmin');
    }
public function index(Request $request)
{
    $query = Department::with('parent');

    // Search manual
    if ($request->filled('search')) {
        $query->where('name_departments', 'like', '%' . $request->search . '%');
    }

    $departments = $query->get();

    if ($request->ajax()) {
        return view('department.partials.table', compact('departments'))->render();
    }

    return view('department.index', compact('departments'));
}

    // Form untuk menambah departemen baru
    public function create()
    {
        // Ambil semua departemen untuk opsi parent (bisa jadi parent_id)
        $departments = Department::all();

        // Tampilkan form create, kirim data departemen untuk dropdown parent
        return view('department.create', compact('departments'));
    }

    // Proses simpan data departemen baru
    public function store(Request $request)
    {
        // dd($request->all());
        // Validasi input yang masuk, pastikan nama dan parent valid (parent boleh null)
        $request->validate([
            'name_departments' => 'required|string|max:255',
            'parent_id' => 'nullable|string|exists:departments,id_departments',
        ]);

        // Cek ulang kalau parent_id ada, harus valid di DB (untuk safety ekstra)
        if ($request->parent_id && !Department::find($request->parent_id)) {
            return redirect()->back()->with('error', 'Parent tidak ditemukan.');
        }

        // Mulai transaksi DB supaya kalau gagal rollback, gak jadi data setengah jadi
        DB::beginTransaction();
        try {
            $department = new Department();

            // Generate id_departments otomatis dengan prefix DP
            $department->id_departments = IdGenerator::generate(Department::class, 'DP');

            // Isi data nama, lokasi, parent
            $department->name_departments = $request->name_departments;
            $department->parent_id = $request->parent_id;
            // Simpan ke database
            $department->save();

            // Commit transaksi DB
            DB::commit();

            // Redirect ke list departemen dengan pesan sukses
            return redirect()->route('department.index')->with('success', 'Departemen berhasil disimpan.');
        } catch (\Exception $e) {
            // Kalau ada error, rollback transaksi
            DB::rollBack();

            // Log error untuk debugging
            \Log::error('Gagal menyimpan department: ' . $e->getMessage());

            // Redirect balik dengan pesan error
            return redirect()->back()->with('error', 'Gagal menyimpan departemen. Silakan coba lagi.');
        }
    }

    // Form edit departemen berdasarkan UUID
    public function edit($uuid)
    {
        // Cari departemen berdasar uuid, kalau gak ketemu 404
        $department = Department::where('uuid', $uuid)->firstOrFail();

        // Ambil semua departemen untuk opsi parent
        $departments = Department::all();

        // Tampilkan form edit, kirim data department dan list departemen untuk parent dropdown
        return view('department.edit', compact('department', 'departments'));
    }

    // Update data departemen berdasarkan id (id_departments)
    public function update(Request $request, $id)
    {
        // Cari departemen dulu, kalau gak ada 404
        $department = Department::findOrFail($id);

        // Validasi input masuk, sama kayak di store
        $request->validate([
            'name_departments' => 'required|string|max:255',
            'parent_id' => 'nullable|string|exists:departments,id_departments',
        ]);

        // Validasi tambahan: pastikan parent_id gak sama dengan departemen sendiri (hindari loop)
        if ($request->parent_id && $request->parent_id === $id) {
            return redirect()->back()->with('error', 'Departemen tidak boleh menjadi parent dirinya sendiri.');
        }

        // Update data departemen
        $department->name_departments = $request->name_departments;
        $department->parent_id = $request->parent_id;
        $department->save();

        // Redirect ke halaman list departemen dengan pesan sukses
        return redirect()->route('department.index')->with('success', 'Departemen berhasil diperbarui.');
    }

    // Hapus departemen berdasarkan id_departments
    public function destroy($id, Request $request)
    {
        // Ambil departemen sekaligus relasi users, contents, anak-anaknya dan parentnya
        $department = Department::with(['users', 'contents', 'children', 'parent'])
            ->where('id_departments', $id)
            ->first();

        // Cegah hapus departemen universitas (DP00)
        if ($department->id_departments === 'DP00') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus superadmin.');
        }

        // Mulai transaksi DB supaya aman dan konsisten
        DB::beginTransaction();
        try {
            // Ambil anak-anak departemen (children)
            $childDepartments = $department->children;

            // Gabungkan semua ID departemen anak dan departemen ini sendiri dalam array
            $allDepartmentIds = collect($childDepartments)->pluck('id_departments')->toArray();
            $allDepartmentIds[] = $department->id_departments;

            // Jika opsi deleteAction adalah 'deleteAndMove', pindahkan anak-anak ke Universitas (DP00)
            if ($request->has('deleteAction') && $request->deleteAction == 'deleteAndMove') {
                foreach ($childDepartments as $child) {
                    // Update parent_id anak jadi DP00 (Universitas)
                    $child->parent_id = 'DP00';
                    $child->save();
                }
            }

            // Ambil semua konten yang terkait dengan departemen utama dan anak-anaknya
            $allContents = Content::whereHas('departments', function ($query) use ($allDepartmentIds) {
                $query->whereIn('departments.id_departments', $allDepartmentIds);
            })->get();

            // Loop untuk cek dan bersihkan konten yang hanya terhubung ke departemen yang akan dihapus
            foreach ($allContents as $content) {
                // Ambil relasi departemen yang terkait konten ini
                $contentDepartments = $content->departments;

                // Cek apakah konten terhubung dengan departemen induk yang dihapus
                $isLinkedToParentDepartment = $contentDepartments->contains(function ($dept) use ($department) {
                    return $dept->parent_id === $department->id_departments;
                });

                // Kalau terhubung ke induk yang dihapus, lepaskan relasi dengan induk ini
                if ($isLinkedToParentDepartment) {
                    $content->departments()->detach($department->id_departments);
                }

                // Kalau konten sudah gak punya relasi departemen lagi (tertinggal orphan)
                if ($content->departments()->count() === 0) {
                    // Hapus file konten di storage kalau ada
                    if ($content->file_server && \Storage::exists('contents/' . $content->file_server)) {
                        \Storage::delete('contents/' . $content->file_server);
                    }

                    // Hapus konten dari database (forceDelete supaya benar-benar hilang)
                    $content->forceDelete();
                }
            }

            // Bersihkan konten orphan yang gak punya departemen sama sekali
            $orphanedContents = Content::whereDoesntHave('departments')->get();
            foreach ($orphanedContents as $content) {
                if ($content->departments->isEmpty()) {
                    if ($content->file_server && \Storage::exists('contents/' . $content->file_server)) {
                        \Storage::delete('contents/' . $content->file_server);
                    }
                    $content->forceDelete();
                } else {
                    // Log jika konten masih terhubung ke departemen (untuk debugging)
                    \Log::info('Konten masih terhubung dengan departemen', [
                        'content_id' => $content->id_contents,
                        'departments' => $content->departments->pluck('id_departments')->toArray()
                    ]);
                }
            }

            // Ambil semua user yang ada di departemen ini dan anak-anaknya
            $allUsers = User::whereIn('id_departments', $allDepartmentIds)->get();

            foreach ($allUsers as $user) {
                // Cek apakah user termasuk di departemen anak, jika iya jangan dihapus
                $isUserInChildDepartment = $childDepartments->contains(function ($childDepartment) use ($user) {
                    return $childDepartment->id_departments == $user->id_departments;
                });

                if ($isUserInChildDepartment) {
                    continue; // Skip hapus user yang ada di departemen anak
                }

                // Hapus konten yang dibuat oleh user ini
                $userContents = Content::where('created_by', $user->id)->get();
                foreach ($userContents as $uc) {
                    // Lepas relasi konten dengan departemen
                    $uc->departments()->detach();

                    // Hapus file konten kalau ada
                    if ($uc->file_server && \Storage::exists('contents/' . $uc->file_server)) {
                        \Storage::delete('contents/' . $uc->file_server);
                    }

                    // Hapus konten user
                    $uc->delete();
                }

                // Hapus user dari database
                $user->delete();
            }

            // Kalau opsi deleteAction adalah 'delete', hapus juga anak-anaknya
            if ($request->has('deleteAction') && $request->deleteAction == 'delete') {
                foreach ($childDepartments as $child) {
                    $child->delete();
                }
            }

            // Terakhir, hapus departemen utama
            $department->delete();

            // Commit transaksi DB
            DB::commit();

            // Redirect ke list departemen dengan pesan sukses
            return redirect()->route('department.index')->with('success', 'Departemen berhasil dihapus.');
        } catch (\Exception $e) {
            // Kalau ada error, rollback transaksi supaya data gak kacau
            DB::rollBack();

            // Redirect dengan pesan error dari exception
            return redirect()->route('department.index')->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
