<?php
namespace App\Http\Controllers;


class HomeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');  // Pastikan user sudah login
    }

    /**
     * Menampilkan dashboard berdasarkan role
     */
    public function index()
    {
        // Cek role pengguna dan arahkan ke dashboard yang sesuai
        if (auth()->user()->role_id === 'RL01') { // Superadmin
            return redirect()->route('superadmin.dashboard');
        } elseif (auth()->user()->role_id === 'RL02') { // Admin
            return redirect()->route('admin.dashboard');
        }

        // Jika tidak ada role yang sesuai, arahkan ke halaman login
        return redirect()->route('login');
    }
}

