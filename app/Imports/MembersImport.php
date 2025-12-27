<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\Instansi;
use App\Models\Posisi;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Validators\Failure;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Throwable;

class MembersImport implements ToModel, WithHeadingRow, WithValidation, SkipsEmptyRows, SkipsOnError, SkipsOnFailure
{
    use Importable;

    private $errors = [];
    private $failures = [];

    public function model(array $row)
    {
        // Cari instansi berdasarkan nama
        $instansi = null;
        if (!empty($row['instansi'])) {
            $instansi = Instansi::where('nama', trim($row['instansi']))->first();
        }

        // Cari posisi berdasarkan nama
        $posisi = null;
        if (!empty($row['posisi'])) {
            $posisi = Posisi::where('nama', trim($row['posisi']))->first();
        }

        // Parse tanggal
        $tanggalLahir = $this->parseDate($row['tanggal_lahir'] ?? null);
        $tanggalMasuk = $this->parseDate($row['tanggal_masuk'] ?? null);
        $tanggalKontrakBerakhir = $this->parseDate($row['tanggal_kontrak_berakhir'] ?? null);

        // Parse NIK - handle scientific notation
        $nik = $this->parseNumericToString($row['nik'] ?? null, 16);

        // Parse WhatsApp - handle scientific notation
        $whatsapp = $this->parseNumericToString($row['whatsapp'] ?? null);

        // Parse No Karyawan
        $noKaryawan = isset($row['no_karyawan']) ? trim((string) $row['no_karyawan']) : null;

        // Parse jenis kelamin
        $jenisKelamin = $this->parseJenisKelamin($row['jenis_kelamin'] ?? null);

        // Parse status kepegawaian
        $statusKepegawaian = $this->parseStatusKepegawaian($row['status_kepegawaian'] ?? null);

        // Parse status akun
        $statusAkun = $this->parseStatusAkun($row['status_akun'] ?? null);

        // Cek duplikat sebelum insert
        if ($nik && Member::where('nik', $nik)->exists()) {
            return null;
        }
        if ($noKaryawan && Member::where('no_karyawan', $noKaryawan)->exists()) {
            return null;
        }
        if (!empty($row['email']) && Member::where('email', $row['email'])->exists()) {
            return null;
        }

        return new Member([
            'no_karyawan' => $noKaryawan,
            'nik' => $nik,
            'name' => trim($row['nama_lengkap']),
            'jenis_kelamin' => $jenisKelamin,
            'tanggal_lahir' => $tanggalLahir,
            'alamat' => $row['alamat'] ?? null,
            'email' => !empty($row['email']) ? trim($row['email']) : null,
            'whatsapp' => $whatsapp,
            'instansi_id' => $instansi?->id,
            'posisi_id' => $posisi?->id,
            'tanggal_masuk' => $tanggalMasuk,
            'tanggal_kontrak_berakhir' => $tanggalKontrakBerakhir,
            'status_kepegawaian' => $statusKepegawaian,
            'status' => $statusAkun,
            'password' => Hash::make($row['password'] ?? '12345678'),
        ]);
    }

    /**
     * Parse numeric value to string - handle scientific notation
     */
    private function parseNumericToString($value, ?int $padLength = null): ?string
    {
        if (empty($value) && $value !== '0') {
            return null;
        }

        $value = (string) $value;

        // Hapus petik di depan jika ada
        $value = ltrim($value, "'\"");

        // Handle scientific notation (contoh: 3.20123E+15)
        if (preg_match('/^[\d.]+E\+?\d+$/i', $value)) {
            // Gunakan sprintf untuk convert tanpa kehilangan presisi
            $value = sprintf('%.0f', (float) $value);
        }

        // Handle float yang mungkin dari Excel
        if (is_numeric($value) && strpos($value, '.') !== false) {
            $value = sprintf('%.0f', (float) $value);
        }

        // Hapus karakter non-numerik (kecuali untuk no_karyawan yang bisa mengandung huruf)
        if ($padLength !== null) {
            $value = preg_replace('/[^0-9]/', '', $value);
        }

        // Pad dengan 0 di depan jika perlu (untuk NIK)
        if ($padLength !== null && strlen($value) < $padLength && strlen($value) > 0) {
            $value = str_pad($value, $padLength, '0', STR_PAD_LEFT);
        }

        return !empty($value) ? $value : null;
    }

    /**
     * Parse tanggal dari berbagai format
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Jika numeric (Excel serial date)
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value));
            }

            $value = trim((string) $value);

            // Coba berbagai format tanggal
            $formats = [
                'd/m/Y',
                'd-m-Y',
                'Y-m-d',
                'd/m/y',
                'd-m-y',
                'Y/m/d',
                'd.m.Y',
                'd.m.y',
            ];

            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $value);
                    if ($date !== false) {
                        return $date;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Terakhir coba parse otomatis
            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Parse jenis kelamin
     */
    private function parseJenisKelamin($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        $value = strtolower(trim((string) $value));

        if (in_array($value, ['l', 'laki-laki', 'laki', 'pria', 'male', 'm', '1'])) {
            return 'L';
        }

        if (in_array($value, ['p', 'perempuan', 'wanita', 'female', 'f', '2'])) {
            return 'P';
        }

        return null;
    }

    /**
     * Parse status kepegawaian
     */
    private function parseStatusKepegawaian($value): string
    {
        if (empty($value)) {
            return 'aktif';
        }

        $value = strtolower(trim((string) $value));

        $mapping = [
            'aktif' => 'aktif',
            'active' => 'aktif',
            '1' => 'aktif',
            'nonaktif' => 'nonaktif',
            'non-aktif' => 'nonaktif',
            'non aktif' => 'nonaktif',
            'inactive' => 'nonaktif',
            '0' => 'nonaktif',
            'cuti' => 'cuti',
            'leave' => 'cuti',
            'resign' => 'resign',
            'keluar' => 'resign',
            'quit' => 'resign',
        ];

        return $mapping[$value] ?? 'aktif';
    }

    /**
     * Parse status akun
     */
    private function parseStatusAkun($value): bool
    {
        if (empty($value)) {
            return true;
        }

        $value = strtolower(trim((string) $value));

        $activeValues = ['aktif', 'active', 'ya', 'yes', '1', 'true', 'on'];

        return in_array($value, $activeValues);
    }

    public function rules(): array
    {
        return [
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'nullable|email',
        ];
    }

    public function customValidationMessages(): array
    {
        return [
            'nama_lengkap.required' => 'Nama lengkap wajib diisi',
            'email.email' => 'Format email tidak valid',
        ];
    }

    public function onError(Throwable $e)
    {
        $this->errors[] = $e->getMessage();
    }

    public function onFailure(Failure ...$failures)
    {
        foreach ($failures as $failure) {
            $this->failures[] = $failure;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getFailures(): array
    {
        return $this->failures;
    }
}