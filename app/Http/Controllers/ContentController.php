<?php

namespace App\Http\Controllers;

use App\Models\Content;
use App\Models\Department;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use App\Models\MonitorContent;


class ContentController extends Controller
{

    public function __construct()
    {
        // Middleware buat batasi akses ke controller ini, biar cuma role 'admin' yang bisa masuk
        $this->middleware('role:admin');
    }

    // Method buat ngasih file konten ke user, dicek dulu hak aksesnya
    public function serveFile($uuid)
    {
        $user = auth()->user(); // ambil user yang lagi login

        // Cari konten berdasarkan uuid + ikut relasi departemen
        $content = Content::with('departments')->where('uuid', $uuid)->firstOrFail();
        $userDept = $user->id_departments; // departemen user

        // Ambil semua departemen yang punya konten ini
        $contentDepartments = $content->departments->pluck('id_departments');
        $isOwned = $contentDepartments->contains($userDept); // buat cek apakah konten ini punya departemen user

        // Cek apakah konten ini ditandai visible buat parent
        $visibleToParent = \App\Models\MonitorContent::whereIn('id_departments', $contentDepartments)
            ->where('is_visible_to_parent', true)
            ->exists();

        // Kalau bukan punya user dan gak visible buat parent → tolak
        if (!$isOwned && !$visibleToParent) {
            abort(403, 'Anda tidak punya akses ke file ini.');
        }

        // Buat path ke file konten (disimpan per tahun)
        $tahun = \Carbon\Carbon::parse($content->start_date)->format('Y');
        $path = storage_path("app/public/public_content/{$tahun}/" . $content->file_server);

        // Kalau file ga ada → 404
        if (!file_exists($path)) {
            abort(404, 'File tidak ditemukan.');
        }

        // Balikin file ke browser biar bisa ditampilkan inline
        return response()->file($path, [
            'Content-Type' => mime_content_type($path),
            'Content-Disposition' => 'inline; filename="' . $content->file_original . '"',
        ]);
    }

    // Buat nampilin detail konten berdasarkan uuid
    public function show(Request $request, $uuid)
    {
        $content = Content::with('departments')
            ->where('uuid', $uuid)->firstOrFail();

        $user = auth()->user(); // ambil user login

        // Cek apakah konten ini milik departemen user
        $isOwnedByUserDept = $content->departments
            ->pluck('id_departments')
            ->contains($user->id_departments);

        // Kalau bukan → lempar balik ke index
        if (!$isOwnedByUserDept) {
            return redirect()->route('content.index')
                ->with('error', 'Anda hanya bisa melihat konten milik departemen Anda.');
        }

        return view('content.show', compact('content'));
    }

