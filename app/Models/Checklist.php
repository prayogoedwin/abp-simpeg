<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    protected $fillable = [
        'instansi_id',
        'member_id',
        'template_name',
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class);
    }

    public function member()
    {
        return $this->belongsTo(Member::class);
    }

    

    public function details()
    {
        return $this->hasMany(ChecklistDetail::class);
    }

    public static function getRekapBulanan(int $memberId, int $bulan, int $tahun): array
    {
        $startDate = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        
        $checklists = self::where('member_id', $memberId)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->get()
            ->keyBy(fn ($item) => $item->created_at->format('Y-m-d'));

        $rekap = [];
        $currentDate = $startDate->copy();

        while ($currentDate <= $endDate) {
            $dateKey = $currentDate->format('Y-m-d');
            $checklist = $checklists->get($dateKey);

            $rekap[] = [
                'checklist_id' => $checklist?->id,
                'tanggal' => $currentDate->copy(),
                'hari' => $currentDate->translatedFormat('l'),
                'nama_template' => $checklist?->template_name ?? '-',
            ];

            $currentDate->addDay();
        }

        

        return $rekap;
    }
}
