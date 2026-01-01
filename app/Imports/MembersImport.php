<?php

namespace App\Imports;

use App\Models\Member;
use App\Models\Instansi;
use App\Models\Posisi;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsEmptyRows;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class MembersImport implements ToCollection, WithHeadingRow, SkipsEmptyRows
{
    public $imported = 0;
    public $skipped = 0;
    public $errors = [];

    public function collection(Collection $rows)
    {
        Log::info('Import started', ['total_rows' => $rows->count()]);

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;
            
            try {
                $row = $row->toArray();
                $row = array_change_key_case($row, CASE_LOWER);
                
                Log::info("Processing row {$rowNumber}", $row);

                if (empty($row['nama_lengkap'])) {
                    Log::warning("Row {$rowNumber}: nama_lengkap kosong, skip");
                    $this->skipped++;
                    continue;
                }

                $nik = $this->parseNumericToString($row['nik'] ?? null, 16);
                $noKaryawan = !empty($row['no_karyawan']) ? trim((string) $row['no_karyawan']) : null;
                $email = !empty($row['email']) ? trim((string) $row['email']) : null;

                if ($nik && Member::where('nik', $nik)->exists()) {
                    Log::warning("Row {$rowNumber}: NIK {$nik} sudah ada, skip");
                    $this->skipped++;
                    $this->errors[] = "Baris {$rowNumber}: NIK {$nik} sudah terdaftar";
                    continue;
                }
                
                if ($noKaryawan && Member::where('no_karyawan', $noKaryawan)->exists()) {
                    Log::warning("Row {$rowNumber}: No Karyawan {$noKaryawan} sudah ada, skip");
                    $this->skipped++;
                    $this->errors[] = "Baris {$rowNumber}: No Karyawan {$noKaryawan} sudah terdaftar";
                    continue;
                }
                
                if ($email && Member::where('email', $email)->exists()) {
                    Log::warning("Row {$rowNumber}: Email {$email} sudah ada, skip");
                    $this->skipped++;
                    $this->errors[] = "Baris {$rowNumber}: Email {$email} sudah terdaftar";
                    continue;
                }

                $instansi = null;
                if (!empty($row['instansi'])) {
                    $instansi = Instansi::where('nama', 'like', '%' . trim($row['instansi']) . '%')->first();
                }

                $posisi = null;
                if (!empty($row['posisi'])) {
                    $posisi = Posisi::where('nama', 'like', '%' . trim($row['posisi']) . '%')->first();
                }

                $member = Member::create([
                    'no_karyawan' => $noKaryawan,
                    'nik' => $nik,
                    'name' => trim($row['nama_lengkap']),
                    'jenis_kelamin' => $this->parseJenisKelamin($row['jenis_kelamin'] ?? null),
                    'tanggal_lahir' => $this->parseDate($row['tanggal_lahir'] ?? null),
                    'alamat' => $row['alamat'] ?? null,
                    'email' => $email,
                    'whatsapp' => $this->parseNumericToString($row['whatsapp'] ?? null),
                    'instansi_id' => $instansi ? $instansi->id : null,
                    'posisi_id' => $posisi ? $posisi->id : null,
                    'tanggal_masuk' => $this->parseDate($row['tanggal_masuk'] ?? null),
                    'tanggal_kontrak_berakhir' => $this->parseDate($row['tanggal_kontrak_berakhir'] ?? null),
                    'status_kepegawaian' => $this->parseStatusKepegawaian($row['status_kepegawaian'] ?? null),
                    'status' => $this->parseStatusAkun($row['status_akun'] ?? null),
                    'password' => Hash::make($row['password'] ?? '12345678'),
                ]);

                Log::info("Row {$rowNumber}: Member created", ['id' => $member->id, 'name' => $member->name]);
                $this->imported++;

            } catch (\Exception $e) {
                Log::error("Row {$rowNumber}: Error - " . $e->getMessage());
                $this->errors[] = "Baris {$rowNumber}: " . $e->getMessage();
                $this->skipped++;
            }
        }

        Log::info('Import completed', ['imported' => $this->imported, 'skipped' => $this->skipped]);
    }

    private function parseNumericToString($value, $padLength = null)
    {
        if (empty($value) && $value !== '0') {
            return null;
        }

        $value = (string) $value;
        $value = ltrim($value, "'\"");

        if (preg_match('/^[\d.]+E\+?\d+$/i', $value)) {
            $value = sprintf('%.0f', (float) $value);
        }

        if (is_numeric($value) && strpos($value, '.') !== false) {
            $value = sprintf('%.0f', (float) $value);
        }

        if ($padLength !== null) {
            $value = preg_replace('/[^0-9]/', '', $value);
        }

        if ($padLength !== null && strlen($value) < $padLength && strlen($value) > 0) {
            $value = str_pad($value, $padLength, '0', STR_PAD_LEFT);
        }

        return !empty($value) ? $value : null;
    }

    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            if (is_numeric($value)) {
                return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $value));
            }

            $value = trim((string) $value);
            $formats = ['d/m/Y', 'd-m-Y', 'Y-m-d', 'd/m/y', 'd-m-y'];

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

            return Carbon::parse($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    private function parseJenisKelamin($value)
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

    private function parseStatusKepegawaian($value)
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
            'inactive' => 'nonaktif',
            '0' => 'nonaktif',
            'cuti' => 'cuti',
            'resign' => 'resign',
            'keluar' => 'resign',
        ];

        return $mapping[$value] ?? 'aktif';
    }

    private function parseStatusAkun($value)
    {
        if (empty($value)) {
            return true;
        }

        $value = strtolower(trim((string) $value));

        return in_array($value, ['aktif', 'active', 'ya', 'yes', '1', 'true', 'on']);
    }
}