    // Buat nampilin daftar konten (filter status + departemen)
    public function index(Request $request)
    {
        $today = Carbon::now()->startOfMinute(); // waktu sekarang (bulat ke menit)
        $user = auth()->user();
        $adminDeptId = $user->id_departments; // departemen admin

        // Ambil filter dari query
        $departmentFilter = $request->query('department_filter', 'self'); // default self
        $statusFilter = $request->query('status_filter', 'sedang'); // default sedang tayang

        // Ambil semua anak departemen dari user (kalau dia parent)
        $departments = Department::where('parent_id', $adminDeptId)->get();

        $isViewingChild = false; // flag buat cek apakah lagi lihat konten anak

        // Query awal konten
        $query = Content::with('departments');

        if ($departmentFilter === 'self') {
            // Kalau lihat konten sendiri → filter departemen sendiri
            $query->whereHas('departments', function ($q) use ($adminDeptId) {
                $q->where('departments.id_departments', $adminDeptId);
            });
            $isViewingChild = false;
        } else {
            // Cek apakah departemen filter itu anak dari adminDeptId
            $isChild = Department::where('uuid', $departmentFilter)
                ->where('parent_id', $adminDeptId)
                ->first();

            if ($isChild) {
                // Kalau iya → berarti parent lihat konten anak
                $isViewingChild = true;

                $query->whereHas('departments', function ($q) use ($departmentFilter) {
                    $q->where('departments.uuid', $departmentFilter)
                        ->where('monitor_contents.is_visible_to_parent', true);
                });
            } else {
                // Kalau bukan anak → fallback ke konten sendiri
                $query->whereHas('departments', function ($q) use ($adminDeptId) {
                    $q->where('departments.id_departments', $adminDeptId);
                });

                $isViewingChild = false;
            }
        }

        $contents = $query->get();

        // ====== CEK STATUS KONTEN ======
        $contents->each(function ($content) use ($today) {
            $nowDate = $today->toDateString(); // tanggal hari ini
            $nowDay = $today->dayOfWeekIso;    // 1 (Senin) - 7 (Minggu)

            $startDate = $content->start_date;
            $endDate = $content->end_date ?? $startDate;
            $startTime = $content->start_time;
            $endTime = $content->end_time ?? '23:59';

            $repeatDays = explode(',', $content->repeat_days ?? '');
            $isRepeatToday = in_array($nowDay, $repeatDays);

            // Buat waktu mulai & selesai
            $startDateTime = Carbon::parse($startDate . ' ' . $startTime);
            $endDateTime = Carbon::parse($endDate . ' ' . $endTime);

            // Tentuin status konten
            if ($today->lt($startDateTime)) {
                $content->status = 'Akan Tayang';
            } elseif ($today->gt($endDateTime)) {
                $content->status = 'Sudah Selesai';
            } elseif ($isRepeatToday || $startDate == $nowDate || $endDate == $nowDate) {
                if ($today->between($startDateTime, $endDateTime)) {
                    $content->status = 'Sedang Tayang';
                } else {
                    $content->status = 'Akan Tayang';
                }
            } else {
                $content->status = 'Akan Tayang';
            }

            // Flag tambahan buat parent-child
            $content->isVisible = $content->departments
                ->flatMap(fn($d) => $d->pivot ? [$d->pivot->is_visible_to_parent] : [])
                ->contains(true);

            $content->isTayangRequest = $content->departments
                ->flatMap(fn($d) => $d->pivot ? [$d->pivot->is_tayang_request] : [])
                ->contains(true);
        });

        // ====== FILTER KONTEN YANG DITAMPILIN ======
        $contentsToShow = $contents->filter(function ($c) use ($statusFilter, $isViewingChild, $adminDeptId) {
            if ($isViewingChild) {
                // Kalau parent lihat anak → cuma nampilin yang visible + lagi tayang
                return $c->status === 'Sedang Tayang' && $c->isVisible;
            } else {
                $isChildContent = $c->departments->contains(fn($d) => $d->parent_id == $adminDeptId);

                if ($isChildContent) {
                    // Kalau konten anak → harus visible + sudah minta tayang
                    return $c->status === 'Sedang Tayang' && $c->isVisible && $c->isTayangRequest;
                }

                // Kalau konten sendiri → filter sesuai status
                return match ($statusFilter) {
                    'akan' => $c->status === 'Akan Tayang',
                    'sudah' => $c->status === 'Sudah Selesai',
                    default => $c->status === 'Sedang Tayang',
                };
            }
        });

        // Kalau request via AJAX → return partial view
        if ($request->ajax()) {
            return view('content.partials.content-list', [
                'contentsToShow' => $contentsToShow,
                'departments' => $departments,
            ])->render();
        }

        // Kalau bukan → return full view
        return view('content.index', compact(
            'departments',
            'statusFilter',
            'contentsToShow',
            'isViewingChild',
            'departmentFilter',
        ));
    }

    // Buat edit konten
    public function edit(Request $request, $uuid)
    {
        $user = auth()->user(); // ambil user
        $content = Content::with('departments')
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Cek apakah konten ini punya departemen user
        $isOwnedByUserDept = $content->departments
            ->pluck('id_departments')
            ->contains($user->id_departments);

        if (!$isOwnedByUserDept) {
            return redirect()->route('content.index')
                ->with('error', 'Anda hanya bisa mengedit konten milik departemen Anda.');
        }

        // Ambil departemen user buat cek apakah dia bisa request ke parent
        $department = Department::findOrFail($user->id_departments);
        $canRequestToParent = $department->isChild(); // kalau anak → bisa minta tayang ke parent

        // Ambil departemen untuk dropdown
        $departments = Department::where('id_departments', $user->id_departments)->get();

        return view('content.edit', compact('content', 'departments', 'canRequestToParent'));
    }

