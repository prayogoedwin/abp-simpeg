<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    /**
     * Login member
     * Bisa login pakai: email, nik, no_karyawan, atau whatsapp
     */
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $username = $request->username;
        $password = $request->password;
        $deviceName = $request->device_name ?? 'mobile';

        // Cari member by email, nik, no_karyawan, atau whatsapp
        $member = Member::where('email', $username)
            ->orWhere('nik', $username)
            ->orWhere('no_karyawan', $username)
            ->orWhere('whatsapp', $username)
            ->first();

        if (!$member) {
            throw ValidationException::withMessages([
                'username' => ['Akun tidak ditemukan.'],
            ]);
        }

        if (!Hash::check($password, $member->password)) {
            throw ValidationException::withMessages([
                'password' => ['Password salah.'],
            ]);
        }

        // Cek status member
        if (!$member->status) {
            throw ValidationException::withMessages([
                'username' => ['Akun Anda tidak aktif. Hubungi admin.'],
            ]);
        }

        // Revoke token lama (optional, biar cuma 1 device)
        // $member->tokens()->delete();

        // Buat token baru
        $token = $member->createToken($deviceName)->plainTextToken;

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'data' => [
                'member' => [
                    'id' => $member->id,
                    'name' => $member->name,
                    'email' => $member->email,
                    'nik' => $member->nik,
                    'no_karyawan' => $member->no_karyawan,
                    'whatsapp' => $member->whatsapp,
                    'foto' => $member->foto ? asset('storage/' . $member->foto) : null,
                    'instansi' => $member->instansi?->only(['id', 'nama', 'alamat', 'lat', 'lng','radius']),
                    'posisi' => $member->posisi?->only(['id', 'nama']),
                ],
                'token' => $token,
            ],
        ]);
    }

    /**
     * Logout member
     */
    public function logout(Request $request)
    {
        // Revoke current token
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil',
        ]);
    }

    /**
     * Get profile member yang sedang login
     */
    public function profile(Request $request)
    {
        $member = $request->user();
        $member->load(['instansi', 'posisi']);

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'nik' => $member->nik,
                'no_karyawan' => $member->no_karyawan,
                'whatsapp' => $member->whatsapp,
                'alamat' => $member->alamat,
                'jenis_kelamin' => $member->jenis_kelamin,
                'jenis_kelamin_label' => $member->jenis_kelamin_label,
                'tanggal_lahir' => $member->tanggal_lahir?->format('Y-m-d'),
                'umur' => $member->umur,
                'tanggal_masuk' => $member->tanggal_masuk?->format('Y-m-d'),
                'masa_kerja' => $member->masa_kerja,
                'status_kepegawaian' => $member->status_kepegawaian,
                'foto' => $member->foto ? asset('storage/' . $member->foto) : null,
                'instansi' => $member->instansi?->only(['id', 'nama', 'alamat', 'lat', 'lng','radius']),
                'posisi' => $member->posisi?->only(['id', 'nama']),
            ],
        ]);
    }

    /**
     * Update profile member
     */
    public function updateProfile(Request $request)
    {
        $member = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'nik' => 'required|string|max:255',
            'email' => 'required|email|unique:members,email,' . $member->id,
            'whatsapp' => 'nullable|string|max:20|unique:members,whatsapp,' . $member->id,
            'alamat' => 'nullable|string|max:500',
            'jenis_kelamin' => 'nullable|in:L,P',
            'tanggal_lahir' => 'nullable|date',
            'foto' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
        ]);

        $data = $request->only([
            'name',
            'nik',
            'email',
            'whatsapp',
            'alamat',
            'jenis_kelamin',
            'tanggal_lahir',
        ]);

        // Handle upload foto
        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($member->foto && Storage::disk('public')->exists($member->foto)) {
                Storage::disk('public')->delete($member->foto);
            }

            $data['foto'] = $request->file('foto')->store('pegawai', 'public');
        }

        $member->update($data);
        $member->load(['instansi', 'posisi']);

        return response()->json([
            'success' => true,
            'message' => 'Profil berhasil diperbarui',
            'data' => [
                'id' => $member->id,
                'name' => $member->name,
                'email' => $member->email,
                'nik' => $member->nik,
                'no_karyawan' => $member->no_karyawan,
                'whatsapp' => $member->whatsapp,
                'alamat' => $member->alamat,
                'jenis_kelamin' => $member->jenis_kelamin,
                'jenis_kelamin_label' => $member->jenis_kelamin_label,
                'tanggal_lahir' => $member->tanggal_lahir?->format('Y-m-d'),
                'umur' => $member->umur,
                'foto' => $member->foto ? asset('storage/' . $member->foto) : null,
                'instansi' => $member->instansi?->only(['id', 'nama', 'alamat', 'lat', 'lng' ,'radius']),
                'posisi' => $member->posisi?->only(['id', 'nama']),
            ],
        ]);
    }

    /**
     * Update password member
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $member = $request->user();

        // Cek password lama
        if (!Hash::check($request->current_password, $member->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password lama tidak sesuai.'],
            ]);
        }

        // Update password baru
        $member->update([
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Password berhasil diperbarui',
        ]);
    }
}