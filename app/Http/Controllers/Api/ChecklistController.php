<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Checklist;
use App\Models\ChecklistDetail;
use App\Models\ChecklistTemplate;
use Illuminate\Http\Request;

class ChecklistController extends Controller
{
    public function submit(Request $request)
    {
        // dd($request->all());
        $checklistTemplate = ChecklistTemplate::find($request->template_id);

        // dd($checklistTemplate);

        $checklist = Checklist::create([
            'checklist_template_id' => $checklistTemplate->id,
            'instansi_id' => $checklistTemplate->instansi_id,
            'member_id' => auth()->user()->id,
        ]);



        foreach ($request->answers as $detailId => $answer) {
            
            // Jika tipe datanya checkbox, $answer akan berupa array. 
            // Kita ubah menjadi string (comma separated) agar bisa disimpan di kolom string/text.
            $finalAnswer = is_array($answer) ? implode(', ', $answer) : $answer;
            
            ChecklistDetail::create([
                'checklist_id' => $checklist->id,
                'type' => $checklistTemplate->details()->where('id', $detailId)->value('type'),
                'label' => $checklistTemplate->details()->where('id', $detailId)->value('label'),
                'options' => $checklistTemplate->details()->where('id', $detailId)->value('options'),
                'value' => $finalAnswer,
            ]);
            
        }

        // dd($checklist->load('details'));
        return response()->json([
            'success' => true,
            'message' => "Checklist tercatat",
                
            'data' => [
                'id' => $checklist->id,
                'instansi_id' => $checklist->instansi_id,
                'member_id' => $checklist->member_id,
                'details' => $checklist->details
            ],
        ]);
    }
}