    public function update(Request $request, $uuid)
    {
        // Ambil user yang sedang login
        $user = auth()->user();
        // Ambil departemen milik user
        $userDept = $user->id_departments;

        // Cari konten berdasarkan UUID + ikutkan relasi departemen
        $content = Content::with('departments')
            ->where('uuid', $uuid)
            ->firstOrFail();

        // Cek apakah konten ini memang milik departemen user
        $isOwnedByUserDept = $content->departments
            ->pluck('id_departments')
            ->contains($userDept);

        // Kalau bukan milik departemen user → tolak
        if (!$isOwnedByUserDept) {
            return redirect()->route('content.index')
                ->with('error', 'Anda hanya bisa mengupdate konten milik departemen Anda.');
        }

        // Simpan tipe file lama (cek apakah gambar/video)
        $oldFileMimeType = $content->file_server ? Storage::mimeType("public_content/" . now()->year . "/{$content->file_server}") : null;
        $oldIsImage = $oldFileMimeType ? str_starts_with($oldFileMimeType, 'image/') : null;

        // Validasi input request dari form
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:50',
            'description' => 'required|string|max:100',
            'file_original' => 'nullable|file|mimes:jpg,jpeg,png,mp4,mov,avi,webm|max:50240',
            'duration' => 'required|integer|in:5,10,20,30,60,90,120,180',
            'repeat_days.*' => 'nullable|in:1,2,3,4,5,6,7,all',
            'start_date' => 'required|date',
            'start_time' => 'required|date_format:H:i',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'end_time' => 'nullable|date_format:H:i',
            'is_visible_to_parent' => 'nullable|in:0,1',
        ], [
            // Pesan error custom
            'title.required' => 'Judul konten wajib diisi.',
            'title.max' => 'Judul tidak boleh lebih dari 50 karakter.',
            'description.required' => 'Deskripsi konten wajib diisi.',
            'description.max' => 'Deskripsi tidak boleh lebih dari 100 karakter.',
            'file_original.required' => 'File konten wajib diunggah.',
            'file_original.mimes' => 'File harus JPG, JPEG, PNG, MP4, MOV.',
            'file_original.max' => 'Ukuran file maksimal 50MB.',
            'duration.required' => $oldIsImage ? 'Durasi belum ditentukan untuk image' : 'Durasi tayang wajib diisi.',
            'repeat_days.required' => 'Minimal satu hari harus dipilih.',
            'repeat_days.*.in' => 'Hari yang dipilih tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'is_visible_to_parent.in' => 'Pilihan boleh dilihat oleh departemen induk tidak valid.',
        ]);

        // Ambil repeat_days dari request (kalau pilih "all" → ubah jadi 1-7)
        $repeatDays = $request->input('repeat_days', []);
        if (in_array('all', $repeatDays)) {
            $repeatDays = ['1', '2', '3', '4', '5', '6', '7'];
        }

        // Validasi tambahan manual
        $validator->after(function ($validator) use ($request, $repeatDays) {
            // Cek kalau tanggal sama, jam selesai harus > jam mulai
            if (
                $request->start_date === $request->end_date &&
                $request->start_time &&
                $request->end_time &&
                $request->start_time >= $request->end_time
            ) {
                $validator->errors()->add('end_time', 'Waktu selesai harus setelah waktu mulai.');
            }

            // Cek apakah repeat_days masuk dalam range tanggal
            $start = \Carbon\Carbon::parse($request->start_date);
            $end = $request->end_date ? \Carbon\Carbon::parse($request->end_date) : $start;
            $period = \Carbon\CarbonPeriod::create($start, $end);
            $daysInRange = collect($period)->map(fn($d) => $d->dayOfWeekIso)->unique()->values()->all();
            $invalidDays = array_diff($repeatDays, $daysInRange);

            if (!empty($invalidDays)) {
                $validator->errors()->add('repeat_days', 'Hari tayang yang dipilih tidak ada dalam rentang tanggal yang dipilih.');
            }
        });

        // Kalau validasi lolos → simpan hasilnya
        $validated = $validator->validate();

        // Mulai transaksi database
        DB::beginTransaction();

        try {
            $file = $request->file('file_original');
            $fileServerName = $content->file_server;
            $fileOriginalName = $content->file_original;

            // Daftar resolusi gambar yang valid
            $validImageSizes = [
                ['width' => 1024, 'height' => 1280],
                ['width' => 1080, 'height' => 1920],
                ['width' => 1080, 'height' => 1350],
            ];
            // Daftar resolusi video yang valid
            $validVideoSizes = [
                ['width' => 1024, 'height' => 1280],
                ['width' => 1080, 'height' => 1920],
                ['width' => 1080, 'height' => 1350],
                ['width' => 720, 'height' => 1280],
            ];
            $tolerance = 2; // toleransi perbedaan pixel

            if ($file && $file->isValid()) {
                // Kalau file baru berupa gambar
                if (str_starts_with($file->getMimeType(), 'image/')) {
                    [$width, $height] = getimagesize($file->getPathname());
                    $isValidSize = collect($validImageSizes)->contains(
                        fn($size) =>
                        abs($size['width'] - $width) <= $tolerance &&
                        abs($size['height'] - $height) <= $tolerance
                    );
                    if (!$isValidSize) {
                        return back()->withErrors(['file_original' => 'Resolusi gambar salah'])->withInput();
                    }
                }

                // Kalau file baru berupa video
                if (str_starts_with($file->getMimeType(), 'video/')) {
                    try {
                        $getID3 = new \getID3();
                        $fileInfo = $getID3->analyze($file->getPathname());
                        $width = $fileInfo['video']['resolution_x'] ?? null;
                        $height = $fileInfo['video']['resolution_y'] ?? null;

                        if (!$width || !$height) {
                            return back()->withErrors(['file_original' => 'Gagal membaca resolusi video'])->withInput();
                        }

                        $isValidSize = collect($validVideoSizes)->contains(
                            fn($size) =>
                            abs($size['width'] - $width) <= $tolerance &&
                            abs($size['height'] - $height) <= $tolerance
                        );
                        if (!$isValidSize) {
                            return back()->withErrors(['file_original' => 'Resolusi video salah'])->withInput();
                        }

                        // Kalau video, durasi ambil otomatis dari file
                        $validated['duration'] = (int) ($fileInfo['playtime_seconds'] ?? 0);
                    } catch (\Exception $e) {
                        return back()->withErrors(['file_original' => 'Gagal membaca durasi video'])->withInput();
                    }
                }

                // Simpan file baru ke storage
                $tahun = now()->year;
                $fileServerName = uniqid() . '.' . $file->getClientOriginalExtension();
                $fileOriginalName = $file->getClientOriginalName();

                // Hapus file lama kalau ada
                $oldPath = "public_content/{$tahun}/{$content->file_server}";
                if ($content->file_server && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }

                $file->storeAs("public_content/{$tahun}/", $fileServerName, 'public');
            } else {
                // Kalau tidak upload baru & file lama adalah video → pakai durasi lama
                if (!$oldIsImage) {
                    $validated['duration'] = $content->duration;
                }
            }
            
            // Update data konten ke database
            $content->update([ //validated itu hasil validasi yang sudah dicek, jadi aman buat langsung disimpan
                'title' => $validated['title'],
                'description' => $validated['description'],
                'repeat_days' => implode(',', $repeatDays),
                'start_date' => $validated['start_date'],
                'start_time' => $validated['start_time'],
                'end_date' => $validated['end_date'],
                'end_time' => $validated['end_time'],
                'duration' => $validated['duration'],
                'modified_at' => now(),
                'modified_by' => $user->id,
                'file_original' => $fileOriginalName,
                'file_server' => $fileServerName,
            ]);

            // Update pivot (relasi ke departemen) //pivot itu buat ngatur data tambahan di tabel relasi many-to-many, kalo disini ini buat atur is_visible_to_parent
            $isVisibleToParent = $request->input('is_visible_to_parent', '0') === '1';
            $content->departments()->updateExistingPivot($userDept, [
                'is_visible_to_parent' => $isVisibleToParent
            ]);

            // Commit transaksi
            DB::commit(); //komit di database, artinya semua perubahan yang dilakukan di database selama transaksi ini dianggap berhasil dan disimpan permanen. Kalau ada error sebelum commit, maka semua perubahan akan dibatalkan (rollback) otomatis, jadi database tetap konsisten.

            return redirect()->route('content.index', [
                'status_filter' => $request->input('status_filter'),
                'department_filter' => $request->input('department_filter'),
            ])->with('success', 'Konten berhasil diperbarui.');
        } catch (\Exception $e) {
            // Kalau gagal → rollback & catat error
            DB::rollBack();
            \Log::error('Error updating content: ' . $e->getMessage());

            return back()->with('error', 'Terjadi kesalahan saat mengupdate konten. ' . $e->getMessage());
        }
    }

    public function create()
    {
        $user = auth()->user();
        // ini untuk cek apakah visibility disembunyikan biar user tertentu ga bisa atur
        $hideVisibilityOption = optional($user->department)->parent_id === 'D000';
        // ini untuk ambil departemen user biar kontennya nyambung dengan departemen dia
        $departments = Department::where('id_departments', $user->id_departments)->get();

        // ini untuk kirim data ke form create biar bisa dipakai di blade
        return view('content.create', compact('departments', 'hideVisibilityOption'));
    }


    public function store(Request $request)
    {
        $user = auth()->user(); // ambil user yang lagi login
        $department = $user->department; // ambil departemen dari user

        $file = $request->file('file_original'); // ambil file upload, nanti dipakai terus

        // aturan validasi input
        $rules = [
            'title' => 'required|string|max:50', // judul wajib, max 50 huruf
            'description' => 'required|string|max:100', // deskripsi wajib, max 100 huruf
            'file_original' => 'required|file|mimes:jpg,jpeg,png,mp4,mov|max:50240', // file wajib, hanya gambar/video tertentu, max 50MB
            'duration' => 'nullable|integer|in:5,10,20,30,60,90,120,180', // durasi opsional, tapi harus salah satu dari angka ini
            'repeat_days' => 'required|array|min:1', // minimal pilih 1 hari
            'repeat_days.*' => 'in:1,2,3,4,5,6,7,all', // tiap hari hanya boleh angka 1–7 atau "all"
            'start_date' => 'required|date', // tanggal mulai wajib
            'start_time' => 'required|date_format:H:i', // jam mulai wajib
            'end_date' => 'required|date|after_or_equal:start_date', // tanggal selesai wajib, nggak boleh sebelum mulai
            'end_time' => 'required|date_format:H:i', // jam selesai wajib
            'is_visible_to_parent' => 'nullable|boolean', // opsional, cuma 0/1
        ];

        // pesan error custom
        $messages = [
            'title.required' => 'Judul konten wajib diisi.',
            'title.max' => 'Judul tidak boleh lebih dari 50 karakter.',
            'description.required' => 'Deskripsi konten wajib diisi.',
            'description.max' => 'Deskripsi tidak boleh lebih dari 100 karakter.',
            'file_original.required' => 'File konten wajib diunggah.',
            'file_original.mimes' => 'File harus JPG, JPEG, PNG, MP4, MOV.',
            'file_original.max' => 'Ukuran file terlalu besar (maks 50MB).',
            'duration.in' => 'Durasi harus salah satu dari 5, 10, 20, 30, 60, 90, 120, atau 180 detik.',
            'repeat_days.required' => 'Minimal satu hari harus dipilih.',
            'repeat_days.*.in' => 'Hari yang dipilih tidak valid.',
            'start_date.required' => 'Tanggal mulai wajib diisi.',
            'end_date.required' => 'Tanggal selesai wajib diisi.',
            'end_date.after_or_equal' => 'Tanggal selesai harus sama atau setelah tanggal mulai.',
            'start_time.required' => 'Jam mulai wajib diisi.',
            'end_time.required' => 'Jam selesai wajib diisi.',
            'end_time.date_format' => 'Format jam selesai tidak sesuai (contoh: 13:00).',
        ];

        $validator = Validator::make($request->all(), $rules, $messages); // jalankan validasi dasar

        $repeatDays = $request->input('repeat_days', []); // ambil pilihan hari
        if (in_array('all', $repeatDays)) {
            $repeatDays = ['1', '2', '3', '4', '5', '6', '7']; // kalau pilih "all", paksa jadi semua hari
        }

        // validasi tambahan
        $validator->after(function ($validator) use ($request, $repeatDays, $file) {
            // cek kalau tanggal sama, jam selesai nggak boleh <= jam mulai
            if (
                $request->start_date === $request->end_date &&
                $request->start_time &&
                $request->end_time &&
                $request->start_time >= $request->end_time
            ) {
                $validator->errors()->add('end_time', 'Waktu selesai harus setelah waktu mulai.');
            }

            // cek hari tayang sesuai rentang tanggal
            $start = \Carbon\Carbon::parse($request->start_date);
            $end = \Carbon\Carbon::parse($request->end_date);
            $daysInRange = collect(\Carbon\CarbonPeriod::create($start, $end))
                ->map(fn($d) => $d->dayOfWeekIso)
                ->unique()->values()->all();

            $invalidDays = array_diff($repeatDays, $daysInRange);
            if (!empty($invalidDays)) {
                $validator->errors()->add('repeat_days', 'Hari tayang yang dipilih tidak ada dalam rentang tanggal.');
            }

            // cek durasi berdasarkan tipe file
            if ($file && $file->isValid()) {
                $mime = $file->getMimeType();

                if (str_starts_with($mime, 'image/')) {
                    // gambar harus ada durasi
                    if (!$request->filled('duration')) {
                        $validator->errors()->add('duration', 'Durasi wajib diisi untuk gambar.');
                    }
                } elseif (str_starts_with($mime, 'video/')) {
                    // video → durasi otomatis kalau kosong
                    if (!$request->filled('duration')) {
                        try {
                            $getID3 = new \getID3();
                            $info = $getID3->analyze($file->getPathname());
                            $seconds = $info['playtime_seconds'] ?? null;

                            if ($seconds) {
                                $request->merge(['duration' => (int) ceil($seconds)]);
                            } else {
                                $validator->errors()->add('duration', 'Gagal baca durasi video.');
                            }
                        } catch (\Exception $e) {
                            $validator->errors()->add('duration', 'Gagal baca durasi video.');
                        }
                    }
                }
            }
        });

        $validator->validate(); // jalankan semua validasi

        DB::beginTransaction(); // mulai transaksi DB
        try {
            // cek resolusi file
            if ($file && $file->isValid()) {
                $validImageSizes = [
                    ['width' => 1024, 'height' => 1280],
                    ['width' => 1080, 'height' => 1920],
                    ['width' => 1080, 'height' => 1350],
                    ['width' => 1280, 'height' => 720],
                ];
                $validVideoSizes = [
                    ['width' => 1024, 'height' => 1280],
                    ['width' => 1080, 'height' => 1920],
                    ['width' => 1080, 'height' => 1350],
                    ['width' => 720, 'height' => 1280],
                    ['width' => 1280, 'height' => 720],
                ];
                $tolerance = 2; // toleransi beda pixel

                if (str_starts_with($file->getMimeType(), 'image/')) {
                    [$width, $height] = getimagesize($file->getPathname());
                    $isValid = collect($validImageSizes)->contains(
                        fn($s) =>
                        abs($s['width'] - $width) <= $tolerance &&
                        abs($s['height'] - $height) <= $tolerance
                    );
                    if (!$isValid) {
                        DB::rollBack();
                        return back()->withErrors(['file_original' => 'Resolusi gambar salah'])->withInput();
                    }
                }

                if (str_starts_with($file->getMimeType(), 'video/')) {
                    try {
                        $getID3 = new \getID3();
                        $fi = $getID3->analyze($file->getPathname());
                        $w = $fi['video']['resolution_x'] ?? null;
                        $h = $fi['video']['resolution_y'] ?? null;

                        if (!$w || !$h) {
                            DB::rollBack();
                            return back()->withErrors(['file_original' => 'Gagal baca resolusi video'])->withInput();
                        }

                        $isValid = collect($validVideoSizes)->contains(
                            fn($s) =>
                            abs($s['width'] - $w) <= $tolerance &&
                            abs($s['height'] - $h) <= $tolerance
                        );
                        if (!$isValid) {
                            DB::rollBack();
                            return back()->withErrors(['file_original' => 'Resolusi video salah'])->withInput();
                        }
                    } catch (\Exception $e) {
                        DB::rollBack();
                        return back()->withErrors(['file_original' => 'Gagal baca resolusi video'])->withInput();
                    }
                }
            }

            // simpan file ke storage
            $tahun = now()->year;
            $fileServerName = uniqid() . '.' . $file->getClientOriginalExtension(); // nama random
            $fileOriginalName = $file->getClientOriginalName(); // nama asli
            $file->storeAs("public_content/{$tahun}/", $fileServerName, 'public');

            // simpan ke DB
            $content = new Content();
            $content->uuid = Str::uuid(); // buat uuid random
            $content->title = $request->title;
            $content->description = $request->description;
            $content->repeat_days = implode(',', $repeatDays);
            $content->start_date = $request->start_date;
            $content->start_time = $request->start_time;
            $content->end_date = $request->end_date;
            $content->end_time = $request->end_time;
            $content->duration = $request->duration;
            $content->created_by = $user->id;
            $content->modified_by = $user->id;
            $content->file_server = $fileServerName;
            $content->file_original = $fileOriginalName;
            $content->save();

            // tentukan apakah konten anak bisa dilihat parent
            $isVisibleToParent = $department && $department->parent_id && $department->parent_id !== 'D000'
                ? $request->input('is_visible_to_parent', '0') == '1'
                : false;

            // simpan relasi ke tabel pivot
            $content->departments()->attach($user->id_departments, [
                'is_visible_to_parent' => $isVisibleToParent
            ]);

            DB::commit(); // simpan perubahan
            return redirect()->route('content.index')->with('success', 'Konten berhasil dibuat.');
        } catch (\Exception $e) {
            DB::rollBack(); // kalau ada error, rollback
            Log::error('Error saat membuat konten: ' . $e->getMessage()); // simpan ke log
            return back()->with('error', 'Gagal membuat konten.')->withInput();
        }
    }


    public function destroy($id)
    {
        $user = auth()->user();

        $content = Content::with('departments')->findOrFail($id);
        $contentDepartments = $content->departments->pluck('id_departments');

        // Hanya konten milik departemen sendiri
        if (!$contentDepartments->contains($user->id_departments)) {
            return redirect()->route('content.index')
                ->with('error', 'Anda hanya bisa menghapus konten milik departemen Anda.');
        }

        // Hapus semua relasi monitor_contents untuk konten ini
        \App\Models\MonitorContent::where('content_id', $content->id)->delete();

        // Hapus file dari public_content/{tahun}
        if ($content->file_server) {
            $tahun = \Carbon\Carbon::parse($content->created_at)->year;
            $path = "public_content/{$tahun}/{$content->file_server}";

            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        // Hapus konten dari tabel contents
        $content->delete();

        return redirect()->route('content.index')->with('success', 'Konten berhasil dihapus.');
    }

    public function requestTayang(Request $request, $id)
    {
        $user = auth()->user(); // ambil user yang login
        $departmentFilter = $request->query('department_filter'); // ambil filter departemen dari query string
        $statusFilter = $request->query('status_filter'); // ambil filter status dari query string

        // cari departemen anak berdasarkan filter uuid dan pastikan parent_id = departemen user
        $childDept = Department::where('uuid', $departmentFilter)
            ->where('parent_id', $user->id_departments)
            ->first();

        if ($childDept) {
            // update konten anak -> set is_tayang_request jadi 1 (minta tayang)
            MonitorContent::where('content_id', $id)
                ->where('id_departments', $childDept->id_departments)
                ->update(['is_tayang_request' => 1]);
        }

        // redirect ke halaman index dengan query filter tetap
        return redirect()
            ->route('content.index', [
                'department_filter' => $departmentFilter,
                'status_filter' => $statusFilter,
            ])
            ->with('success', 'Konten berhasil ditayangkan.'); // kasih flash message
    }


    public function cancelTayang(Request $request, $id)
    {
        $user = auth()->user(); // ambil user yang login
        $departmentFilter = $request->query('department_filter'); // ambil filter departemen
        $statusFilter = $request->query('status_filter'); // ambil filter status

        // cek apakah departemen filter valid sebagai anak dari user
        $childDept = Department::where('uuid', $departmentFilter)
            ->where('parent_id', $user->id_departments)
            ->first();

        if ($childDept) {
            // update konten anak -> set is_tayang_request jadi 0 (batalkan tayang)
            MonitorContent::where('content_id', $id)
                ->where('id_departments', $childDept->id_departments)
                ->update(['is_tayang_request' => 0]);
        }

        // redirect balik ke index dengan filter
        return redirect()
            ->route('content.index', [
                'department_filter' => $departmentFilter,
                'status_filter' => $statusFilter,
            ])
            ->with('success', 'Tayang konten dibatalkan.'); // flash message
    }


    public function listOnly(Request $request)
    {
        $today = Carbon::now()->startOfMinute(); // waktu sekarang (dibulatkan ke menit)
        $user = auth()->user(); // user login
        $adminDeptId = $user->id_departments; // ambil id departemen admin
        $departmentFilter = $request->query('department_filter'); // filter departemen dari query
        $statusFilter = $request->query('status_filter', 'sedang'); // filter status (default sedang)

        $departments = Department::where('parent_id', $adminDeptId)->get(); // ambil anak departemen
        $query = Content::with('departments'); // query dasar ambil konten + relasi departemen
        $isViewingChild = false; // flag apakah lagi lihat anak

        if (!$departmentFilter || $departmentFilter === 'self') {
            // kalau filter kosong atau 'self' → ambil konten departemen user sendiri
            $query->whereHas('departments', fn($q) => $q->where('departments.id_departments', $adminDeptId));
        } else {
            // kalau ada filter → cek apakah itu memang anak departemen user
            $isChild = Department::where('uuid', $departmentFilter)->where('parent_id', $adminDeptId)->first();
            if ($isChild) {
                $isViewingChild = true; // aktifkan flag anak
                // ambil konten anak departemen yang visible ke parent
                $query->whereHas('departments', fn($q) => $q->where('departments.uuid', $departmentFilter)
                    ->where('monitor_contents.is_visible_to_parent', true));
            }
        }

        $contents = $query->get(); // eksekusi query

        // loop tiap konten untuk hitung status & flag
        $contents->each(function ($content) use ($today) {
            $nowDate = $today->toDateString(); // tanggal sekarang (Y-m-d)
            $nowDay = $today->dayOfWeekIso;    // hari sekarang (1–7)

            $startDate = $content->start_date; // tanggal mulai konten
            $endDate = $content->end_date ?? $startDate; // tanggal selesai (fallback ke start_date)
            $startTime = $content->start_time ?? '00:00'; // jam mulai (default 00:00)
            $endTime = $content->end_time ?? '23:59'; // jam selesai (default 23:59)

            $startDateTime = Carbon::parse($startDate . ' ' . $startTime); // gabung tanggal + jam mulai
            $endDateTime = Carbon::parse($endDate . ' ' . $endTime); // gabung tanggal + jam selesai

            $repeatDays = explode(',', $content->repeat_days ?? ''); // pecah repeat_days jadi array
            $isRepeatToday = in_array($nowDay, $repeatDays); // cek apakah hari ini masuk repeat

            // tentukan status konten
            if ($today->lt($startDateTime)) {
                $content->status = 'Akan Tayang'; // belum mulai
            } elseif ($today->gt($endDateTime)) {
                $content->status = 'Sudah Selesai'; // lewat
            } elseif ($isRepeatToday || $startDate == $nowDate || $endDate == $nowDate) {
                $content->status = $today->between($startDateTime, $endDateTime)
                    ? 'Sedang Tayang' // sekarang sedang tayang
                    : 'Akan Tayang'; // waktunya belum masuk
            } else {
                $content->status = 'Akan Tayang'; // default fallback
            }

            // cek flag is_visible_to_parent (relasi pivot)
            $content->isVisible = $content->departments
                ->flatMap(fn($d) => $d->pivot ? [$d->pivot->is_visible_to_parent] : [])
                ->contains(true);

            // cek flag is_tayang_request (relasi pivot)
            $content->isTayangRequest = $content->departments
                ->flatMap(fn($d) => $d->pivot ? [$d->pivot->is_tayang_request] : [])
                ->contains(true);
        });

        // filter konten sesuai kondisi
        $contentsToShow = $contents->filter(function ($c) use ($statusFilter, $isViewingChild, $departmentFilter, $adminDeptId) {
            if ($isViewingChild) {
                // kalau lihat anak → hanya konten sedang tayang & visible
                return $c->status === 'Sedang Tayang' && $c->isVisible;
            } else {
                $isChildContent = $c->departments->contains(fn($d) => $d->parent_id == $adminDeptId);
                if ($isChildContent) {
                    // kalau konten anak → harus sedang tayang, visible, dan sudah request
                    return $c->status === 'Sedang Tayang' && $c->isVisible && $c->isTayangRequest;
                } else {
                    // kalau konten sendiri → filter pakai status filter biasa
                    return match ($statusFilter) {
                        'akan' => $c->status === 'Akan Tayang',
                        'sudah' => $c->status === 'Sudah Selesai',
                        default => $c->status === 'Sedang Tayang',
                    };
                }
            }
        });

        // balikin JSON berisi hanya judul konten
        return response()->json($contentsToShow->pluck('title'));
    }
}

// dd($request->all());