@extends('layouts.app')

@section('content')

   {{-- Notifikasi sukses --}}
   @if (session('success'))
    <div class="alert alert-success alert-dismissible fade show shadow-sm auto-dismiss" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i>
        {{ session('success') }}
    </div>
@endif

{{-- Notifikasi error --}}
@if (session('error'))
    <div class="alert alert-danger alert-dismissible fade show shadow-sm auto-dismiss" role="alert">
        <i class="bi bi-x-circle-fill me-2"></i>
        {{ session('error') }}
    </div>
@endif


    <div class="container py-5" id="theme-container">
        {{-- Tombol tambah konten --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <a href="{{ route('content.create') }}" class="btn btn-success btn-sm shadow-sm">
                <i class="bi bi-plus-circle me-1"></i> Tambah Konten
            </a>
        </div>

        {{-- Filter --}}
        <div class="filter-box mb-4">
            <div class="filter-box-inner">
                <form method="GET" class="row g-3 align-items-end">
                    {{-- Filter departemen --}}
                    <div class="col-md-6">
                        <label for="department_filter" class="form-label fw-bold">Filter Departemen</label>
                        <select id="department_filter" name="department_filter" class="form-select shadow-sm">
                            <option value="self" @selected(request('department_filter') == 'self')>Departemen Saya</option>
                            @foreach ($departments as $dept)
                                <option value="{{ $dept->uuid }}" @selected(request('department_filter') == $dept->uuid)>
                                    {{ $dept->name_departments }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Filter status hanya tampil kalau TIDAK sedang melihat konten anak --}}
                    <div class="col-md-6 {{ $isViewingChild ? 'd-none' : '' }}" id="statusBox">
                        <label for="status_filter" class="form-label fw-bold">Filter Status</label>
                        <select name="status_filter" id="status_filter" class="form-select shadow-sm">
                            <option value="sedang" @selected($statusFilter == 'sedang')>Sedang Tayang</option>
                            <option value="akan" @selected($statusFilter == 'akan')>Akan Tayang</option>
                            <option value="sudah" @selected($statusFilter == 'sudah')>Sudah Tayang</option>
                        </select>
                    </div>

                </form>
            </div>
        </div>

        {{-- Tombol titik tiga daftar konten --}}
        <div class="d-flex justify-content-end mb-2 position-relative">
            <button class="btn btn-sm btn-outline-secondary" id="kontenTrigger">
                <i class="bi bi-three-dots-vertical"></i> Daftar Konten
            </button>

            {{-- Dropdown daftar konten --}}
            <div id="kontenDropdown" class="konten-dropdown d-none">
                <div class="fw-bold mb-2" id="dropdownJudul">Konten Sedang Tayang:</div>
                <table class="table table-sm table-bordered table-striped mb-0">
                    <thead>
                        <tr>
                            <th style="width: 40px">No</th>
                            <th>Judul</th>
                        </tr>
                    </thead>
                    <tbody id="dropdownList">
                        <tr>
                            <td colspan="2"><em>Memuat konten...</em></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Carousel konten --}}
        <div class="carousel-container position-relative mb-4">
            {{-- Tombol kiri --}}
            <button class="carousel-control-prev" onclick="scrollCarousel(-1)">
                <i class="bi bi-chevron-left"></i>
            </button>

            {{-- Wrapper isi carousel --}}
            <div class="content-carousel d-flex flex-nowrap gap-4 overflow-hidden" id="carouselWrapper">
                @include('content.partials.content-list')
            </div>

            {{-- Tombol kanan --}}
            <button class="carousel-control-next" onclick="scrollCarousel(1)">
                <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
@endsection

