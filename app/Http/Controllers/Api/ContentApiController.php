<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Content;
use App\Http\Resources\ContentResource;
use Illuminate\Http\Request;

class ContentApiController extends Controller
{
   

    public function index(Request $request)
    {
        $user = auth()->user();
        $departmentId = $user->id_departments;
        

        // Ambil departemen anak
        $childIds = \App\Models\Department::where('parent_id', $departmentId)
            ->pluck('id_departments')
            ->toArray();

        $today = now()->toDateString();

        $contents = Content::where(function ($q) use ($departmentId, $childIds) {
            // Konten milik sendiri
            $q->whereHas('departments', function ($q2) use ($departmentId) {
                $q2->where('departments.id_departments', $departmentId);
            });

            // Konten dari anak yang minta tayang
            if (!empty($childIds)) {
                $q->orWhereHas('departments', function ($q2) use ($childIds) {
                    $q2->whereIn('departments.id_departments', $childIds)
                        ->where('monitor_contents.is_visible_to_parent', 1)
                        ->where('monitor_contents.is_tayang_request', 1);
                });
            }
        })
            ->whereDate('start_date', '<=', $today)
            ->whereDate('end_date', '>=', $today)
            ->with('departments') // agar relasi dikirim
            ->get();

        return response()->json([
            'data' => ContentResource::collection($contents, )
        ]);
    }


    /**
     * KALO USER MAU NAMPILIN, DIA AMBIL FILE_URL YANG UDAH DI BUAT AMA INDEX TADI
     * TERUS BROWSER akan AKSES URL /api/sync-file/{uuid} 
     * (yang diarahkan ke method serveSyncFile), 
     * lalu file dikirim ke browser untuk ditampilkan.
     * 
     * Alur singkatnya:
     * Web 2 ambil data konten lewat API index.
     * Web 2 baca file_url dari setiap konten.
     * Web 2 tampilkan gambar/video di halaman dengan <img src="file_url"> atau <video src="file_url">.
     * Saat file_url diakses, method serveSyncFile yang mengirim file ke browser.
     * 
     * @param string $uuid UUID konten yang ingin diambil filenya
     */
    public function serveSyncFile($uuid)
    {
        // Cari konten di database berdasarkan uuid yang dikirim dari frontend.
        // Jika tidak ditemukan, otomatis akan error 404 (tidak ditemukan).
        $content = Content::where('uuid', $uuid)->firstOrFail();

        // Ambil tahun dari tanggal mulai konten (start_date), 
        // digunakan untuk menentukan folder penyimpanan file di server.
        $tahun = \Carbon\Carbon::parse($content->start_date)->format('Y');
        $path = storage_path("app/public/public_content/{$tahun}/" . $content->file_server);

        // Cek apakah file benar-benar ada di server.
        // Jika tidak ada, kirim error 404 ke user/frontend.
        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Kirim file ke user/frontend.
        // File akan langsung tampil di browser (inline), 
        // dan nama file yang tampil adalah nama file asli (file_original).
        // Di Web 2: dipakai untuk menampilkan gambar/video di layar signage digital.
        return response()->file($path, [
            'Content-Type' => mime_content_type($path),
            'Content-Disposition' => 'inline; filename="' . $content->file_original . '"',
        ]);
    }
}