@once
    @push('scripts')
        {{-- CSS khusus halaman konten --}}
        <link href="{{ asset('css/index_content.css') }}" rel="stylesheet">

        {{-- Auto dismiss untuk alert --}}
        <script>
            document.addEventListener("DOMContentLoaded", function () {
                document.querySelectorAll('.auto-dismiss').forEach(function (alertEl) {
                    setTimeout(() => {
                        let alert = new bootstrap.Alert(alertEl);
                        alert.close();
                    }, 3000); // 3 detik
                });
            });
        </script>

        {{-- Script utama konten --}}
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                // --- Variabel element penting ---
                const deptSelect = document.getElementById('department_filter');
                const statusSelect = document.getElementById('status_filter');
                const carouselWrapper = document.getElementById('carouselWrapper');
                const kontenTrigger = document.getElementById('kontenTrigger');
                const kontenDropdown = document.getElementById('kontenDropdown');
                const dropdownJudul = document.getElementById('dropdownJudul');
                const dropdownList = document.getElementById('dropdownList');

                // --- Tampilkan / sembunyikan filter status ---
                function updateStatusBoxVisibility() {
                    const selectedDept = deptSelect?.value || 'self';
                    const isChild = [...deptSelect.options].some(opt =>
                        opt.value !== 'self' && opt.value === selectedDept
                    );
                    const statusBox = document.getElementById('status_filter')?.closest('.col-md-6');
                    if (statusBox) {
                        statusBox.classList.toggle('d-none', isChild);
                    }
                }

                // --- Update daftar konten + carousel via AJAX ---
                function updateKonten() {
                    const dept = deptSelect?.value || 'self';
                    const status = statusSelect?.value || 'sedang';

                    // Update URL di browser tanpa reload
                    const url = new URL(window.location.href);
                    url.searchParams.set('department_filter', dept);
                    url.searchParams.set('status_filter', status);
                    window.history.pushState({}, '', url.toString());

                    // Animasi fade out
                    carouselWrapper.style.opacity = 0;
                    carouselWrapper.scrollTo({ left: 0, behavior: 'instant' });

                    // --- Update daftar titik tiga ---
                    fetch(`/content-list-only?department_filter=${dept}&status_filter=${status}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(res => res.json())
                        .then(data => {
                            dropdownJudul.innerText = `Konten ${status.charAt(0).toUpperCase() + status.slice(1)} Tayang:`;

                            if (data.length > 0) {
                                dropdownList.innerHTML = data.map((title, i) => `
                                    <tr class="list-item" data-index="${i}" style="cursor:pointer;">
                                        <td class="text-center">${i + 1}</td>
                                        <td>${title}</td>
                                    </tr>`).join('');
                            } else {
                                dropdownList.innerHTML = `<tr><td colspan="2"><em>Tidak ada konten ditemukan.</em></td></tr>`;
                            }

                            // Tambahin event klik ke tiap row list
                            const rows = dropdownList.querySelectorAll(".list-item");
                            rows.forEach(row => {
                                row.addEventListener("click", () => {
                                    const index = parseInt(row.dataset.index, 10);
                                    const slides = carouselWrapper.querySelectorAll(".content-card-hover");
                                    if (!slides.length) return;

                                    const slideWidth = slides[0].offsetWidth + 16; // 16px jarak antar slide
                                    const targetSlide = slides[index];

                                    // Scroll ke slide sesuai index
                                    carouselWrapper.scrollTo({
                                        left: slideWidth * index,
                                        behavior: "smooth"
                                    });

                                    // Efek highlight (glow) di slide yang diklik
                                    if (targetSlide.classList.contains("active-glow")) {
                                        targetSlide.classList.remove("active-glow");
                                    } else {
                                        slides.forEach(s => s.classList.remove("active-glow"));
                                        targetSlide.classList.add("active-glow");
                                    }
                                });
                            });
                        });

                    // --- Update konten utama carousel ---
                    fetch(`/content?department_filter=${dept}&status_filter=${status}`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                        .then(res => res.text())
                        .then(html => {
                            setTimeout(() => {
                                carouselWrapper.innerHTML = html;
                                carouselWrapper.style.opacity = 1;

                                // Animasi fade-in
                                carouselWrapper.classList.add('fade-in');
                                setTimeout(() => carouselWrapper.classList.remove('fade-in'), 300);

                                attachDotEvents(); // refresh event dot
                                updateStatusBoxVisibility();
                            }, 300);
                        });
                }

                // --- Scroll carousel kiri/kanan ---
                window.scrollCarousel = function (direction) {
                    const wrapper = document.getElementById('carouselWrapper');
                    const slide = wrapper.querySelector('.content-card-hover');
                    if (!slide) return;

                    const slideWidth = slide.offsetWidth + 16;
                    wrapper.scrollBy({
                        left: slideWidth * direction,
                        behavior: 'smooth'
                    });

                    // Update dot aktif
                    const dots = document.querySelectorAll('.carousel-dot');
                    const currentIndex = Math.round(wrapper.scrollLeft / slideWidth) + direction;
                    dots.forEach(dot => dot.classList.remove('active'));
                    if (dots[currentIndex]) dots[currentIndex].classList.add('active');
                }

                // --- Event klik dot navigasi ---
                function attachDotEvents() {
                    const dots = document.querySelectorAll('.carousel-dot');
                    dots.forEach(dot => {
                        dot.addEventListener('click', () => {
                            const index = parseInt(dot.dataset.index, 10);
                            const slide = carouselWrapper.querySelector('.content-card-hover');
                            if (!slide) return;
                            const slideWidth = slide.offsetWidth + 16;
                            carouselWrapper.scrollTo({
                                left: slideWidth * index,
                                behavior: 'smooth'
                            });
                            dots.forEach(d => d.classList.remove('active'));
                            dot.classList.add('active');
                        });
                    });
                }

                // --- Initial load ---
                updateKonten();
                updateStatusBoxVisibility();

                // Event ganti filter
                deptSelect?.addEventListener('change', () => {
                    updateStatusBoxVisibility();
                    updateKonten();
                });
                statusSelect?.addEventListener('change', updateKonten);

                attachDotEvents();

                // --- Toggle daftar konten (titik tiga) ---
                kontenTrigger?.addEventListener('click', () => {
                    kontenDropdown.classList.toggle('d-none');
                });
                document.addEventListener('click', (e) => {
                    if (!kontenTrigger.contains(e.target) && !kontenDropdown.contains(e.target)) {
                        kontenDropdown.classList.add('d-none');
                    }
                });
            });
        </script>
    @endpush
@endonce
